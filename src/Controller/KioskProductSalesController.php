<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Event\Event;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Network\Request;
use Cake\ORM\TableRegistry;
use Cake\Mailer\Email;
use Cake\Network\Exception\SocketException;
use Cake\I18n;
use Cake\ORM\Behavior;

use Cake\Datasource\ConnectionManager;


class KioskProductSalesController extends AppController
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
		$this->set(compact('paymentType','newDiscountArr'));
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
		$this->loadModel('FaultyProducts');
		$this->loadModel('RetailCustomers');
		$CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
		$this->set(compact('CURRENCY_TYPE' ));
        $refundOptions = $active = Configure::read('refund_status');
		$newDiscountArr = Configure::read('new_discount');
		$discountOptions = Configure::read('discount');
		$paymentType=Configure::read('payment_type');
		$this->set('refundOptions',$refundOptions);
		$this->set('discountOptions',$discountOptions);
		$this->set('newDiscountArr',$newDiscountArr);
		$this->set(compact('paymentType'));
		
		//pr($refundOptions);die;
    }
    
    
    
    public function editReceipt($receipt_id = ''){
		if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
			$kiosk_id_to_set = $this->get_kiosk_for_invoice();
			
			if(!empty($kiosk_id_to_set)){
				if($kiosk_id_to_set == 10000){
					$productSource = "products";
					$productSalesSource = "kiosk_product_sales";
					$recipt_source = "product_receipts";
					$payment_source = "payment_details";
				}else{
					$kiosk_id = $kiosk_id_to_set;
					$productSource = "kiosk_{$kiosk_id}_products";
					$productSalesSource = "kiosk_{$kiosk_id}_product_sales";
					$recipt_source = "kiosk_{$kiosk_id}_product_receipts";
					$payment_source = "kiosk_{$kiosk_id}_payment_details";
				}
				
			}
		}else{
            $kiosk_id = $this->request->Session()->read('kiosk_id');
            $productSource = "kiosk_{$kiosk_id}_products";
					$productSalesSource = "kiosk_{$kiosk_id}_product_sales";
					$recipt_source = "kiosk_{$kiosk_id}_product_receipts";
					$payment_source = "kiosk_{$kiosk_id}_payment_details";
        }
		$productTable = TableRegistry::get($productSource,[
																	'table' => $productSource,
																]);
		$productSalesTable = TableRegistry::get($productSalesSource,[
																	'table' => $productSalesSource,
																]);
		$recipt_Table = TableRegistry::get($recipt_source,[
																	'table' => $recipt_source,
																]);
		$payment_Table = TableRegistry::get($payment_source,[
																	'table' => $payment_source,
																]);
		
		$vat = $this->VAT;
        $currencySymbol = Configure::read('CURRENCY_TYPE');
		//$currencySymbol = $this->setting['currency_symbol'];
        $orderDetails_query = $recipt_Table->get($receipt_id);
        if(!empty($orderDetails_query)){
            $orderDetails = $orderDetails_query->toArray();
        }
		$customerId = $orderDetails['customer_id'];
		
		$customerAccountDetails_query = $this->Customers->get($customerId);
        if(!empty($customerAccountDetails_query)){
            $customerAccountDetails = $customerAccountDetails_query->toArray();
        }
		$country = $customerAccountDetails['country'];
        $products_query = $productTable->find('list',[
                                        'keyField' => 'id',
                                        'valueField' => 'product'
                                     ]);
		$products_query = $products_query->hydrate(false);
        if(!empty($products_query)){
            $products = $products_query->toArray();
        }else{
			$products = array();
		}
		//deleting the already existing basket for the new entries
		//if(!empty($this->Session->read('oldBasket'))){
			$this->request->Session()->delete('oldBasket');
		//}
		
		$oldBlkDiscount = $orderDetails['bulk_discount'];
		 $this->paginate = [
            'limit' => 50,
            'order' => ['product' => 'ASC'],
            'conditions' => ['NOT' => ['quantity' => 0]],
        ];

		//$this->paginate = array(
		//						'Product' => array(
		//											'limit' => 50,
		//											'model' => 'Product',
		//											'order' => array('product' => 'ASC'),
		//											'recursive' => -1,
		//											'conditions' => array('NOT' => array('Product.quantity' => 0))
		//										)
		//						);
		//-----------------------------------------
        
		$categories_query = $this->Categories->find('all',array(
								'fields' => array('id', 'category','id_name_path'),
                                                                'conditions' => array('Categories.status' => 1),
								'order' => 'Categories.category asc'
								));
        $categories_query = $categories_query->hydrate(false);
        if(!empty($categories_query)){
            $categories = $categories_query->toArray();
        }
		$categoryList = array();
		foreach($categories as $sngCat){
			$categoryList[$sngCat['id']] = $sngCat['category'];
		}
		$categories = $this->CustomOptions->category_options($categories,true);
		
		//-----------------------------------------
		
		//receipt for the added products
		$bulkDiscountSession = $this->request->Session()->read('BulkDiscount');
		$session_basket = $this->request->Session()->read('Basket');
		//pr($session_basket);die;
		if(is_array($session_basket)){
			$productCodeArr = array();
			foreach($session_basket as $key => $basketItem){
				if($key == 'error')continue;
				
				$product_code_query = $productTable->find('all',array('conditions'=>array('id'=>$key),'fields'=>array('id','product_code')));
				$product_code_query = $product_code_query->hydrate(false);
				if(!empty($product_code_query)){
					$productCodeArr[] = $product_code_query->first();
				}else{
					$productCodeArr[] = array();
				}
			}
			$productCode = array();
			if(!empty($productCodeArr)){
				foreach($productCodeArr as $k=>$productCodeData){
					$productCode[$productCodeData['id']]=$productCodeData['product_code'];
				}
			}
			$basketStr = "";
			$counter = 0;
			$totalBillingAmount = 0;
			$totalDiscountAmount = 0;
			$sub_total=$vatAmount = 0;
			 //pr($session_basket);
			foreach($session_basket as $key => $basketItem){
				if($key == 'error')continue;
				$counter++;
				$vat = $this->VAT;
				$vatItem = $vat/100;
				$discount = $basketItem['discount'];				
				$sellingPrice = $basketItem['selling_price'];
				$net_amount = $basketItem['net_amount'];
				$itemPrice = $basketItem['selling_price']/(1+$vatItem);
				$price_without_vat = $basketItem['price_without_vat'];
				//added on Aug 1, 2016
				//$discountAmount = round($sellingPrice*$basketItem['discount']/100* $basketItem['quantity'],2);
				$discountAmount = $price_without_vat * $basketItem['discount']/100 * $basketItem['quantity'];
				//modified on Aug 1, 2016
				$totalDiscountAmount+= $discountAmount;
				//$totalItemPrice = round($basketItem['selling_price'] * $basketItem['quantity'],2);
				$totalItemPrice = $price_without_vat * $basketItem['quantity'];
				//modified on Aug 1, 2016
				$bulkDiscountPercentage = $bulkDiscountSession;
				$totalItemCost = round($totalItemPrice-$discountAmount,2);
				$totalItemCost = $totalItemPrice - $discountAmount;
				$totalBillingAmount+=$totalItemCost;
				$vatperitem = $basketItem['quantity']*($sellingPrice-$itemPrice);
				$vatAmount+= $vatperitem;
				$bulkDiscountValue = $totalBillingAmount*$bulkDiscountPercentage/100;
				$netBillingAmount = $totalBillingAmount-$bulkDiscountValue;
				//$netPrice = round($netBillingAmount/(1+$vatItem),2);
				$netPrice = $netBillingAmount;
				//modified on Aug 1, 2016
				//$vatAmount = round($netBillingAmount-$netPrice,2);
				$vatAmount = $netBillingAmount*$vatItem;
				//modified on Aug 1, 2016
				
				$finalAmount = $netBillingAmount;
				if($country == "OTH"){$finalAmount = $netPrice;}else{
					$finalAmount = $netBillingAmount+$vatAmount;
				}
				
				$sub_total = $sub_total + $totalItemCost;
				
				//modified on Aug 1, 2016. This can be conditional
				$basketStr.="<tr>
						<td>{$counter})</td>
						<td>{$productCode[$key]}</td>
						<td>".$basketItem['product']."</td>
						<td>".$basketItem['quantity']."</td>
						<td>".$currencySymbol.number_format($price_without_vat,2)."</td>
						<td> ".$discount."</td>
						<td>".$currencySymbol.number_format($net_amount,2)."</td>
						<td>".$currencySymbol.number_format($totalItemCost,2)."</td></tr>";
			}
			if(!empty($basketStr)){
				$basketStr = "<table><tr>
							<th style='width: 10px;'>Sr No</th>
							<th style='width: 132px;'>Product Code</th>
							<th style='width: 445px;'>Product</th>
							<th style='width: 30px;'>Qty</th>
							<th style='width: 99px;'>Sale Price</th>
							<th style='width: 40px;'>Disct %</th>
							<th style='width: 10px;'>Disct Value</th>
							<th style='width: 10px;'>Amount</th>
							</tr>".$basketStr."
							<tr><td colspan='7'>Sub Total</td><td>".$currencySymbol.number_format($sub_total,2)."</td></tr>
							<tr><td colspan='7'>Bulk Discount ({$bulkDiscountPercentage}%)</td><td>".$currencySymbol.number_format($bulkDiscountValue,2)."</td></tr>
							<tr><td colspan='7'>Sub Total(After bulk discount)</td><td>".$currencySymbol.number_format($netBillingAmount,2)."</td></tr>
							<tr><td colspan='7'>Vat</td><td>".$currencySymbol.number_format($vatAmount,2)."</td></tr>
							<tr><td colspan='7'>Net Amount</td><td>".$currencySymbol.number_format($netPrice,2)."</td></tr>
							<tr><td colspan='7'>Total Amount</td><td>".$currencySymbol.number_format($finalAmount,2)."</td></tr></table>";
							
							
				$productCounts = count($this->request->Session()->read('Basket'));
			
				if($productCounts){
					//$productCounts product(s) added to the cart.
					$flashMessage = "Total item Count:$productCounts.<br/>$basketStr";
				}else{
					$flashMessage = "No item added to the cart. Item Count:$productCounts";
				}
				
				if(array_key_exists('error',$session_basket)){
					$flashMessage = $session_basket['error']."<br/>".$flashMessage;
				}
				$this->Flash->success($flashMessage,['escape' => false]);
			}
		}
		//-----------------------------------------
		$this->set(compact('categories','customerAccountDetails','orderDetails','oldBlkDiscount', 'categoryList', 'vat'));
        $product_query = $this->paginate($productTable);
        //$product_query = $product_query->hydrate(false);
        if(!empty($product_query)){
            $product = $product_query->toArray();
        }
        //pr($product);die;
		$this->set('products', $product);
    }
    
    public function allKioskSale()
    {
        $vat = $this->VAT;
		$kioskId = -1;
		//$kiosks = $this->Kiosk->find('list');
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
		$this->set(compact('kiosks','kioskId','vat'));
		$conditionArr = array();
		$paymentConditionArr = array();
		$wholesaleConditionArr = array();
		$start_date = $this->request->query('start_date');
		$end_date = $this->request->query('end_date');
		$paymentMode = $this->request->query('payment_mode');
		if(empty($start_date)){
			$start_date = date('Y-m-d');
			$end_date = date('Y-m-d');
			$end_date1 = date('Y-m-d',strtotime('+1 Days'));
		}
		if(!empty($start_date) && !empty($end_date)){
			$conditionArr[] = array(
						"created >" => $start_date,
						"created <" => $end_date1,			
					       );
			$paymentConditionArr[] = array(
						"created >" => $start_date,
						"created <" => $end_date1,			
					       );
			$wholesaleConditionArr[] = array(
						"created >" => $start_date,
						"created <" => $end_date1,		
							);
		}
		
		$paymentDetail = $receiptIds = $refundData = $saleSumData = $cardPayment = $cashPayment = array();
		
		foreach($kiosks as $kioskId => $kioskName){
			if($kioskId == 10000)continue; 
            $KioskProductSalesTable_source = "kiosk_{$kioskId}_product_sales";
            $KioskProductSalesTable = TableRegistry::get($KioskProductSalesTable_source,[
                                                                    'table' => $KioskProductSalesTable_source,
                                                                        ]);
    
            $query = $KioskProductSalesTable->find('all',
                                                   ['conditions' => $conditionArr, 'refund_status' => [1,2]]);
                  $query
                          ->select(['todayProductRefund' => $query->func()->sum('refund_price*quantity')]);
            $query_result = $query->hydrate(false);
            if(!empty($query_result)){
                $refundData[$kioskId] = $query_result->first();
            }else{
                $refundData[$kioskId] = array();
            }
			$wholesaleConditionArr['kiosk_id'] = $kioskId;
            $query = $this->ProductPayments->find('all',
                                                   ['conditions' => $wholesaleConditionArr ,
													//'ProductPayments.kiosk_id' => $kioskId
													]
												   );
                  $query
                          ->select(['totalsale' => $query->func()->sum('amount')]);
						  //pr($query);
            $query_result = $query->hydrate(false);
            if(!empty($query_result)){
                $saleSumData[$kioskId] = $query_result->first();
            }else{
                $saleSumData[$kioskId] = array();
            }
		}
		unset($wholesaleConditionArr['kiosk_id']);
		//pr($saleSumData);die;
		foreach($kiosks as $kioskId => $kioskName){
			if($kioskId == 10000)continue;
            $receiptTable_source = "kiosk_{$kioskId}_product_sales";
            $KioskProductSalesTable = TableRegistry::get($receiptTable_source,[
                                                                    'table' => $receiptTable_source,
                                                                        ]);
            $PaymentDetailsTable_source = "kiosk_{$kioskId}_payment_details";
            $PaymentDetailsTable = TableRegistry::get($PaymentDetailsTable_source,[
                                                                    'table' => $PaymentDetailsTable_source,
                                                                        ]);
			
            $query = $PaymentDetailsTable->find('all',
                                                   ['conditions' => $wholesaleConditionArr]);
                  $query
                          ->select(['totalsale' => $query->func()->sum('amount')]);
            $query_result = $query->hydrate(false);
            if(!empty($query_result)){
                $sum = $query_result->first();
            }else{
                $sum = array();
            }
			$sum_arr[$kioskId] = $sum['totalsale'];
			$data_query = $KioskProductSalesTable->find('all',array(
													  'conditions' => $wholesaleConditionArr,
													  'fields' => array('product_id','kiosk_id','quantity','cost_price','sale_price','product_receipt_id','discount_status','discount')
													  ));
            $data_query = $data_query->hydrate(false);
            if(!empty($data_query)){
                $data = $data_query->toArray();
            }else{
                $data = array();
            }
			$wholesale_Arr[$kioskId] = $data;
		}
		$categories_query = $this->Categories->find('all',array(
								'fields' => array('id', 'category','id_name_path'),
								'conditions' => array('Categories.status' => 1),
								'order' => 'Categories.category asc'
								));
        $categories_query = $categories_query->hydrate(false);
        if(!empty($categories_query)){
            $categories = $categories_query->toArray();
        }else{
            $categories = array();
        }
		$categories = $this->CustomOptions->category_options($categories,true );
        //pr($saleSumData);die;
		//$cardPayment = $cashPayment = array();
		//pr($wholesale_Arr);die;
		$this->set(compact('kiosks','sum_arr', 'paymentMode', 'end_date', 'start_date', 'saleSumData', 'refundData','wholesale_Arr','categories'));
    }
    
    public function searchKioskSale()
    {
        $vat = $this->VAT;
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
		$this->set(compact('kiosks','kioskId','vat'));
		//pr($this->request);die;
		$sumTotalArr = array();
		$actualVat = $this->VAT;
		//$products = $this->Product->find('list',array('fields' => array('id','product')));
		$products_query = $this->Products->find('all',array(
													 'fields' => array('id','product','product_code'),
													 ));
        $products_query->hydrate(false);
        if(!empty($products_query)){
            $products = $products_query->toArray();
        }else{
            $products = array();
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
		$searchKW = '';
		if(array_key_exists('search_kw', $this->request->query)){
			 $searchKW = $this->request->query['search_kw'];
		}
		$receipt_id = '';
		if(array_key_exists('receipt_id', $this->request->query)){
			$receipt_id = $this->request->query['receipt_id'];
		}
		$category_ids = "";
		$category_id = $productIDs = $ids = array();
		if(array_key_exists('category',$this->request->query)&& !empty($this->request->query['category'][0])){
			$category_id = $this->request->query['category'];
			$category_ids = implode("_",$category_id);
			$ids_query = $this->Products->find('list',[
                                                 'conditions' => ['category_id IN' => $category_id],
                                                'keyField' => 'id',
                                                'valueField' => 'id'
											 ]);
            $ids_query = $ids_query->hydrate(false);
            if(!empty($ids_query)){
                $ids = $ids_query->toArray();
            }else{
                $ids = array();
            }
		}
		$start_date = '';
		if(array_key_exists('start_date',$this->request->query)){
			$start_date = $this->request->query['start_date'];
			$this->set(compact('start_date'));
		}
		
		$end_date = '';
        //pr($this->request);die;
		if(array_key_exists('end_date',$this->request->query)){
			$end_date = $this->request->query['end_date'];
			$this->set(compact('end_date'));
		}
			$conditionArr = array();
		
		$kiosk_id = $this->request->query['ProductSale']['kiosk_id'];
		if($kiosk_id == -1){
			return $this->redirect("/KioskProductSales/search_all_product_payments?start_date=$start_date&end_date=$end_date&kiosk_id=-1&search_kw=$searchKW&category_id=$category_ids");die;
		}
		
		$product_receipts_source = "kiosk_{$kiosk_id}_product_receipts";
        $product_receiptsTable = TableRegistry::get($product_receipts_source,[
                                                                    'table' => $product_receipts_source,
                                                                        ]);
		$receiptTable_source = "kiosk_{$kiosk_id}_product_sales";
        $KioskProductSalesTable = TableRegistry::get($receiptTable_source,[
                                                                    'table' => $receiptTable_source,
                                                                        ]);
	
		$conditionArr['sale_type'] = 0;
		if(array_key_exists('start_date',$this->request->query) &&
		   array_key_exists('end_date',$this->request->query) &&
		   !empty($this->request->query['start_date']) &&
		   !empty($this->request->query['end_date'])){
			$conditionArr[] = array(
						"created >=" => date('Y-m-d', strtotime($this->request->query['start_date'])),
						"created <" => date('Y-m-d', strtotime($this->request->query['end_date']. ' +1 Days')),			
					       );
		}
		
		if(array_key_exists('search_kw',$this->request->query) && !empty($this->request->query['search_kw'])){
			//$conditionArr['OR']['LOWER(Product.product) like '] =  strtolower("%$searchKW%");
			//$conditionArr['OR']['LOWER(Product.product_code) like '] =  strtolower("%$searchKW%");
			$searchW = strtolower($searchKW);
            $conn = ConnectionManager::get('default');
            $stmt = $conn->execute("SELECT `id` from `products` WHERE LOWER(`product_code`) like ('%{$searchW}%') or LOWER(`product`) like ('%{$searchW}%')");
            $productResult = $stmt ->fetchAll('assoc');			
			$productIDs = array();
			
			foreach($productResult as $sngproductResult){
                //pr($sngproductResult);die;
				$productIDs[] = $sngproductResult['id'];
			}
			//pr($productIDs);
			//$conditionArr['OR']['KioskProductSale.product_id'] =  $productIDs;
			$this->set('search_kw',$this->request->query['search_kw']);
		}
		
		$p_ids = array();
		if(!empty($ids)&&!empty($productIDs)){
			$p_ids = array_merge($productIDs,$ids);
		}elseif(!empty($productIDs)){
			$p_ids = $productIDs;
		}elseif(!empty($ids)){
			$p_ids = $ids;
		}
		
		if(!empty($p_ids)){
			$conditionArr['OR']['product_id IN'] =  $p_ids;
		}
		
		if(array_key_exists('receipt_id',$this->request->query) && !empty($this->request->query['receipt_id'])){
			$conditionArr['product_receipt_id'] =  $receipt_id;
			$this->set('receipt_id',$this->request->query['receipt_id']);
		}
		$conditionArr['refund_status'] = '<> 1';
		$result_query = $KioskProductSalesTable->find('all',array(
															'conditions'=>$conditionArr,
															'recursive'=> -1,
															'fields' => array('id','product_receipt_id')));
        //pr($result_query);die;
        $result_query = $result_query->hydrate(false);
        if(!empty($result_query)){
            $result = $result_query->toArray();
        }else{
            $result = array();
        }
		//pr($result);die;
		$receiptArray = array();
		$receipt_Ids = array();
	//echo count($result);die;
		if(count($result)){
			foreach($result as $rk => $results){
				$receiptArray[$results['product_receipt_id']] = $results['id'];
			}
			if(!empty($receiptArray)){
                $receipt_Ids = array_keys($receiptArray);
            }
            $query = $product_receiptsTable->find('all',
                                                   ['conditions' => ['id IN' => $receipt_Ids]]);
                  $query
                          ->select(['totalSum' => $query->func()->sum('bill_amount')]);
            $query_result = $query->hydrate(false);
            if(!empty($query_result)){
                $sumTotalArr = $query_result->first();
            }else{
                $sumTotalArr = array();
            }
			
		}
		//for sum total purpose, getting the receipt ids as per the above condition array
		
		//$sumTotalArr = $this->KioskProductSale->find('first',array('conditions'=>$conditionArr,'fields'=>array('CASE WHEN KioskProductSale.discount>0 THEN SUM(KioskProductSale.sale_price*KioskProductSale.quantity*(1-KioskProductSale.discount/100)) ELSE SUM(KioskProductSale.sale_price*KioskProductSale.quantity) END as totalSum')));
		$sumTotal = 0;
		if(count($sumTotalArr)){
			if($sumTotalArr['totalSum']>0){
				$sumTotal = $sumTotalArr['totalSum'];
			}
		}
		
		$netAmount = $sumTotal/(1+$actualVat/100);
		$totalVat = $sumTotal - $netAmount;
		
		//sum total ends here
		//pr($receipt_Ids);
        if(empty($receipt_Ids)){
            $receipt_Ids = array(0 => null);   
        }
		$conditionArr['product_receipt_id IN'] = $receipt_Ids;
		$conditionArr['refund_status'] = '<> 1';
		$this->paginate = [
                            'conditions' => $conditionArr,
                            'limit' => ROWS_PER_PAGE,
                            'order' => ['id DESC']
                            //'contain' =>'kiosk_3_product_receipts'
                          ];
		$kioskProductSales_query = $this->paginate($KioskProductSalesTable);
        if(!empty($kioskProductSales_query)){
            $kioskProductSales = $kioskProductSales_query->toArray();
        }else{
            $kioskProductSales = array();
        }
        $y_receipt_ids = array();
        foreach($kioskProductSales as $receipt_idsArr){
            $y_receipt_ids[] = $receipt_idsArr['product_receipt_id'];
        }
        if(empty($y_receipt_ids)){
            $y_receipt_ids = array(0 => null);
        }
        $recepit_table_data_query = $product_receiptsTable->find('all',[
                                                                        'conditions' => ['id IN' => $y_receipt_ids]
                                                                        ]
                                                                );
        $recepit_table_data_query = $recepit_table_data_query->hydrate(false);
        if(!empty($recepit_table_data_query)){
            $recepit_table_data = $recepit_table_data_query->toArray();
        }else{
            $recepit_table_data = array();
        }
        $recepitTableData = array();
        foreach($recepit_table_data as $recepit_keys => $recepit_values){
            $recepitTableData[$recepit_values['id']] = $recepit_values;
        }
        //pr($recepitTableData);die;
        $this->set(compact('recepitTableData'));
        //pr($recepit_table_data);die;
        //pr($receipt_ids);die;
        //pr($kioskProductSales);die;
		//pr($kioskProductSales);die;
		$hint = $this->ScreenHint->hint('kiosk_product_sales','index');
					if(!$hint){
						$hint = "";
					}
	    $orignal_amount = array();
		//pr($receipt_Ids);
		$productReceiptDetail_query = $product_receiptsTable->find('all',array('conditions' =>array('id IN' => $receipt_Ids),'fields'=>array('id','orig_bill_amount')));
        $productReceiptDetail_query->hydrate(false);
        if(!empty($productReceiptDetail_query)){
            $productReceiptDetail = $productReceiptDetail_query->toArray();
        }else{
            $productReceiptDetail = array();
        }
		//pr($productReceiptDetail);
		foreach($productReceiptDetail  as $key => $value){
            //pr($value);die;
			if(empty($value["orig_bill_amount"])){
				$value["orig_bill_amount"] = 0;
			}
			$orignal_amount[$value["id"]] = $value["orig_bill_amount"];
		}
		if(!empty($conditionArr)){
			if(array_key_exists("refund_status",$conditionArr)){
				unset($conditionArr["refund_status"]);
			}
			if(array_key_exists("product_receipt_id",$conditionArr)){
				unset($conditionArr["product_receipt_id"]);
			}
		}
        $conditionArr['refund_status IN'] = array(1,2);
        $query = $KioskProductSalesTable->find('all',
                                                   ['conditions' => $conditionArr]);
            $query
                        ->select(['totalrefund' => $query->func()->sum('refund_price*quantity')]);
						//pr($query);die;
        $query_result = $query->hydrate(false);
        if(!empty($query_result)){
            $refundSumData = $query_result->first();
        }else{
            $refundSumData = array();
        }
		$refundData = $refundSumData["totalrefund"];
		
		$refund_query = $KioskProductSalesTable->find('all',['conditions' => $conditionArr]);
		$refund_query = $refund_query->hydrate(false);
		if(!empty($refund_query)){
			$refund_data_entry = $refund_query->toArray();
		}else{
			$refund_data_entry = array();
		}
		$value_to_contain = array();
		if(array_key_exists(0,$conditionArr)){
			$value_to_contain  = $conditionArr[0];
			unset($conditionArr[0]);
		}	
		
		
		$conditionArr[0] = $value_to_contain;
		unset($conditionArr['refund_status IN']);
		//pr($refund_data_entry);die;
		$new_refund_data = array();
		
		if(!empty($refund_data_entry)){
			foreach($refund_data_entry as $refund_key => $refund_value){
				if(array_key_exists($refund_value['product_receipt_id'],$new_refund_data)){
					if(array_key_exists($refund_value['product_id'],$new_refund_data[$refund_value['product_receipt_id']])){
						$new_refund_data[$refund_value['product_receipt_id']][$refund_value['product_id']]['refund_price'] += $refund_value['refund_price'];
						$new_refund_data[$refund_value['product_receipt_id']][$refund_value['product_id']]['quantity'] += $refund_value['quantity'];
						if(array_key_exists($refund_value['refund_by'],$users)){
							$new_refund_data[$refund_value['product_receipt_id']][$refund_value['product_id']]['refund_by'] .=   " , ".$users[$refund_value['refund_by']];		
						}else{
							$new_refund_data[$refund_value['product_receipt_id']][$refund_value['product_id']]['refund_by'] .=   " , --";	
						}
						
						$created_date = $refund_value['created'];
						$created_date = $created_date->i18nFormat(
																	[\IntlDateFormatter::FULL, \IntlDateFormatter::FULL]
															);
						
						
						$new_refund_data[$refund_value['product_receipt_id']][$refund_value['product_id']]['refund_date'] .=   " ; ".$created_date;	
					
					}else{
						$new_refund_data[$refund_value['product_receipt_id']][$refund_value['product_id']]['refund_price'] =   $refund_value['refund_price'];
					$new_refund_data[$refund_value['product_receipt_id']][$refund_value['product_id']]['quantity'] =   $refund_value['quantity'];
					if(array_key_exists($refund_value['refund_by'],$users)){
						$new_refund_data[$refund_value['product_receipt_id']][$refund_value['product_id']]['refund_by'] =   $users[$refund_value['refund_by']];		
					}else{
						$new_refund_data[$refund_value['product_receipt_id']][$refund_value['product_id']]['refund_by'] =   "";	
					}
					$created_date = $refund_value['created'];
					$created_date = $created_date->i18nFormat(
                                                                [\IntlDateFormatter::FULL, \IntlDateFormatter::FULL]
                                                        );
					$new_refund_data[$refund_value['product_receipt_id']][$refund_value['product_id']]['refund_date'] =   $created_date;	
					
					}
				}else{
					$new_refund_data[$refund_value['product_receipt_id']][$refund_value['product_id']] =   array();
					$new_refund_data[$refund_value['product_receipt_id']][$refund_value['product_id']]['refund_price'] =   $refund_value['refund_price'];
					$new_refund_data[$refund_value['product_receipt_id']][$refund_value['product_id']]['quantity'] =   $refund_value['quantity'];
					if(array_key_exists($refund_value['refund_by'],$users)){
						$new_refund_data[$refund_value['product_receipt_id']][$refund_value['product_id']]['refund_by'] =   $users[$refund_value['refund_by']];		
					}else{
						$new_refund_data[$refund_value['product_receipt_id']][$refund_value['product_id']]['refund_by'] =   "";	
					}
					
					$created_date = $refund_value['created'];
					$created_date = $created_date->i18nFormat(
                                                                [\IntlDateFormatter::FULL, \IntlDateFormatter::FULL]
                                                        );
					$new_refund_data[$refund_value['product_receipt_id']][$refund_value['product_id']]['refund_date'] =   $created_date;	
					
				}
			}	
		}
		
        unset($conditionArr['refund_status IN']);
		
		//pr($orignal_amount);die;
		$categories_query = $this->Categories->find('all',array(
								'fields' => array('id', 'category','id_name_path'),
								'conditions' => array('Categories.status' => 1),
								'order' => 'Categories.category asc'
								));
        $categories_query->hydrate(false);
        if(!empty($categories_query)){
            $categories = $categories_query->toArray();
        }else{
            $categories = array();
        }
		$categories = $this->CustomOptions->category_options($categories,true,$category_id);
		//pr($categories);die;
		$this->set(compact('kiosk_id','orignal_amount','refundData','categories','new_refund_data'));
		$this->set(compact('hint','kioskProductSales','products','users','actualVat','sumTotal','netAmount','totalVat'));
		//$this->layout = 'default';
		$this->render('search_kiosk_sale');
    }
    
    public function searchAllProductPayments()
    {
        $vat = $this->VAT;
		$this->set(compact('vat'));
		$kiosks_query = $this->Kiosks->find('list', [
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
			$wholesaleConditionArr[] = array(
						"created >" => date('Y-m-d', strtotime($this->request->query['start_date'])),
						"created <" => date('Y-m-d', strtotime($this->request->query['end_date']. ' +1 Days')),		
							);
		}
		
		$retail = '';
		if(array_key_exists('retail',$this->request->query)){
			$retail = $this->request->query['retail'];
			$this->set(compact('retail'));
		}
		
		$wholesale = '';
		if(array_key_exists('wholesale',$this->request->query)){
			$wholesale = $this->request->query['wholesale'];
			$this->set(compact('wholesale'));
		}
		$catagriy_ids = array();
		$productIDs = $product_ids = $catagriy_ids = array();
        //pr($this->request);die;
		if(array_key_exists('category_id',$this->request->query)){
			$catagory = $this->request->query['category_id'];
			$catagriy_ids = explode("_",$catagory);
			$product_ids_query = $this->Products->find('list',[
                                                         'conditions' => ['category_id IN' => $catagriy_ids],
                                                        'keyField' => 'id',
                                                        'valueField' => 'id'
                                                    ]);
            $product_ids_query = $product_ids_query->hydrate(false);
            if(!empty($product_ids_query)){
                $product_ids = $product_ids_query->toArray();
            }else{
                $product_ids = array();
            }
		}
		
		$searchKW = '';
		if(array_key_exists('search_kw',$this->request->query) && !empty($this->request->query['search_kw'])){
			$searchKW = $this->request->query['search_kw'];
			//$conditionArr['OR']['LOWER(Product.product) like '] =  strtolower("%$searchKW%");
			//$conditionArr['OR']['LOWER(Product.product_code) like '] =  strtolower("%$searchKW%");
			$searchW = strtolower($searchKW);
            $conn = ConnectionManager::get('default');
            $stmt = $conn->execute("SELECT `id` from `products` WHERE LOWER(`product_code`) like ('%{$searchW}%') or LOWER(`product`) like ('%{$searchW}%')");
            $productResult = $stmt ->fetchAll('assoc');
			
			
			$productIDs = array();
			
			foreach($productResult as $sngproductResult){
				$productIDs[] = $sngproductResult['id'];
			}
			//pr($productIDs);
			//$conditionArr['AND']['KioskProductSale.product_id'] =  $productIDs;
			//$wholesaleConditionArr['OR']['KioskProductSale.product_id'] =  $productIDs;
			$this->set('search_kw',$this->request->query['search_kw']);
		}
		
		$p_ids = array();
		if(!empty($product_ids)&&!empty($productIDs)){
			$p_ids = array_merge($productIDs,$product_ids);
		}elseif(!empty($productIDs)){
			$p_ids = $productIDs;
		}elseif(!empty($product_ids)){
			$p_ids = $product_ids;
		}
		
		
		if(!empty($p_ids)){
			$conditionArr['AND']['product_id IN'] = $p_ids;
		}
		
		
		$conditionArr['sale_type'] = 0;
		$paymentDetail = $receiptIds = $refundData = $saleSumData = $cardPayment = $cashPayment = array();
		foreach($kiosks as $kioskId => $kioskName){
			$ids = array();
			if($kioskId == 10000)continue;
			//$this->KioskProductSales->setSource("kiosk_{$kioskId}_product_sales");
            $receiptTable_source = "kiosk_{$kioskId}_product_sales";
            $KioskProductSalesTable = TableRegistry::get($receiptTable_source,[
                                                                                'table' => $receiptTable_source,
                                                                                    ]);
            //pr($conditionArr);die;
            $conditionArr['refund_status'] = 0;
			$ids_query = $KioskProductSalesTable->find('list',[
                                                          'keyField' => 'id',
                                                         'valueField' => 'product_receipt_id',
                                                         'conditions' => $conditionArr
                                                         ]);
            unset($conditionArr['refund_status']);
            $ids_query = $ids_query->hydrate(false);
            if(!empty($ids_query)){
                $ids = $ids_query->toArray();
            }else{
                $ids = array();
            }
			if(empty($ids)){
				$ids = array(0);
			}
			$saledata_query = $KioskProductSalesTable->find('all',array(
													  'conditions' => $conditionArr,
													  'fields' => array('product_id','kiosk_id','quantity','cost_price','sale_price','product_receipt_id','discount_status','discount','cost_price')
													  ));
            $saledata_query->hydrate(false);
            if(!empty($saledata_query)){
                $saledata = $saledata_query->toArray();
            }else{
                $saledata = array();
            }
			$sale_Arr[$kioskId] = $saledata;
			
			
			unset($conditionArr['sale_type']);
            $query = $KioskProductSalesTable->find('all',['conditions' => $conditionArr, 'refund_status' => [1,2]]);
                  $query
                          ->select(['todayProductRefund' => $query->func()->sum('refund_price * quantity')]);
            $query_result = $query->hydrate(false);
            if(!empty($query_result)){
               $refundData[$kioskId] = $query_result->first();
            }else{
                $refundData[$kioskId] = array();
            }
			$conditionArr['sale_type'] = 0;
			
			if(!empty($searchKW) || !empty($catagory)){
                
                $query = $this->ProductPayments->find('all',['conditions' => $paymentConditionArr, 'ProductPayments.kiosk_id' => $kioskId]);
                  $query
                          ->select(['totalsale' => $query->func()->sum('amount')]);
                $query_result = $query->hydrate(false);
                if(!empty($query_result)){
                   $saleSumData[$kioskId] = $query_result->first();
                }else{
                    $saleSumData[$kioskId] = array();
                }
			}else{
                $query = $this->ProductPayments->find('all',['conditions' => [$paymentConditionArr, 'ProductPayments.kiosk_id' => $kioskId]]);
                  $query
                          ->select(['totalsale' => $query->func()->sum('amount')]);
                          //pr($query);echo "</br>";
                $query_result = $query->hydrate(false);
                if(!empty($query_result)){
                   $saleSumData[$kioskId] = $query_result->first();
                }else{
                    $saleSumData[$kioskId] = array();
                }
			}
		}
        //pr($saleSumData);die;
		$qanArr = array();
		if($wholesale == 1){
			$conditionArr['sale_type'] = array(0,1);
			foreach($kiosks as $kioskId => $kioskName){
                $receiptTable_source = "kiosk_{$kioskId}_product_sales";
                $KioskProductSalesTable = TableRegistry::get($receiptTable_source,[
                                                                                'table' => $receiptTable_source,
                                                                                    ]);
				$qntityData = $this->KioskProductSale->find('first',array('fields' => array('SUM(quantity) as total_qantity'),'conditions' => array($conditionArr, 'KioskProductSale.refund_status' => array(0))));
				if(!empty($qntityData)){
					$qanArr[$kioskId] = $qntityData[0]['total_qantity'];
				}else{
					$qanArr[$kioskId] = 0;
				}
			}
		}else{
			$conditionArr['sale_type'] = 0;
            $conditionArr['refund_status'] = 0;
			foreach($kiosks as $kioskId => $kioskName){
				$receiptTable_source = "kiosk_{$kioskId}_product_sales";
                $KioskProductSalesTable = TableRegistry::get($receiptTable_source,[
                                                                                'table' => $receiptTable_source,
                                                                                    ]);
                
                $query = $KioskProductSalesTable->find('all',['conditions' => $conditionArr]);
                  $query
                          ->select(['total_qantity' => $query->func()->sum('quantity')]);
                $query_result = $query->hydrate(false);
                unset($conditionArr['refund_status']);
                if(!empty($query_result)){
                   $qntityData = $query_result->first();
                }else{
                    $qntityData = array();
                }
					if(!empty($qntityData)){
						$qanArr[$kioskId] = $qntityData['total_qantity'];
					}else{
						$qanArr[$kioskId] = 0;
					}
			}
		}
		unset($conditionArr['sale_type']);
		$sum_arr = $wholesale_Arr = array();
		if($wholesale == 1){
			$conditionArr['sale_type'] = 1;
			foreach($kiosks as $kioskId => $kioskName){
                $conditionArr['refund_status'] = 0;
				if($kioskId == 10000)continue;
				//$kioskId = 3;
				$ids = array();
                $receiptTable_source = "kiosk_{$kioskId}_product_sales";
                $KioskProductSalesTable = TableRegistry::get($receiptTable_source,[
                                                                                'table' => $receiptTable_source,
                                                                                    ]);
                $PaymentDetails_source = "kiosk_{$kioskId}_payment_details";
                $PaymentDetailsTable = TableRegistry::get($PaymentDetails_source,[
                                                                                'table' => $PaymentDetails_source,
                                                                                    ]);
				$ids_query = $KioskProductSalesTable->find('list',[
                                                             'keyField' => 'id',
                                                             'valueField' => 'product_receipt_id',
                                                             'conditions' => $conditionArr
                                                            ]);
                unset($conditionArr['refund_status']);
                $ids_query = $ids_query->hydrate(false);
                if(!empty($ids_query)){
                    $ids = $ids_query->toArray();
                }else{
                    $ids = array();
                }
					$wholesaleConditionArr['product_receipt_id'] = $ids;
                
                $query = $PaymentDetailsTable->find('all',['conditions' => $wholesaleConditionArr]);
                  $query
                          ->select(['totalsale' => $query->func()->sum('amount')]);
                $query_result = $query->hydrate(false);
                if(!empty($query_result)){
                   $sum = $query_result->first();
                }else{
                    $sum = array();
                }
				$sum_arr[$kioskId] = $sum['totalsale'];
				$data_query = $KioskProductSalesTable->find('all',array(
														  'conditions' => $conditionArr,
														  'fields' => array('product_id','kiosk_id','quantity','cost_price','sale_price','product_receipt_id','discount_status','discount','cost_price')
														  ));
                $data_query->hydrate(false);
				if(!empty($data_query)){
                    $data = $data_query->toArray();
                }else{
                    $data = array();
                }
				$wholesale_Arr[$kioskId] = $data;
			}//die;
		}
		$categories_query = $this->Categories->find('all',array(
							'fields' => array('id', 'category','id_name_path'),
							'conditions' => array('Categories.status' => 1),
							'order' => 'Categories.category asc'
							));
        $categories_query = $categories_query->hydrate(false);
        if(!empty($categories_query)){
            $categories = $categories_query->toArray();
        }else{
            $categories = array();
        }
        //pr($catagriy_ids);die;
		$categories = $this->CustomOptions->category_options($categories,true,$catagriy_ids);
		//pr($categories);
		
		$this->set(compact('kiosks', 'paymentMode', 'end_date', 'start_date', 'saleSumData','wholesale_Arr','refundData', 'cardPayment', 'cashPayment','sum_arr','sale_Arr','retail','wholesale','qanArr','categories'));
		$this->render('all_kiosk_sale');
    }
    
    public function allWholesaleKioskSale()
    {
        $vat = $this->VAT;
		$kiosks_query = $this->Kiosks->find('list',[
                                             'keyField' => 'id',
                                             'valueField' => 'name',
                                             'conditions' => ['Kiosks.status' => 1],
                                             'order'=>('Kiosks.name asc')
                                           ]);
        $kiosks_query = $kiosks_query->hydrate(false);
        if(!empty($kiosks_query)){
            $kiosks = $kiosks_query->toArray();
        }else{
            $kiosks = array();
        }
		$conditionArr = array();
		$paymentConditionArr = array();
		if(array_key_exists("start_date",$this->request->query)){
			$start_date = date('Y-m-d',strtotime($this->request->query('start_date')));
		}else{
			$start_date = "";
		}
		
		if(array_key_exists("end_date",$this->request->query)){
			$end_date = $this->request->query('end_date');
			 $end_date1 = date('Y-m-d',strtotime($end_date. ' +1 Days'));
		}else{
			$end_date1 = "";
			$end_date = "";
		}
		
		if(empty($start_date)){
			$start_date = date('Y-m-d');
			$end_date = date('Y-m-d');
			$end_date1 = date('Y-m-d',strtotime('+1 Days'));
		}
	
		if(!empty($start_date) && !empty($end_date)){
			$conditionArr[] = array(
						"created >" => $start_date,
						"created <" => $end_date1,			
					       );
			$paymentConditionArr[] = array(
						"created >" => $start_date,
						"created <" => $end_date1,			
					       );
		}
		$conditionArr['sale_type'] = 1;
		$paymentDetail = array();
		$receiptIds = array();
		$refundData = array();
		$saleSumData = array();
		$cardPayment = array();
		$cashPayment = array();
		
		foreach($kiosks as $kioskId => $kioskName){
			if($kioskId == 10000){
				$recipt_table_source = "product_receipts";
				$recieptTable = TableRegistry::get($recipt_table_source,[
                                                                        'table' => $recipt_table_source,
                                                                            ]);
				
                $KioskProductSales_source = "kiosk_product_sales";
                $KioskProductSalesTable = TableRegistry::get($KioskProductSales_source,[
                                                                        'table' => $KioskProductSales_source,
                                                                        ]);
                $PaymentDetails_source = "payment_details";
                $PaymentDetailsTable = TableRegistry::get($PaymentDetails_source,[
                                                                        'table' => $PaymentDetails_source,
                                                                        ]);
			}else{
				$recipt_table_source = "kiosk_{$kioskId}_product_receipts";
				$recieptTable = TableRegistry::get($recipt_table_source,[
                                                                        'table' => $recipt_table_source,
                                                                            ]);
				
				
                $KioskProductSales_source = "kiosk_{$kioskId}_product_sales";
                $KioskProductSalesTable = TableRegistry::get($KioskProductSales_source,[
                                                                        'table' => $KioskProductSales_source,
                                                                        ]);
                $PaymentDetails_source = "kiosk_{$kioskId}_payment_details";
                $PaymentDetailsTable = TableRegistry::get($PaymentDetails_source,[
                                                                        'table' => $PaymentDetails_source,
                                                                        ]);
			}
			
			
			$recipt_ids_query = $KioskProductSalesTable->find('list',[
                                                                'keyField' => 'id',
                                                                'valueField' => 'product_receipt_id',
                                                                'conditions' => [$conditionArr]
                                                               ]);
			
			$recipt_ids_query = $recipt_ids_query->hydrate(false);
            if(!empty($recipt_ids_query)){
                $recipt_ids = $recipt_ids_query->toArray();
            }else{
                $recipt_ids = array();
            }
            
			$credit_conditionArr =  array(
						"created >" => $start_date,
						"created <" => $end_date1,			
					       );
			
			
            $query_kiosk_sale = $KioskProductSalesTable->find('all',['conditions' => $conditionArr,  'KioskProductSale.refund_status' => [1,2]]);
                  $query_kiosk_sale
                          ->select(['todayProductRefund' => $query_kiosk_sale->func()->sum('refund_price*quantity')]);
            $query_result = $query_kiosk_sale->hydrate(false);
            if(!empty($query_result)){
               $refundData[$kioskId] = $query_result->first();
            }else{
                $refundData[$kioskId] = array();
            }
			//$refundData[$kioskId] = $refundData[$kioskId]['todayProductRefund'];
			$refundData[$kioskId] = $this->get_credit_data($kioskId,$credit_conditionArr);
			//pr($refundData);die;
			if(empty($recipt_ids)){
				$recipt_ids = array(null);
			}
			
			$recipt_res_query = $recieptTable->find('list',array(
												'conditions' => array('id IN' => $recipt_ids),
												'keyField' => "id",
												'valueField' => "vat",
												));
		
				
			$recipt_res_query = $recipt_res_query->hydrate(false);
			if(!empty($recipt_res_query)){
				$recipt_res = $recipt_res_query->toArray();
			}else{
				$recipt_res = array();
			}
				
					
				
			if(!empty($recipt_res)){
				$with_vat_ids = array_keys(array_filter($recipt_res));
				$without_vat_ids = array_diff($recipt_ids,$with_vat_ids);	
			}else{
				$with_vat_ids = $without_vat_ids = array(0=>null);
			}
			
			if(empty($with_vat_ids)){
				$with_vat_ids = array(null);
			}
				
			
			//-----------------------------------------------
			$paymentConditionArr['product_receipt_id IN'] = $with_vat_ids;
				$query_payment_detail_with_vat = $PaymentDetailsTable->find('all',['conditions' => $paymentConditionArr]);
                $query_payment_detail_with_vat
                          ->select(['totalsale' => $query_payment_detail_with_vat->func()->sum('amount')]);
                $query_payment_detail_with_vat = $query_payment_detail_with_vat->hydrate(false);
				if(!empty($query_payment_detail_with_vat)){
					$payment_detail_with_vat_res  = $query_payment_detail_with_vat->first();
					$payment_detail_with_vat = $payment_detail_with_vat_res['totalsale'];
				}else{
					$payment_detail_with_vat = 0;
				}
				
				if(empty($without_vat_ids)){
					$without_vat_ids = array(null);
				}
				
				$paymentConditionArr['product_receipt_id IN'] = $without_vat_ids;
				$query_payment_detail_without_vat = $PaymentDetailsTable->find('all',['conditions' => $paymentConditionArr]);
                $query_payment_detail_without_vat
                          ->select(['totalsale' => $query_payment_detail_without_vat->func()->sum('amount')]);
                $query_payment_detail_without_vat = $query_payment_detail_without_vat->hydrate(false);
				if(!empty($query_payment_detail_with_vat)){
					$payment_detail_without_vat_res  = $query_payment_detail_without_vat->first();
					$payment_detail_without_vat = $payment_detail_without_vat_res['totalsale'];
					
					$payment_detail_without_vat = $payment_detail_without_vat + $payment_detail_without_vat*($vat/100);
					
				}else{
					$payment_detail_without_vat = 0;
				}
				
				unset($paymentConditionArr['product_receipt_id IN']);
                if(!empty($payment_detail_with_vat) || !empty($payment_detail_without_vat)){
                    $saleSumData[$kioskId]['totalsale'] = $payment_detail_with_vat + $payment_detail_without_vat;													//$query_payment_result->first();
                }else{
                    $saleSumData[$kioskId]['totalsale']	 = 0;
                }
			//-----------------------------------------------	
				
			
			
//            $query = $PaymentDetailsTable->find('all',['conditions' => $paymentConditionArr,
//													   'PaymentDetail.product_receipt_id' => $recipt_ids]);
//                  $query
//                          ->select(['totalsale' => $query->func()->sum('amount')]);
//            $query_result = $query->hydrate(false);
//            if(!empty($query_result)){
//               $saleSumData[$kioskId] = $query_result->first();
//            }else{
//                $saleSumData[$kioskId] = array();
//            }				
		}
		
		$categories_query = $this->Categories->find('all',array(
								'fields' => array('id', 'category','id_name_path'),
								'conditions' => array('Categories.status' => 1),
								'order' => 'Categories.category asc'
								));
        $categories_query = $categories_query->hydrate(false);
        if(!empty($categories_query)){
            $categories = $categories_query->toArray();
        }else{
            $categories = array();
        }
		$categories = $this->CustomOptions->category_options($categories,true);
		$t_data = $this->t_data($conditionArr,$paymentConditionArr);
		//pr($t_data);die;
		//$start_date = date('Y-m-d');
		$this->set(compact('vat','kiosks', 'paymentMode', 'end_date', 'start_date', 'saleSumData', 'refundData', 'cardPayment', 'cashPayment','categories','t_data'));
    }
    
    public function t_data($conditionArr = array(),$paymentConditionArr = array()){
        $KioskProductSales_source = "t_kiosk_product_sales";
        $KioskProductSalesTable = TableRegistry::get($KioskProductSales_source,[
                                                                        'table' => $KioskProductSales_source,
                                                                        ]);
        $ProductReceipts_source = "t_product_receipts";
        $ProductReceiptsTable = TableRegistry::get($ProductReceipts_source,[
                                                                'table' => $ProductReceipts_source,
                                                                ]);
        $PaymentDetails_source = "t_payment_details";
        $PaymentDetailsTable = TableRegistry::get($PaymentDetails_source,[
                                                                'table' => $PaymentDetails_source,
                                                                        ]);
		
		$CreditReceiptSource = "t_credit_receipts";
		$CreditReceiptTable = TableRegistry::get($CreditReceiptSource,[
                                                                        'table' => $CreditReceiptSource,
                                                                            ]);
		
		$kiosks_query = $this->Kiosks->find('list', [
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
		
		$credit_conditionArr = $conditionArr[0];
		
		$t_saleSumData = array();
		foreach($kiosks as $kioskId => $kioskName){
			if($kioskId == 10000){
				$kioskId = 0;
			}
			 $conditionArr['refund_status'] = 0;
            $conditionArr['kiosk_id'] = $kioskId;
			$recipt_ids_query = $KioskProductSalesTable->find('list',[
                                                                'keyField' => 'id',
                                                                'valueField' => 'product_receipt_id',
																'conditions' => $conditionArr
                                                               ]);
			unset($conditionArr['refund_status']);
            unset($conditionArr['kiosk_id']);
            $recipt_ids_query = $recipt_ids_query->hydrate(false);
            if(!empty($recipt_ids_query)){
                $recipt_ids = $recipt_ids_query->toArray();
            }else{
                $recipt_ids = array();
            }
			//$t_saleSumData[$kioskId] = $this->PaymentDetail->find('first', array('fields' => array('SUM(amount) as totalsale'), 'conditions' => array($paymentConditionArr,'PaymentDetail.product_receipt_id' => $recipt_ids),
																				  //'recursive' => -1
																				  // ));
            //pr($recipt_ids);die;
            if(empty($recipt_ids)){
                $recipt_ids = array(0 => null);
            }
			
			$credit_conditionArr['kiosk_id'] = $kioskId;
			//pr($credit_conditionArr);die;
			$refundData_res_query = $CreditReceiptTable->find('all',['conditions' => $credit_conditionArr]);
                $refundData_res_query
                          ->select(['todayProductRefund' => $refundData_res_query->func()->sum('credit_amount')]);
						  //pr($refundData_res_query);die;
						  
						  
			$refundData_res_query = $refundData_res_query->hydrate(false);
			if(!empty($refundData_res_query)){	
				$refundData_res = $refundData_res_query->first();
			}else{
				$refundData_res = array();
			}
			
			$refundData[$kioskId] = $refundData_res['todayProductRefund'];
			
			
			$query_product_receipt = $ProductReceiptsTable->find('all',array('conditions'=>array('id IN' => $recipt_ids),
													'fields' => array('orig_bill_amount','vat')
													)
										);
            $query_product_receipt = $query_product_receipt->hydrate(false);
            if(!empty($query_product_receipt)){
                $orgData[$kioskId] = $query_product_receipt->toArray();
            }else{
                $orgData[$kioskId] = array();
            }
            
			$sale_amount = 0;
			if(!empty($orgData[$kioskId])){
				foreach($orgData[$kioskId] as $sum_key => $sum_value){
					$org_amount = $sum_value['orig_bill_amount'];
					$vat = $sum_value['vat'];
					if(!empty($vat)){
						$sale_amount += $org_amount; //   /(1+($vat/100));
					}else{
						$sale_amount += $org_amount;
					}
				}
			}
			$t_saleSumData[$kioskId] = $sale_amount;																	  
		}
		$t_saleSumData['refund_data'] = $refundData;
		return $t_saleSumData;
	}

	
	
    public function searchWholsale()
    {   
		$kiosks_query = $this->Kiosks->find('list', [
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
		$products_query = $this->Products->find('all',array(
													 'fields' => array('id','product','product_code'),
													 ));
        $products_query = $products_query->hydrate(false);
        if(!empty($products_query)){
            $products = $products_query->toArray();
        }else{
            $products = array();
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
		$actualVat = $this->VAT;
		//pr($this->request);die;
		$searchKW = '';
		if(array_key_exists('search_kw', $this->request->query)){
			 $searchKW = $this->request->query['search_kw'];
		}
		$start_date = '';
		if(array_key_exists('start_date',$this->request->query)){
			$start_date = $this->request->query['start_date'];
			$this->set(compact('start_date'));
		}
		
		$end_date = '';
		if(array_key_exists('end_date',$this->request->query)){
			$end_date = $this->request->query['end_date'];
			$this->set(compact('end_date'));
		}
		$category_ids = "";
		if(array_key_exists('category',$this->request->query)&& !empty($this->request->query['category'][0])){
			$category_id = $this->request->query['category'];
			$category_ids = implode("_",$category_id);
			$ids_query = $this->Products->find('list',[
                                                'conditions' => ['category_id IN' => $category_id],
                                                'keyField' => 'id',
                                                'valueField' => 'id'
											    ]);
            $ids_query = $ids_query->hydrate(false);
            if(!empty($ids_query)){
                $ids = $ids_query->toArray();
            }else{
                $ids = array();
            }
		}
		//pr($this->request);die;
		$kiosk_id = "";
		if(array_key_exists('ProductSale',$this->request->query)){
			$kiosk_id = $this->request->query['ProductSale']['kiosk_id'];
		}
		if($kiosk_id == -1){
			return $this->redirect("/KioskProductSales/search_all_wholesale_kiosk_sale?start_date=$start_date&end_date=$end_date&kiosk_id=-1&search_kw=$searchKW&category_id=$category_ids");die;
		}
		//echo "hi";die;
		if($kiosk_id == 10000){
            $ProductReceipts_source = "product_receipts";
            $ProductReceiptsTable = TableRegistry::get($ProductReceipts_source,[
                                                                    'table' => $ProductReceipts_source,
                                                                        ]);
            $KioskProductSalesTable_source = "kiosk_product_sales";
            $KioskProductSalesTable = TableRegistry::get($KioskProductSalesTable_source,[
                                                                    'table' => $KioskProductSalesTable_source,
                                                                        ]);
		}else{
            if(empty($kiosk_id)){
				$kiosk_id = $this->request->Session()->read('kiosk_id');
			}
            if(empty($kiosk_id)){
				 $ProductReceipts_source = "product_receipts";
                $ProductReceiptsTable = TableRegistry::get($ProductReceipts_source,[
                                                                        'table' => $ProductReceipts_source,
                                                                            ]);
                $KioskProductSalesTable_source = "kiosk_product_sales";
                $KioskProductSalesTable = TableRegistry::get($KioskProductSalesTable_source,[
                                                                        'table' => $KioskProductSalesTable_source,
                                                                        ]);
				$kiosk_id = 10000;
			}else{
                    $ProductReceipts_source = "kiosk_{$kiosk_id}_product_receipts";
                    $ProductReceiptsTable = TableRegistry::get($ProductReceipts_source,[
                                                                            'table' => $ProductReceipts_source,
                                                                                ]);
                    $KioskProductSalesTable_source = "kiosk_{$kiosk_id}_product_sales";
                    $KioskProductSalesTable = TableRegistry::get($KioskProductSalesTable_source,[
                                                                    'table' => $KioskProductSalesTable_source,
                                                                        ]);
			}
           
		}
				
		if(array_key_exists('start_date',$this->request->query) &&
		   array_key_exists('end_date',$this->request->query) &&
		   !empty($this->request->query['start_date']) &&
		   !empty($this->request->query['end_date'])){
			$conditionArr[] = array(
						"created >=" => date('Y-m-d', strtotime($this->request->query['start_date'])),
						"created <" => date('Y-m-d', strtotime($this->request->query['end_date']. ' +1 Days')),			
					       );
		}
		
		if(array_key_exists('search_kw',$this->request->query) && !empty($this->request->query['search_kw'])){
			$searchW = strtolower($searchKW);
            $conn = ConnectionManager::get('default');
            $stmt = $conn->execute("SELECT `id` from `products` WHERE LOWER(`product_code`) like ('%{$searchW}%') or LOWER(`product`) like ('%{$searchW}%')");
            $productResult = $stmt ->fetchAll('assoc');		
			$productIDs = array();
			
			foreach($productResult as $sngproductResult){
				$productIDs[] = $sngproductResult['id'];
			}
			//pr($productIDs);
			$this->set('search_kw',$this->request->query['search_kw']);
		}
		
		$p_ids = array();
		if(!empty($ids)&&!empty($productIDs)){
			$p_ids = array_merge($productIDs,$ids);
		}elseif(!empty($productIDs)){
			$p_ids = $productIDs;
		}elseif(!empty($ids)){
			$p_ids = $ids;
		}
		
		if(!empty($p_ids)){
			$conditionArr['OR']['product_id IN'] =  $p_ids;
		}
		$conditionArr['sale_type'] = 1;
		if(array_key_exists('receipt_id',$this->request->query) && !empty($this->request->query['receipt_id'])){
			$receipt_id = $this->request->query['receipt_id'];
			$conditionArr['product_receipt_id IN'] =  $receipt_id;
			$this->set('receipt_id',$this->request->query['receipt_id']);
		}
		$conditionArr['refund_status'] = '<> 1';
		$result_query = $KioskProductSalesTable->find('all',array(
															'conditions'=>$conditionArr,
															'recursive'=> -1,
															'fields' => array('id','product_receipt_id',)));
        $result_query = $result_query->hydrate(false);
        if(!empty($result_query)){
            $result = $result_query->toArray();
        }else{
            $result = array();
        }
		 
		$receiptArray = array();
		$receipt_Ids = array();
		
		if(count($result)){
			foreach($result as $rk => $results){
				$receiptArray[$results['product_receipt_id']] = $results['id'];
			}
			
			$receipt_Ids = array_keys($receiptArray);
			$sumTotalArr_query = $ProductReceiptsTable->find('all',array(
																	 'conditions'=> array('id IN' => $receipt_Ids),
																	 'fields'=>array('id','bill_amount','vat'),
																	 ));
            $sumTotalArr_query = $sumTotalArr_query->hydrate(false);
            if(!empty($sumTotalArr_query)){
                $sumTotalArr = $sumTotalArr_query->toArray();
            }else{
                $sumTotalArr = array();
            }
		}
		//pr($sumTotalArr);
		$totalVat = $netAmount = $sumTotal = 0;
		if(!empty($sumTotalArr)){
			foreach($sumTotalArr as $s_key => $s_val){
				$vat_applicable = $amount = 0;
				$amount = $s_val['bill_amount'];
				$vat_applicable = $s_val['vat'];
				if($vat_applicable == 20){
					$temp_net_amount = $amount/(1+$actualVat/100);
					$netAmount += $temp_net_amount;
					$tempVat = $amount - $temp_net_amount;
					$totalVat += $tempVat;
				}else{
					$netAmount += $amount;
				}
			}
		}
		if(empty($receipt_Ids)){
			$receipt_Ids = array(0 => null);
		}
		$conditionArr['product_receipt_id IN'] = $receipt_Ids;
		$conditionArr['refund_status'] = '<> 1';
		$this->paginate = [
                            'conditions' => $conditionArr,
                            'limit' => ROWS_PER_PAGE,
                            'order' => ['id DESC'],
                          ];
		//die;
		$kioskProductSales_query = $this->paginate($KioskProductSalesTable);
        if(!empty($kioskProductSales_query)){
            $kioskProductSales = $kioskProductSales_query->toArray();
        }else{
            $kioskProductSales = array();
        }
        $y_product_recepit_ids = array();
        foreach($kioskProductSales as $product_recepitArr){
            $y_product_recepit_ids[] = $product_recepitArr['product_receipt_id'];
        }
        if(empty($y_product_recepit_ids)){
            $y_product_recepit_ids = array(0 => null);
        }
        $recepit_table_data_query = $ProductReceiptsTable->find('all',[
                                                                        'conditions' => ['id IN' => $y_product_recepit_ids]
                                                                        ]
                                                                );
        $recepit_table_data_query = $recepit_table_data_query->hydrate(false);
        if(!empty($recepit_table_data_query)){
            $recepit_table_data = $recepit_table_data_query->toArray();
        }else{
            $recepit_table_data = array();
        }
		//pr($recepit_table_data);die;
        $recepitTableData = array();
        foreach($recepit_table_data as $recepit_keys => $recepit_values){
            $recepitTableData[$recepit_values['id']] = $recepit_values;
        }
        $this->set(compact('recepitTableData'));
        //pr($recepitTableData);die;
        
        //pr($y_product_recepit_ids);die;
		//pr($kioskProductSales);die;
		$hint = $this->ScreenHint->hint('kiosk_product_sales','index');
					if(!$hint){
						$hint = "";
					}
	    $orignal_amount = array();
		//pr($receipt_Ids);
		$productReceiptDetail_query = $ProductReceiptsTable->find('all',array('conditions' =>array('id IN' => $receipt_Ids),'fields'=>array('id','orig_bill_amount')));
        $productReceiptDetail_query = $productReceiptDetail_query->hydrate(false);
        if(!empty($productReceiptDetail_query)){
            $productReceiptDetail = $productReceiptDetail_query->toArray();
        }else{
            $productReceiptDetail = array();
        }
		//pr($productReceiptDetail);
		foreach($productReceiptDetail  as $key => $value){
			if(empty($value["orig_bill_amount"])){
				$value["orig_bill_amount"] = 0;
			}
			$orignal_amount[$value["id"]] = $value["orig_bill_amount"];
		}
		if(!empty($conditionArr)){
			if(array_key_exists("refund_status",$conditionArr)){
				unset($conditionArr["refund_status"]);
			}
			if(array_key_exists("product_receipt_id IN",$conditionArr)){
				unset($conditionArr["product_receipt_id IN"]);
			}
		}
        $conditionArr['refund_status IN'] = array(1, 2);
        
        $query_kiosk_sales = $KioskProductSalesTable->find('all',['conditions' => $conditionArr]);
        $query_kiosk_sales
                ->select(['totalrefund' => $query_kiosk_sales->func()->sum('refund_price*quantity')]);
        $query_kiosk_result = $query_kiosk_sales->hydrate(false);
        if(!empty($query_kiosk_result)){
            $refundSumData = $query_kiosk_result->first();
        }else{
            $refundSumData = array();
        }
        unset($conditionArr['refund_status IN']);
		$refundData = $refundSumData["totalrefund"];
		//pr($orignal_amount);die;
		$categories_query = $this->Categories->find('all',array(
								'fields' => array('id', 'category','id_name_path'),
								'conditions' => array('Categories.status' => 1),
								'order' => 'Categories.category asc'
								));
        $categories_query = $categories_query->hydrate(false);
        if(!empty($categories_query)){
            $categories = $categories_query->toArray();
        }else{
            $categories = array();
        }
		//$categories = $this->CustomOptions->category_options($categories,true);
		if(isset($category_id) && !empty($category_id)){
			$categories = $this->CustomOptions->category_options($categories,true,$category_id);	
		}else{
			$categories = $this->CustomOptions->category_options($categories,true);
		}
		
		$this->set(compact('kiosk_id','orignal_amount','refundData','categories','kiosks'));
		$this->set(compact('hint','kioskProductSales','products','users','actualVat','sumTotal','netAmount','totalVat'));
		//$this->layout = 'default';
		$this->render('search_wholsale_sale');
    }
    
    public function searchAllWholesaleKioskSale()
    {
		//echo "hi";die;
		$products_query = $this->Products->find('all',array(
													 'fields' => array('id','product','product_code'),
													 ));
        $products_query = $products_query->hydrate(false);
        if(!empty($products_query)){
            $products = $products_query->toArray();
        }else{
            $products = array();
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
		$vat = $this->VAT;
		$this->set(compact('vat'));
		$kiosks_query = $this->Kiosks->find('list', [
														'keyField' => 'id',
														'valueField' => 'name',
														'conditions' => ['Kiosks.status' => 1],
														'order'=>['Kiosks.name asc']
													]
											);
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
		$credit_conditionArr = array();
		if(!empty($start_date) && !empty($end_date)){
			$credit_conditionArr[] = array(
						"created >" => date('Y-m-d', strtotime($this->request->query['start_date'])),
						"created <" => date('Y-m-d', strtotime($this->request->query['end_date']. ' +1 Days')),			
					       );
			$conditionArr[] = array(
						"created >" => date('Y-m-d', strtotime($this->request->query['start_date'])),
						"created <" => date('Y-m-d', strtotime($this->request->query['end_date']. ' +1 Days')),			
					       );
			$paymentConditionArr[] = array(
						//"created >" => date('Y-m-d', strtotime($this->request->query['start_date'])),
						//"created <" => date('Y-m-d', strtotime($this->request->query['end_date']. ' +1 Days')),			
					       );
			$wholesaleConditionArr[] = array(
						"created >" => date('Y-m-d', strtotime($this->request->query['start_date'])),
						"created <" => date('Y-m-d', strtotime($this->request->query['end_date']. ' +1 Days')),		
							);
		}
		
		$catagriy_ids = array();
		$productIDs = $product_ids = $catagriy_ids = array();
		if(array_key_exists('category_id',$this->request->query)){
			$catagory = $this->request->query['category_id'];
			$this->set(compact('catagory'));
			$catagriy_ids = explode("_",$catagory);
			if(empty($catagriy_ids)){
				$catagriy_ids = array(0=>null);
			}
			$product_ids_query = $this->Products->find('list',[
                                                        'conditions' => ['category_id IN' => $catagriy_ids],
                                                        'keyField' => 'id',
                                                        'valueField' => 'id'
                                                        ]);
            $product_ids_query = $product_ids_query->hydrate(false);
            if(!empty($product_ids_query)){
                $product_ids = $product_ids_query->toArray();
            }else{
                $product_ids = array();
            }
		}
		
		$searchKW = '';
		if(array_key_exists('search_kw',$this->request->query) && !empty($this->request->query['search_kw'])){
			$searchKW = $this->request->query['search_kw'];
			$searchW = strtolower($searchKW);
            $conn = ConnectionManager::get('default');
            $stmt = $conn->execute("SELECT `id` from `products` WHERE LOWER(`product_code`) like ('%{$searchW}%') or LOWER(`product`) like ('%{$searchW}%')");
            $productResult = $stmt ->fetchAll('assoc');			
			$productIDs = array();
			
			foreach($productResult as $sngproductResult){
				$productIDs[] = $sngproductResult['id'];
			}
			$this->set('search_kw',$this->request->query['search_kw']);
		}
		
		$p_ids = array();
		if(!empty($product_ids)&&!empty($productIDs)){
			$p_ids = array_merge($productIDs,$product_ids);
		}elseif(!empty($productIDs)){
			$p_ids = $productIDs;
		}elseif(!empty($product_ids)){
			$p_ids = $product_ids;
		}
		
		
		if(!empty($p_ids)){
			$conditionArr['AND']['product_id IN'] = $p_ids;
			$credit_conditionArr['AND']['product_id IN'] = $p_ids;
		}
		
		
		
		$paymentDetail = $receiptIds = $refundData = $saleSumData = $cardPayment = $cashPayment = array();
		$conditionArr['sale_type'] = 1;
		$bulk_dis_arr = array();
		foreach($kiosks as $kioskId => $kioskName){
			
			$ids = array();
            if($kioskId == 10000){
				$recipt_table_source = "product_receipts";
				$recieptTable = TableRegistry::get($recipt_table_source,[
                                                                        'table' => $recipt_table_source,
                                                                            ]);
				
                $KioskProductSalesTable_source = "kiosk_product_sales";
                $KioskProductSalesTable = TableRegistry::get($KioskProductSalesTable_source,[
                                                                        'table' => $KioskProductSalesTable_source,
                                                                            ]);
                $PaymentDetails_source = "payment_details";
                $PaymentDetailsTable = TableRegistry::get($PaymentDetails_source,[
                                                                        'table' => $PaymentDetails_source,
                                                                            ]);
            }else{
				$recipt_table_source = "kiosk_{$kioskId}_product_receipts";
				$recieptTable = TableRegistry::get($recipt_table_source,[
                                                                        'table' => $recipt_table_source,
                                                                            ]);
				
                $KioskProductSalesTable_source = "kiosk_{$kioskId}_product_sales";
                $KioskProductSalesTable = TableRegistry::get($KioskProductSalesTable_source,[
                                                                        'table' => $KioskProductSalesTable_source,
                                                                            ]);
                $PaymentDetails_source = "kiosk_{$kioskId}_payment_details";
                $PaymentDetailsTable = TableRegistry::get($PaymentDetails_source,[
                                                                        'table' => $PaymentDetails_source,
                                                                            ]);
            }
            $conditionArr['refund_status'] = 0;
            //pr($conditionArr);die;
			$ids_query = $KioskProductSalesTable->find('list',[
                                                         'keyField' => 'id',
                                                         'valueField' => 'product_receipt_id',
                                                         'conditions' => $conditionArr
                                                        ]);
            unset($conditionArr['refund_status']);
            $ids_query = $ids_query->hydrate(false);
            if(!empty($ids_query)){
                $ids = $ids_query->toArray();
            }else{
                $ids = array();
            }
			if(empty($ids)){
				$ids = array(0);
			}
			$saledata = $KioskProductSalesTable->find('all',array(
													  'conditions' => $conditionArr,
													  'fields' => array('product_id','kiosk_id','quantity','cost_price','sale_price','product_receipt_id','discount_status','discount','cost_price','created')
													  ));
            $saledata = $saledata->hydrate(false);
            if(!empty($saledata)){
                $saledata = $saledata->toArray();
            }else{
                $saledata = array();
            }
			
			
			//if($kioskId == 2){
				$recipt_ids = array();
				foreach($saledata as $key => $value){
					$recipt_ids[] = $value['product_receipt_id'];
				}
				if(empty($recipt_ids)){
					$recipt_ids = array(0 => null);
				}
				$bulk_dis_query = $recieptTable->find('list',array(
													'conditions' => array(
																			'id IN' => $recipt_ids,
													),
													'keyField' => 'id',
													'valueField' => 'bulk_discount',
												));
				$bulk_dis_query = $bulk_dis_query->hydrate(false);
				if(!empty($bulk_dis_query)){
					$res_blk = $bulk_dis_query->toArray();
					$bulk_dis_arr[$kioskId] = $res_blk;
				}else{
					$bulk_dis_arr[$kioskId] = array();
				}
				
			//}
			
			
			$sale_Arr[$kioskId] = $saledata;
			
			//pr($credit_conditionArr);die;
			
			$refundData[$kioskId] = $this->get_credit_data($kioskId,$credit_conditionArr);
			
			//--------------------------------------------------------------------------------------
            //$conditionArr['refund_status IN'] = array(1,2);
            ////pr($conditionArr);die;
            //$query_kiosk_sale = $KioskProductSalesTable->find('all',['conditions' => $conditionArr]);
            //      $query_kiosk_sale
            //              ->select(['todayProductRefund' => $query_kiosk_sale->func()->sum('refund_price * quantity')]);
            //$query_result = $query_kiosk_sale->hydrate(false);
            //unset($conditionArr['refund_status IN']);
            //if(!empty($query_result)){
            //    $refundData[$kioskId] = $query_result->first();
            //}else{
            //    $refundData[$kioskId] = array();
            //}
            //---------------------------------------------------------------------------------------
			
			if(!empty($searchKW) || !empty($catagory)){
				if(empty($ids)){
					$ids = array(null);
				}
               // $paymentConditionArr['product_receipt_id IN'] = $ids;
				$recipt_res_query = $recieptTable->find('list',array(
												'conditions' => array('id IN' => $ids),
												'keyField' => "id",
												'valueField' => "vat",
												));
				$recipt_res_query = $recipt_res_query->hydrate(false);
				if(!empty($recipt_res_query)){
					$recipt_res = $recipt_res_query->toArray();
				}else{
					$recipt_res = array();
				}
				if(!empty($recipt_res)){
					$with_vat_ids = array_keys(array_filter($recipt_res));
					$without_vat_ids = array_diff($ids,$with_vat_ids);	
				}else{
					$with_vat_ids = $without_vat_ids = array(0=>null);
				}
				
				if(empty($with_vat_ids)){
					$with_vat_ids = array(0=>null);
				}
				
				
				$paymentConditionArr['product_receipt_id IN'] = $with_vat_ids;
				$query_payment_detail_with_vat = $PaymentDetailsTable->find('all',['conditions' => $paymentConditionArr]);
                $query_payment_detail_with_vat
                          ->select(['totalsale' => $query_payment_detail_with_vat->func()->sum('amount')]);
                $query_payment_detail_with_vat = $query_payment_detail_with_vat->hydrate(false);
				if(!empty($query_payment_detail_with_vat)){
					$payment_detail_with_vat_res  = $query_payment_detail_with_vat->first();
					$payment_detail_with_vat = $payment_detail_with_vat_res['totalsale'];
				}else{
					$payment_detail_with_vat = 0;
				}
				
				
				if(empty($without_vat_ids)){
					$without_vat_ids = array(0 => null);
				}
			
				
				
				$paymentConditionArr['product_receipt_id IN'] = $without_vat_ids;
				$query_payment_detail_without_vat = $PaymentDetailsTable->find('all',['conditions' => $paymentConditionArr]);
                $query_payment_detail_without_vat
                          ->select(['totalsale' => $query_payment_detail_without_vat->func()->sum('amount')]);
                $query_payment_detail_without_vat = $query_payment_detail_without_vat->hydrate(false);
				if(!empty($query_payment_detail_with_vat)){
					$payment_detail_without_vat_res  = $query_payment_detail_without_vat->first();
					$payment_detail_without_vat = $payment_detail_without_vat_res['totalsale'];
					
					$payment_detail_without_vat = $payment_detail_without_vat + $payment_detail_without_vat*($vat/100);
					
				}else{
					$payment_detail_without_vat = 0;
				}
				
				unset($paymentConditionArr['product_receipt_id IN']);
                if(!empty($payment_detail_with_vat) || !empty($payment_detail_without_vat)){
                    $saleSumData[$kioskId]['totalsale'] = $payment_detail_with_vat + $payment_detail_without_vat;													//$query_payment_result->first();
                }else{
                    $saleSumData[$kioskId]['totalsale']	 = 0;
                }
				
				
                //$query_payment_detail = $PaymentDetailsTable->find('all',['conditions' => $paymentConditionArr]);
                //$query_payment_detail
                //          ->select(['totalsale' => $query_payment_detail->func()->sum('amount')]);
                //$query_payment_result = $query_payment_detail->hydrate(false);
                //unset($paymentConditionArr['product_receipt_id IN']);
                //if(!empty($query_payment_result)){
                //    $saleSumData[$kioskId] = $query_payment_result->first();
                //}else{
                //    $saleSumData[$kioskId] = array();
                //}
				
				
                
			}else{
				
				
				//$paymentConditionArr['product_receipt_id IN'] = $ids;
				
				$recipt_res_query = $recieptTable->find('list',array(
												'conditions' => array('id IN' => $ids),
												'keyField' => "id",
												'valueField' => "vat",
												));
				$recipt_res_query = $recipt_res_query->hydrate(false);
				if(!empty($recipt_res_query)){
					$recipt_res = $recipt_res_query->toArray();
				}else{
					$recipt_res = array();
				}
				
				
				
				if(!empty($recipt_res)){
					$with_vat_ids = array_keys(array_filter($recipt_res));
					$without_vat_ids = array_diff($ids,$with_vat_ids);	
				}else{
					$with_vat_ids = $without_vat_ids = array(0=>null);
				}
				
				
				
				if(empty($with_vat_ids)){
					$with_vat_ids = array(0 => null);
				}
				
				
				
					$paymentConditionArr['product_receipt_id IN'] = $with_vat_ids;
				$query_payment_detail_with_vat = $PaymentDetailsTable->find('all',['conditions' => $paymentConditionArr]);
                $query_payment_detail_with_vat
                          ->select(['totalsale' => $query_payment_detail_with_vat->func()->sum('amount')]);
                $query_payment_detail_with_vat = $query_payment_detail_with_vat->hydrate(false);
				if(!empty($query_payment_detail_with_vat)){
					$payment_detail_with_vat_res  = $query_payment_detail_with_vat->first();
					$payment_detail_with_vat = $payment_detail_with_vat_res['totalsale'];
				}else{
					$payment_detail_with_vat = 0;
				}
				
				//if(!empty($payment_detail_with_vat)){
				//	$payment_detail_with_vat_changed =  $payment_detail_with_vat / (1+($vat/100));
				//}else{
				//	$payment_detail_with_vat_changed = 0;
				//}
				
				if(empty($without_vat_ids)){
					$without_vat_ids = array(0 => null);
				}
				
				$paymentConditionArr['product_receipt_id IN'] = $without_vat_ids;
				$query_payment_detail_without_vat = $PaymentDetailsTable->find('all',['conditions' => $paymentConditionArr]);
                $query_payment_detail_without_vat
                          ->select(['totalsale' => $query_payment_detail_without_vat->func()->sum('amount')]);
                $query_payment_detail_without_vat = $query_payment_detail_without_vat->hydrate(false);
				if(!empty($query_payment_detail_with_vat)){
					$payment_detail_without_vat_res  = $query_payment_detail_without_vat->first();
					$payment_detail_without_vat = $payment_detail_without_vat_res['totalsale'];
					
					$payment_detail_without_vat = $payment_detail_without_vat + $payment_detail_without_vat*($vat/100);
					
				}else{
					$payment_detail_without_vat = 0;
				}
				
				
							
				
                //$query_payment_detail = $PaymentDetailsTable->find('all',['conditions' => $paymentConditionArr]);
                //$query_payment_detail
                //          ->select(['totalsale' => $query_payment_detail->func()->sum('amount')]);
                //$query_payment_result = $query_payment_detail->hydrate(false);
                unset($paymentConditionArr['product_receipt_id IN']);
                if(!empty($payment_detail_with_vat) || !empty($payment_detail_without_vat)){
                    $saleSumData[$kioskId]['totalsale'] = $payment_detail_with_vat + $payment_detail_without_vat;													//$query_payment_result->first();
                }else{
                    $saleSumData[$kioskId]['totalsale']	 = 0;
                }
			}
			
		}
		
		
		foreach($kiosks as $kioskId => $kioskName){
			if($kioskId == 10000){
                $KioskProductSalesTable_source = "kiosk_product_sales";
                $KioskProductSalesTable = TableRegistry::get($KioskProductSalesTable_source,[
                                                                        'table' => $KioskProductSalesTable_source,
                                                                            ]);
			}else{
                $KioskProductSalesTable_source = "kiosk_{$kioskId}_product_sales";
                $KioskProductSalesTable = TableRegistry::get($KioskProductSalesTable_source,[
                                                                        'table' => $KioskProductSalesTable_source,
                                                                            ]);
			}
			$conditionArr['refund_status'] = 0;
            $query_koisk_detail = $KioskProductSalesTable->find('all',['conditions' => $conditionArr]);
            $query_koisk_detail
                      ->select(['total_qantity' => $query_koisk_detail->func()->sum('quantity')]);
            $query_kiosk_result = $query_koisk_detail->hydrate(false);
            unset($conditionArr['refund_status']);
            if(!empty($query_kiosk_result)){
                $qntityData = $query_kiosk_result->first();
            }else{
                $qntityData = array();
            }

			if(!empty($qntityData)){
				$qanArr[$kioskId] = $qntityData['total_qantity'];
			}else{
				$qanArr[$kioskId] = 0;
			}
		}
		
		$categories_query = $this->Categories->find('all',array(
							'fields' => array('id', 'category','id_name_path'),
							'conditions' => array('Categories.status' => 1),
							'order' => 'Categories.category asc'
							));
        $categories_query = $categories_query->hydrate(false);
        if(!empty($categories_query)){
            $categories = $categories_query->toArray();
        }else{
            $categories = array();
        }
		$categories = $this->CustomOptions->category_options($categories,true,$catagriy_ids);
		//pr($credit_conditionArr);die;
        
		list($t_data,$t_sale_Arr) = $this->t_searched_data($conditionArr,$paymentConditionArr,$credit_conditionArr);
		$t_cost_price = $t_qantity = array();
		//pr($t_sale_Arr);die;
		foreach($t_sale_Arr as $k_id => $val){
			if(!empty($val)){
				foreach($val as $s => $k){
					
					if($s == 'recipt_data'){
							$t_bulk_dis_arr[$k_id] = $val[$s];
							unset($t_sale_Arr[$k_id]['recipt_data']);
							
					}
					if(!empty($k)){
						if(!array_key_exists($k_id,$t_cost_price)){
							$t_cost_price[$k_id]=0;
							$t_qantity[$k_id] = 0;
						}
						if(array_key_exists('cost_price',$k) && array_key_exists('quantity',$k)){
							$t_cost_price[$k_id] += $k['cost_price'] * $k['quantity'];
							$t_qantity[$k_id] += $k['quantity'];		
						}
						
					}
				}
			}
		}
      // pr($t_sale_Arr);die;
		$this->set(compact('products','users','kiosks', 'paymentMode', 'end_date', 'start_date', 'saleSumData','wholesale_Arr','refundData', 'cardPayment', 'cashPayment','sum_arr','sale_Arr','retail','wholesale','qanArr','categories','t_data','t_sale_Arr','t_cost_price','t_qantity','bulk_dis_arr','t_bulk_dis_arr'));
		
		$this->render('all_wholesale_kiosk_sale');
    }
	
	public function get_credit_data($kioskId = "",$credit_conditionArr = array()){
		//$kioskId = "10000";
		//echo $kioskId;die;
		$this->loadModel("CreditProductDetails");
		if($kioskId == 10000){
			$CreditReceiptSource = "credit_receipts";
			$CreditProductDetailSource = "credit_product_details";
		}else{
			$CreditReceiptSource = "kiosk_{$kioskId}_credit_receipts";
			$CreditProductDetailSource = "kiosk_{$kioskId}_credit_product_details";
		}
		$CreditReceiptTable = TableRegistry::get($CreditReceiptSource,[
                                                                        'table' => $CreditReceiptSource,
                                                                            ]);
		$CreditProductDetailTable = TableRegistry::get($CreditProductDetailSource,[
                                                                        'table' => $CreditProductDetailSource,
                                                                            ]);
		$productIdFound = 0;
		if(array_key_exists("AND",$credit_conditionArr)){
			$new_codition_arry = array();
				if(array_key_exists(0,$credit_conditionArr)){
					$new_codition_arry[] = array(
						"created >" => $credit_conditionArr[0]["created >"],
						"created <" => $credit_conditionArr[0]["created <"],			
					       );
					$new_codition_arry['AND'] = $credit_conditionArr['AND'];
					unset($credit_conditionArr[0]);
				}else{
					$new_codition_arry[] = $credit_conditionArr['AND'];
				}
				
			$recipt_ids_query = $CreditProductDetailTable->find("list",[
																	'conditions' => $new_codition_arry,
																	'keyField' => 'id',
																	'valueField' => 'credit_receipt_id'
																 ]
														  );
			
			$recipt_ids_query = $recipt_ids_query->hydrate(false);
			if(!empty($recipt_ids_query)){
				$recipt_ids = $recipt_ids_query->toArray();
			}else{
				$recipt_ids = array();
			}
			
			if(!empty($recipt_ids)){
				$credit_conditionArr['id IN'] = $recipt_ids;		
			}
		$productIdFound = 1;
		unset($credit_conditionArr['AND']);
		}
//		pr($credit_conditionArr);die;
		if($productIdFound == 1 && empty($credit_conditionArr)){
			$res = array();
		}else{
			$res_query = $CreditReceiptTable->find("all",array('conditions' => $credit_conditionArr,'fields' =>array('id','vat','credit_amount')));
			//pr($res_query);//die;
			
			$res_query = $res_query->hydrate(false);
			if(!empty($res_query)){
				$res = $res_query->toArray();
			}else{
				$res = array();
			}
		}
		
		//if($kioskId == 2){
			//pr($res);
		//}
		$final_amount = 0;
		if(!empty($res)){
			foreach($res as $kry => $value){
				$amount = $value['credit_amount'];
				$vat = $value['vat'];
				if($vat > 0){
					$amount = ($amount /(1+($vat/100)));
				}
				$final_amount += $amount;
			}
			$final_amount;
		}
		
		return $final_amount;
	}
    
    public function t_searched_data($conditionArr = array(),$paymentConditionArr = array() ,$credit_conditionArr=array()){
		if(array_key_exists('sale_type',$conditionArr)){
			unset($conditionArr['sale_type']);
		}
		$KioskProductSalesTable_source = "t_kiosk_product_sales";
        $KioskProductSalesTable = TableRegistry::get($KioskProductSalesTable_source,[
                                                                        'table' => $KioskProductSalesTable_source,
                                                                            ]);
        $ProductReceipts_source = "t_product_receipts";
        $ProductReceiptsTable = TableRegistry::get($ProductReceipts_source,[
                                                                        'table' => $ProductReceipts_source,
                                                                            ]);
        $PaymentDetails_source = "t_payment_details";
        $PaymentDetailsTable = TableRegistry::get($PaymentDetails_source,[
                                                                        'table' => $PaymentDetails_source,
                                                                            ]);
		$CreditReceiptSource = "t_credit_receipts";
		$CreditReceiptTable = TableRegistry::get($CreditReceiptSource,[
                                                                        'table' => $CreditReceiptSource,
                                                                            ]);
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
		$CreditProductDetailSource = "t_credit_product_details";
		$CreditProductDetailTable = TableRegistry::get($CreditProductDetailSource,[
                                                                        'table' => $CreditProductDetailSource,
                                                                            ]);
		$productIdFound = 0;
		if(array_key_exists("AND",$credit_conditionArr)){
			$new_codition_arry = array();
				if(array_key_exists(0,$credit_conditionArr)){
					$new_codition_arry[] = array(
						"created >" => $credit_conditionArr[0]["created >"],
						"created <" => $credit_conditionArr[0]["created <"],			
					       );
					$new_codition_arry['AND'] = $credit_conditionArr['AND'];
					unset($credit_conditionArr[0]);
				}else{
					$new_codition_arry[] = $credit_conditionArr['AND'];
				}
				
			$recipt_ids_query = $CreditProductDetailTable->find("list",[
																	'conditions' => $new_codition_arry,'keyField' => 'id',
																	'valueField' => 'credit_receipt_id'
																 ]
														  );
			$recipt_ids_query = $recipt_ids_query->hydrate(false);
			if(!empty($recipt_ids_query)){
				$recipt_ids = $recipt_ids_query->toArray();
			}else{
				$recipt_ids = array();
			}
			if(!empty($recipt_ids)){
				$credit_conditionArr['id IN'] = $recipt_ids;	
			}
			$productIdFound = 1;
			unset($credit_conditionArr['AND']);
		}
		$refundData = $t_saleSumData = array();
		$bulk_dis_arr = $saleSumData = array();
		//pr($kiosks);die;
		foreach($kiosks as $kioskId => $kioskName){
			if($kioskId == 10000){
				$kioskId = 0;
			}
            $conditionArr['refund_status'] = 0;
            $conditionArr['kiosk_id'] = $kioskId;
            //if($kioskId == 3){
            //    pr($conditionArr);
            //}
			$ids_query = $KioskProductSalesTable->find('list',[
                                                         'keyField' => 'id',
                                                         'valueField' => 'product_receipt_id',
                                                         'conditions' => [$conditionArr]
                                                        ]);
            
            //if($kioskId == 3){
            //    pr($ids_query);die;
            //}
            unset($conditionArr['refund_status']);
            unset($conditionArr['kiosk_id']);
            $ids_query = $ids_query->hydrate(false);
            if(!empty($ids_query)){
                $ids = $ids_query->toArray();
            }else{
                $ids = array();
            }
            //if($kioskId == 3){
            //   pr($ids);die;  
            //}
            
			if(empty($ids)){
				$ids = array(0);
			}
			if($productIdFound == 1 && !empty($credit_conditionArr)){
				$credit_conditionArr['kiosk_id'] = $kioskId;
			}
			//pr($credit_conditionArr);die;
			if($productIdFound == 1 && !empty($credit_conditionArr)){
				$refundData_res_query = $CreditReceiptTable->find('all',['conditions' => $credit_conditionArr]);
					$refundData_res_query
							  ->select(['todayProductRefund' => $refundData_res_query->func()->sum('credit_amount')]);
							  //pr($refundData_res_query);die;
							  
							  
				$refundData_res_query = $refundData_res_query->hydrate(false);
				if(!empty($refundData_res_query)){	
					$refundData_res = $refundData_res_query->first();
				}else{
					$refundData_res = array();
				}
			}else{
				$refundData_res['todayProductRefund'] = 0;
			}
			
			
			//pr($refundData_res);
			//$refundData_res = $CreditReceiptTable->find("first",array('conditions' => $credit_conditionArr,'fields' =>array('SUM(credit_amount) as todayProductRefund')));
			
			//pr($conditionArr);die;
			
			$refundData[$kioskId] = $refundData_res['todayProductRefund'];
            //pr($conditionArr);
            $saledata_query = $KioskProductSalesTable->find('all',array(
													  'conditions' => array($conditionArr,'kiosk_id' => $kioskId),
													  'fields' => array('product_id','kiosk_id','quantity','cost_price','sale_price','product_receipt_id','discount_status','discount','cost_price')
													  ));
			//pr($saledata_query);die;
            unset($conditionArr['kiosk_id']);
            $saledata_query = $saledata_query->hydrate(false);
            if(!empty($saledata_query)){
                $saledata = $saledata_query->toArray();
            }else{
                $saledata = array();
            }
            //pr($saledata);die;
			$sale_Arr[$kioskId] = $saledata;
			
			$recipt_ids_arr = array();
			foreach($saledata as $key => $value){
				$recipt_ids_arr[] = $value['product_receipt_id'];
			}
			
			if(empty($recipt_ids_arr)){
				$recipt_ids_arr = array(null);
			}
			$bulk_dis_query = $ProductReceiptsTable->find('list',array(
													'conditions'=> array('id IN' => $recipt_ids_arr),
														'keyField' => 'id',
														'valueField' => 'bulk_discount',
													));
			$bulk_dis_query = $bulk_dis_query->hydrate(false);
            if(!empty($bulk_dis_query)){
				$sale_Arr[$kioskId]['recipt_data'] =  $bulk_dis_query->toArray();
			}else{
				$sale_Arr[$kioskId]['recipt_data'] =  array();
			}
			
			
			
			$query_payment_recepit = $ProductReceiptsTable->find('all',array('conditions'=>array('id IN' => $ids),
													'fields' => array('orig_bill_amount','vat')
													)
										);
            $query_payment_recepit = $query_payment_recepit->hydrate(false);
            if(!empty($query_payment_recepit)){
                $orgData[$kioskId] = $query_payment_recepit->toArray();
            }else{
                $orgData[$kioskId] = array();
            }
            
			$sale_amount = 0;
			if(!empty($orgData[$kioskId])){
				foreach($orgData[$kioskId] as $sum_key => $sum_value){
					$org_amount = $sum_value['orig_bill_amount'];
					$vat = $sum_value['vat'];
					//if(!empty($vat)){
					//	$sale_amount += $org_amount/(1+($vat/100));
					//}else{
						$sale_amount += $org_amount;
					//}
				}
			}
			$saleSumData[$kioskId] = $sale_amount;
		}
		//pr($refundData);die;
		$saleSumData['refund_data'] = $refundData;
		//pr($saleSumData);die;
		return array($saleSumData,$sale_Arr);
	}	
	
	
    public function drIndex(){
		$this->check_dr5();
		//$this->ProductReceipt->setSource('t_product_receipts');
		$this->index("t_kiosk_product_sales");
	}
	
	public function drSearchsale($keyword = ""){//to search view sale (special invoice) as per admin navigation
		$this->check_dr5();
		//$this->KioskProductSale->setSource('t_kiosk_product_sales');
		//$this->ProductReceipt->setSource('t_product_receipts');
		$this->searchsale($keyword, 'dr_index');
	}
	
	private function check_dr5(){
		$loggedInUser = $this->request->session()->read('Auth.User.username');
		if (!preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
			$this->Flash->error(__('Sorry,This Page Is Not Existing.'));
				return $this->redirect(array('controller' => 'home','action' => 'dashboard'));
			die;
		}
	}
   
    public function index($kioskProductSalesSource = NULL) {
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		if(!empty($kiosk_id)){
			$receiptTable_source = "kiosk_{$kiosk_id}_product_receipts";
		}elseif(!empty($kioskProductSalesSource)){
			$receiptTable_source = "t_product_receipts";
		}else{
			$receiptTable_source = "product_receipts";
		}
		
		if(!empty($kioskProductSalesSource)){
			$receiptTable_source = "t_product_receipts";
		}
		
		$receiptTable = TableRegistry::get($receiptTable_source,[
                                                                    'table' => $receiptTable_source,
                                                                ]);
		
		$start_date = $end_date = date('d M Y');
		$this->set(compact('start_date','end_date'));
		$settingArr = $this->setting;
		$actualVat = $settingArr['vat'];
		
		if(!empty($kioskProductSalesSource)){
			
				$productReceiptDetail_query = $receiptTable->find('all',array('fields'=>array('id','vat','status','bill_amount','orig_bill_amount'),
																		'conditions' => array(
																							  "created >=" => date('Y-m-d'),
																	"created <" => date('Y-m-d',strtotime(' +1 day')),
																	'kiosk_id' => $kiosk_id
																							  ),
																		));	
			
		}else{
			if(($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS &&
			$this->request->session()->read('Auth.User.user_type')=='wholesale') ||
			$this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER
			){
				$productReceiptDetail_query = $receiptTable->find('all',array('fields'=>array('id','vat','status','bill_amount','orig_bill_amount'),
																			'conditions' => array(
																								  "created >=" => date('Y-m-d'),
																		"created <" => date('Y-m-d',strtotime(' +1 day')),
																		'sale_type' => 1
																								  ),
																			));
			}else{
				$productReceiptDetail_query = $receiptTable->find('all',array('fields'=>array('id','vat','status','bill_amount','orig_bill_amount'),
																			'conditions' => array(
																								  "created >=" => date('Y-m-d'),
																		"created <" => date('Y-m-d',strtotime(' +1 day')),
																		'sale_type' => 0
																								  ),
																			));
			}
		}
		
        $productReceiptDetail_query = $productReceiptDetail_query->hydrate(false);
        if(!empty($productReceiptDetail_query)){
            $productReceiptDetail = $productReceiptDetail_query->toArray();
        }else{
            $productReceiptDetail = array();
        }
		$sumTotal = 0;
		$netAmount = 0;
		$orignal_amount = array();
        //pr($productReceiptDetail);die;
		foreach($productReceiptDetail as $key=>$productReceiptDta){
           // pr($productReceiptDta);die;
			//showing the data with product receipt status == 0 ie. the payment went through and data saved properly
			if($productReceiptDta['status']==0){
				$paymentAmount = $productReceiptDta['bill_amount'];
				$sumTotal+=$paymentAmount;
				$vatPercentage = $productReceiptDta['vat']/100;
				$ntAmount = $paymentAmount/(1+$vatPercentage);
				$netAmount+=$ntAmount;
			}
			if(empty($productReceiptDta['orig_bill_amount'])){
				$productReceiptDta['orig_bill_amount'] = 0;
			}
			$orignal_amount[$productReceiptDta['id']] = $productReceiptDta['orig_bill_amount'];
		}
		$totalVat = $sumTotal - $netAmount;
	
		
		if($kioskProductSalesSource){
			$this->KioskProductSales->recursive = 0;
            $KioskProductSalesTable_source = "$kioskProductSalesSource";
            $KioskProductSalesTable = TableRegistry::get($KioskProductSalesTable_source,[
                                                                        'table' => $KioskProductSalesTable_source,
                                                                            ]);
			//$this->KioskProductSale->setSource($kioskProductSalesSource);
		}elseif((int)$kiosk_id){
			$this->initialize_tables($kiosk_id);
			$this->KioskProductSales->recursive = 0;
            $KioskProductSalesTable_source = "kiosk_{$kiosk_id}_product_sales";
            $KioskProductSalesTable = TableRegistry::get($KioskProductSalesTable_source,[
                                                                        'table' => $KioskProductSalesTable_source,
                                                                            ]);
			//$this->KioskProductSale->setSource("kiosk_{$kiosk_id}_product_sales");
		}else{
			$this->KioskProductSales->recursive = 0;
            $KioskProductSalesTable_source = "kiosk_product_sales";
            $KioskProductSalesTable = TableRegistry::get($KioskProductSalesTable_source,[
                                                                        'table' => $KioskProductSalesTable_source,
                                                                            ]);
			//$this->KioskProductSale->setSource("kiosk_product_sales");
		}
        
        $query_refund_sumdata = $KioskProductSalesTable->find('all',[
																	 'conditions' => ['refund_status IN' => [1, 2],
																	 "created >=" => date('Y-m-d'),
																	 "created <" => date('Y-m-d',strtotime(' +1 day'))
																	 ]
																	]
															  );
        $query_refund_sumdata
                  ->select(['totalrefund' => $query_refund_sumdata->func()->sum('refund_price*quantity')]);
        //pr($query_refund_sumdata);die;
		$query_refund_result = $query_refund_sumdata->hydrate(false);
        if(!empty($query_refund_result)){
            $refundSumData = $query_refund_result->first();
        }else{
            $refundSumData = array();
        }
		//pr($refundSumData);die;
		if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS &&
			$this->request->session()->read('Auth.User.user_type')=='wholesale'){
			$refundData = 0;
		}else{
			$refundData = $refundSumData["totalrefund"];	
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
		
		
		$query_refund_data = $KioskProductSalesTable->find('all',[
																	 'conditions' => ['refund_status IN' => [1, 2],
																	 "created >=" => date('Y-m-d'),
																	 "created <" => date('Y-m-d',strtotime(' +1 day'))
																	 ]
																	]
															  );
		$query_refund_data = $query_refund_data->hydrate(false);
		if(!empty($query_refund_data)){
			$refund_data_entry = $query_refund_data->toArray();
		}else{
			$refund_data_entry = array();
		}
		$new_refund_data = array();
		
		if(!empty($refund_data_entry)){
			foreach($refund_data_entry as $refund_key => $refund_value){
				if(array_key_exists($refund_value['product_receipt_id'],$new_refund_data)){
					if(array_key_exists($refund_value['product_id'],$new_refund_data[$refund_value['product_receipt_id']])){
						$new_refund_data[$refund_value['product_receipt_id']][$refund_value['product_id']]['refund_price'] += $refund_value['refund_price'];
					$new_refund_data[$refund_value['product_receipt_id']][$refund_value['product_id']]['quantity'] += $refund_value['quantity'];
					$new_refund_data[$refund_value['product_receipt_id']][$refund_value['product_id']]['refund_by'] .=   " , ".$users[$refund_value['refund_by']];	
					}else{
						$new_refund_data[$refund_value['product_receipt_id']][$refund_value['product_id']]['refund_price'] =   $refund_value['refund_price'];
					$new_refund_data[$refund_value['product_receipt_id']][$refund_value['product_id']]['quantity'] =   $refund_value['quantity'];
					$new_refund_data[$refund_value['product_receipt_id']][$refund_value['product_id']]['refund_by'] =   $users[$refund_value['refund_by']];	
					}
				}else{
					$new_refund_data[$refund_value['product_receipt_id']][$refund_value['product_id']] =   array();
					$new_refund_data[$refund_value['product_receipt_id']][$refund_value['product_id']]['refund_price'] =   $refund_value['refund_price'];
					$new_refund_data[$refund_value['product_receipt_id']][$refund_value['product_id']]['quantity'] =   $refund_value['quantity'];
					$new_refund_data[$refund_value['product_receipt_id']][$refund_value['product_id']]['refund_by'] =   $users[$refund_value['refund_by']];	
				}
			}	
		}
		
		
		
		//pr($new_refund_data);die;
		
		
		$products_query = $this->Products->find('list',array(
															'keyField' => 'id',
															'valueField' => 'product',
															//'fields' => array('id','product','product_code')
															));
        $products_query = $products_query->hydrate(false);
        if(!empty($products_query)){
            $products = $products_query->toArray();
        }else{
            $products = array();
        }
		$code_query = $this->Products->find('list',array(
															'keyField' => 'id',
															'valueField' => 'product_code',
															//'fields' => array('id','product','product_code')
															));
        $code_query = $code_query->hydrate(false);
        if(!empty($code_query)){
            $code = $code_query->toArray();
        }else{
            $code = array();
        }
		$this->set(compact('code'));
		
       // pr($kiosk_id);die;
	   //echo $kioskProductSalesSource;	
		if($kioskProductSalesSource){
			
			$kiosk_id_1 = $this->request->Session()->read('kiosk_id');
			if($kiosk_id_1 > 0 && $kiosk_id_1 != 10000){
				$this->paginate = [
                                'limit' => 100,
                               // 'model' => 'kioskProductSale',
                                //'contain' => "kiosk_{$kiosk_id}_products",
                                'order' => ['id DESC'],
                                'conditions'=>['NOT'=>['sale_price'=>0],
                                                    "$KioskProductSalesTable_source.created >=" => date('Y-m-d'),
                                                    "$KioskProductSalesTable_source.created <" => date('Y-m-d',strtotime(' +1 day')),
                                                    "$KioskProductSalesTable_source.kiosk_id" => $kiosk_id_1,
                                                    ]
                            ];	
			}else{
				$this->paginate = [
                                'limit' => 100,
                               // 'model' => 'kioskProductSale',
                                //'contain' => "kiosk_{$kiosk_id}_products",
                                'order' => ['id DESC'],
                                'conditions'=>['NOT'=>['sale_price'=>0],
                                                    "$KioskProductSalesTable_source.created >=" => date('Y-m-d'),
                                                    "$KioskProductSalesTable_source.created <" => date('Y-m-d',strtotime(' +1 day')),
                                                    "$KioskProductSalesTable_source.kiosk_id" => 0,
                                                    ]
                            ];	
			}
			
		}else{
			$this->paginate = [
                                'limit' => 100,
                               // 'model' => 'kioskProductSale',
                                //'contain' => "kiosk_{$kiosk_id}_products",
                                'order' => ['id DESC'],
                                'conditions'=>['NOT'=>['sale_price'=>0],
                                                    "$KioskProductSalesTable_source.created >=" => date('Y-m-d'),
                                                    "$KioskProductSalesTable_source.created <" => date('Y-m-d',strtotime(' +1 day')),			
                                              ]
                            ];
		}
		
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
		$hint = $this->ScreenHint->hint('kiosk_product_sales','index');
					if(!$hint){
						$hint = "";
					}
		$categories_query = $this->Categories->find('all',array(
								'fields' => array('id', 'category','id_name_path'),
                                                                'conditions' => array('Categories.status' => 1),
								'order' => 'Categories.category asc'
								));
        $categories_query = $categories_query->hydrate(false);
        if(!empty($categories_query)){
            $categories = $categories_query->toArray();
        }else{
            $categories = array();
        }
		//pr($this->paginate);
		$categories = $this->CustomOptions->category_options($categories,true);			
		//$this->set('hint',$hint);
		if(($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS &&
			$this->request->session()->read('Auth.User.user_type')=='wholesale') ||
			$this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER || $this->request->session()->read('Auth.User.group_id') == SALESMAN
			){
			$this->paginate['conditions'][] = array('sale_type' => 1);
		}else{
			$this->paginate['conditions'][] = array('sale_type' => 0);
		}
		//pr($this->paginate);die;
		$kioskProductSales_query = $this->paginate($KioskProductSalesTable);
        if(!empty($kioskProductSales_query)){
            $kioskProductSales = $kioskProductSales_query->toArray();
        }else{
            $kioskProductSales = array();
        }
        //pr($kioskProductSales);die;
        $y_product_recepit_ids = array();
        foreach($kioskProductSales as $product_recepitArr){
            $y_product_recepit_ids[] = $product_recepitArr['product_receipt_id'];
        }
        if(empty($y_product_recepit_ids)){
            $y_product_recepit_ids = array(0 => null);
        }
		//pr($receiptTable);die;
        $recepit_table_data_query = $receiptTable->find('all',[
                                                                'conditions' => ['id IN' => $y_product_recepit_ids]
                                                            ]
                                                        );
        $recepit_table_data_query = $recepit_table_data_query->hydrate(false);
        if(!empty($recepit_table_data_query)){
            $recepit_table_data = $recepit_table_data_query->toArray();
        }else{
            $recepit_table_data = array();
        }
        $recepitTableData = array();
        foreach($recepit_table_data as $recepit_keys => $recepit_values){
            $recepitTableData[$recepit_values['id']] = $recepit_values;
        }
        $this->set(compact('recepitTableData'));
                
		$start_date = $end_date = date('d M Y');
		$this->set(compact('kiosk_id','orignal_amount','refundData','kiosks','categories','start_date','end_date','new_refund_data'));
		$this->set(compact('hint','users','products','actualVat','kioskProductSales','sumTotal','netAmount','totalVat'));
	}
    
    public function searchsale($keyword = "", $render = null){//to search during view sale as per admin navigation
		
		$kiosk_id = $this->request->Session()->read('kiosk_id');
        //pr($kiosk_id);die;
        if(empty($kiosk_id)){
			if(!empty($render)){
				$salesTable_source = "t_kiosk_product_sales";
				$reciptTable_source = "t_product_receipts";
			}else{
				
				if(array_key_exists('kiosk_id',$this->request->query)){
					$kiosk_id = $this->request->query['kiosk_id'];
					if($kiosk_id == 10000){
						$kiosk_id = 0;
					}
				}
				if(!empty($kiosk_id)){
						$salesTable_source = "kiosk_{$kiosk_id}_product_sales";
						$reciptTable_source = "kiosk_{$kiosk_id}_product_receipts";	
				}else{
					$salesTable_source = "kiosk_product_sales";
					$reciptTable_source = "product_receipts";  	
				}
				
			}
                      
        }else{
			if(!empty($render)){
				$salesTable_source = "t_kiosk_product_sales";
				$reciptTable_source = "t_product_receipts";
			}else{
				$salesTable_source = "kiosk_{$kiosk_id}_product_sales";
				$reciptTable_source = "kiosk_{$kiosk_id}_product_receipts";	
			}
            
        }
		$salesTable = TableRegistry::get($salesTable_source,[
                                                                'table' => $salesTable_source,
                                                            ]);
		$reciptTable = TableRegistry::get($reciptTable_source,[
                                                                'table' => $reciptTable_source,
                                                            ]);
		
		$categories_query = $this->Categories->find('all',array(
														'fields' => array('id', 'category','id_name_path'),
														'conditions' => array('Categories.status' => 1),
														'order' => 'Categories.category asc'
														)
											);
        $categories_query = $categories_query->hydrate(false);
        if(!empty($categories_query)){
            $categories = $categories_query->toArray();
        }else{
            $categories = array();
        }
		//$categories = $this->CustomOptions->category_options($categories,true);
		$sumTotalArr = array();
		$actualVat = $this->VAT;
		//$products = $this->Product->find('list',array('fields' => array('id','product')));
		$products_query = $this->Products->find('list',array(
														'keyField' => 'id',
														'valueField' => 'product',
													 //'fields' => array('id','product','product_code'),
													 ));
        $products_query = $products_query->hydrate(false);
        if(!empty($products_query)){
            $products = $products_query->toArray();
        }else{
            $products = array();
        }
		
		$code_query = $this->Products->find('list',array(
														'keyField' => 'id',
														'valueField' => 'product_code',
													 //'fields' => array('id','product','product_code'),
													 ));
        $code_query = $code_query->hydrate(false);
        if(!empty($code_query)){
            $code = $code_query->toArray();
        }else{
            $code = array();
        }
		$this->set(compact('code'));
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
		$searchKW = '';
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		if(empty($kiosk_id)){
			if(array_key_exists('kiosk_id',$this->request->query)){
				$kiosk_id = $this->request->query['kiosk_id'];
				if($kiosk_id == 10000){
					$kiosk_id = 0;
				}
			}
		}
		$this->set(compact('kiosk_id'));
		if(array_key_exists('search_kw', $this->request->query)){
			$searchKW = $this->request->query['search_kw'];
		}
		$receipt_id = '';
		if(array_key_exists('receipt_id', $this->request->query)){
			$receipt_id = $this->request->query['receipt_id'];
		}
		
		$start_date = '';
		if(array_key_exists('start_date',$this->request->query)){
			$start_date = $this->request->query['start_date'];
			$this->set(compact('start_date'));
		}
		
		$end_date = '';
		if(array_key_exists('end_date',$this->request->query)){
			$end_date = $this->request->query['end_date'];
			$this->set(compact('end_date'));
		}
		$category_ids = "";
		$ids = array();
		if(array_key_exists('category',$this->request->query)&& !empty($this->request->query['category'][0])){
			$category_id = $this->request->query['category'];
			$category_ids = implode("_",$category_id);
			if(empty($category_id)){
                $category_id = array(0 => null);
            }
            $ids_query = $this->Products->find('list',[
                                                'conditions' => ['category_id IN' => $category_id],
                                                'keyField' => 'id',
                                                'valueField' => 'id'
                                                ]);
            $ids_query = $ids_query->hydrate(false);
            if(!empty($ids_query)){
                $ids = $ids_query->toArray();
            }else{
                $ids = array();
            }
		}
		if(!empty($category_id)){
			$categories = $this->CustomOptions->category_options($categories,true,$category_id);	
		}else{
			$categories = $this->CustomOptions->category_options($categories,true);	
		}
		
		$conditionArr = array();
		
		if(array_key_exists('start_date',$this->request->query) &&
		   array_key_exists('end_date',$this->request->query) &&
		   !empty($this->request->query['start_date']) &&
		   !empty($this->request->query['end_date'])){
			$conditionArr[] = array(
						"created >=" => date('Y-m-d', strtotime($this->request->query['start_date'])),
						"created <" => date('Y-m-d', strtotime($this->request->query['end_date']. ' +1 Days')),			
					       );
		}
		
		if(array_key_exists('search_kw',$this->request->query) && !empty($this->request->query['search_kw'])){
			//$conditionArr['OR']['LOWER(Product.product) like '] =  strtolower("%$searchKW%");
			//$conditionArr['OR']['LOWER(Product.product_code) like '] =  strtolower("%$searchKW%");
			$searchW = strtolower($searchKW);
            $conn = ConnectionManager::get('default');
            $stmt = $conn->execute("SELECT `id` from `products` WHERE LOWER(`product_code`) like ('%{$searchW}%') or LOWER(`product`) like ('%{$searchW}%')");
            $productResult = $stmt ->fetchAll('assoc');
			$productIDs = array();
			
			foreach($productResult as $sngproductResult){
				$productIDs[] = $sngproductResult['id'];
			}
			//$conditionArr['OR']['KioskProductSale.product_id'] =  $productIDs;
			$this->set('search_kw',$this->request->query['search_kw']);
		}
		
		$p_ids = array();
		if(!empty($ids)&&!empty($productIDs)){
			$p_ids = array_merge($productIDs,$ids);
		}elseif(!empty($productIDs)){
			$p_ids = $productIDs;
		}elseif(!empty($ids)){
			$p_ids = $ids;
		}
		if(!empty($p_ids)){
			$conditionArr['OR']['product_id IN'] =  $p_ids;
		}
		
		
		if(array_key_exists('receipt_id',$this->request->query) && !empty($this->request->query['receipt_id'])){
			$conditionArr['product_receipt_id IN'] =  $receipt_id;
			$this->set('receipt_id',$this->request->query['receipt_id']);
		}
		$conditionArr['refund_status'] = '<> 1';
		 //if(empty($kiosk_id)){
			if(!empty($render)){
				$conditionArr['kiosk_id'] = $kiosk_id;
			}
		//}
		//pr($conditionArr);die;
		$result_query = $salesTable->find('all',array(
															'conditions'=>array($conditionArr), //, 'kiosk_id'=>$kiosk_id
															'fields' => array('id','product_receipt_id')));
        //pr($result_query);die;
		$result_query = $result_query->hydrate(false);
        if(!empty($result_query)){
            $result = $result_query->toArray();
        }else{
            $result = array();
        }
        //pr($result);die;
		$receiptArray = array();
		$receipt_Ids = array();
		
		if(count($result)){
			foreach($result as $rk => $results){
				$receiptArray[$results['product_receipt_id']] = $results['id'];
			}
			
			$receipt_Ids = array_keys($receiptArray);
			
			if(!empty($render)){
				$sumTotalArr_query = $reciptTable->find('all',array(
																		 'conditions'=> array('id IN' => $receipt_Ids,
																							  ),
																		 //'fields'=>array('SUM(bill_amount) as totalSum'),
																		 ));	
			}else{
				if(($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS &&
				$this->request->session()->read('Auth.User.user_type')=='wholesale') || $this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER //|| $this->request->session()->read('Auth.User.group_id') == SALESMAN
				   ){
					$sumTotalArr_query = $reciptTable->find('all',array(
																		 'conditions'=> array('id IN' => $receipt_Ids,
																							  'sale_type' => 1
																							  ),
																		 //'fields'=>array('SUM(bill_amount) as totalSum'),
																		 ));	
				}else{
					$sumTotalArr_query = $reciptTable->find('all',array(
																		 'conditions'=> array('id IN' => $receipt_Ids,
																							  'sale_type' => 0
																							  ),
																		 //'fields'=>array('SUM(bill_amount) as totalSum'),
																		 ));
				}	
			}
			
			
			
			$sumTotalArr_query
								->select(['totalSum' => $sumTotalArr_query->func()->sum('bill_amount')]);
			$sumTotalArr_query = $sumTotalArr_query->hydrate(false);
			if(!empty($sumTotalArr_query)){
				$sumTotalArr = $sumTotalArr_query->first();
			}else{
				$sumTotalArr = array();
			}
			
		}
		//for sum total purpose, getting the receipt ids as per the above condition array
		$sumTotal = 0;
		if(count($sumTotalArr)){
			if($sumTotalArr['totalSum']>0){
				$sumTotal = $sumTotalArr['totalSum'];
			}
		}
		
		$netAmount = $sumTotal/(1+$actualVat/100);
		$totalVat = $sumTotal - $netAmount;
		
		//sum total ends here
		//pr($receipt_Ids);die;
        if(empty($receipt_Ids)){
            //$receipt_Ids = array(0 => null);
        }
		
		if(!isset($conditionArr['product_receipt_id IN']) || empty($conditionArr['product_receipt_id IN'])){
			if(!empty($receipt_Ids)){
				$conditionArr['product_receipt_id IN'] = $receipt_Ids;
			}
		}
		$conditionArr['refund_status'] = '<> 1';
		if(empty($render)){
			if(($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS &&
			$this->request->session()->read('Auth.User.user_type')=='wholesale')||
		   $this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER
		   //|| $this->request->session()->read('Auth.User.group_id') == SALESMAN
		   ){
				$conditionArr['sale_type'] = 1;
			}else{
				$conditionArr['sale_type'] = 0;
			}	
		}
		//pr($conditionArr);die;
		$this->paginate = [
                            'conditions' => $conditionArr,
                            'limit' => ROWS_PER_PAGE,
                            'order' => ['id DESC'],
                          ];
		
		$kioskProductSales_query = $this->paginate($salesTable);
        if(!empty($kioskProductSales_query)){
            $kioskProductSales = $kioskProductSales_query->toArray();
        }else{
            $kioskProductSales = array();
        }
        $y_product_recepit_ids = array();
        foreach($kioskProductSales as $product_recepitArr){
            $y_product_recepit_ids[] = $product_recepitArr['product_receipt_id'];
        }
        if(empty($y_product_recepit_ids)){
            $y_product_recepit_ids = array(0 => null);
        }
        $recepit_table_data_query = $reciptTable->find('all',[
                                                                'conditions' => ['id IN' => $y_product_recepit_ids]
                                                            ]
                                                        );
        $recepit_table_data_query = $recepit_table_data_query->hydrate(false);
        if(!empty($recepit_table_data_query)){
            $recepit_table_data = $recepit_table_data_query->toArray();
        }else{
            $recepit_table_data = array();
        }
		//pr($recepit_table_data);die;
        $recepitTableData = array();
        foreach($recepit_table_data as $recepit_keys => $recepit_values){
            $recepitTableData[$recepit_values['id']] = $recepit_values;
        }
        $this->set(compact('recepitTableData'));
        
		//pr($kioskProductSales);die;
		$hint = $this->ScreenHint->hint('kiosk_product_sales','index');
					if(!$hint){
						$hint = "";
					}
	    $orignal_amount = array();
		//pr($receipt_Ids);
		if(empty($receipt_Ids)){
			$receipt_Ids = array(0 => null);
		}
		
		if(!empty($render)){
			$productReceiptDetail_query = $reciptTable->find('all',array('conditions' =>array('id IN' => $receipt_Ids,
																							  //'sale_type' => 1,
																							  ),'fields'=>array('id','orig_bill_amount')));			
		}else{
			if(($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS &&
				$this->request->session()->read('Auth.User.user_type')=='wholesale') ||
			   $this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER
			   //|| $this->request->session()->read('Auth.User.group_id') == SALESMAN
			   ){
				$productReceiptDetail_query = $reciptTable->find('all',array('conditions' =>array('id IN' => $receipt_Ids,
																								  'sale_type' => 1,
																								  ),'fields'=>array('id','orig_bill_amount')));		
			}else{
				$productReceiptDetail_query = $reciptTable->find('all',array('conditions' =>array('id IN' => $receipt_Ids,
																								  'sale_type' => 0,
																								  ),'fields'=>array('id','orig_bill_amount')));
			}			
		}
		

		
		
		
		
		
        //pr($productReceiptDetail_query);die;
        $productReceiptDetail_query = $productReceiptDetail_query->hydrate(false);
        if(!empty($productReceiptDetail_query)){
            $productReceiptDetail = $productReceiptDetail_query->toArray();
        }else{
            $productReceiptDetail = array();
        }
		//pr($productReceiptDetail);die;
		foreach($productReceiptDetail  as $key => $value){
			if(empty($value["orig_bill_amount"])){
				$value["orig_bill_amount"] = 0;
			}
			$orignal_amount[$value["id"]] = $value["orig_bill_amount"];
		}
		if(!empty($conditionArr)){
			if(array_key_exists("refund_status",$conditionArr)){
				unset($conditionArr["refund_status"]);
			}
			if(array_key_exists("product_receipt_id",$conditionArr)){
				unset($conditionArr["product_receipt_id"]);
			}
		}
        $conditionArr['refund_status IN'] = array(1, 2);
        $query = $salesTable->find('all',['conditions' => $conditionArr]);
        $query
                  ->select(['totalrefund' => $query->func()->sum('refund_price*quantity')]);
        $query_result = $query->hydrate(false);
        
        if(!empty($query_result)){
            $refundSumData = $query_result->first();
        }else{
            $refundSumData = array();
        }
    
		$refundData = $refundSumData["totalrefund"];
		
		$refund_query = $salesTable->find('all',['conditions' => $conditionArr]);
		$refund_query = $refund_query->hydrate(false);
		if(!empty($refund_query)){
			$refund_data_entry = $refund_query->toArray();
		}else{
			$refund_data_entry = array();
		}
		unset($conditionArr['refund_status IN']);
		//pr($refund_data_entry);die;
		$new_refund_data = array();
		
		if(!empty($refund_data_entry)){
			foreach($refund_data_entry as $refund_key => $refund_value){
				if(array_key_exists($refund_value['product_receipt_id'],$new_refund_data)){
					if(array_key_exists($refund_value['product_id'],$new_refund_data[$refund_value['product_receipt_id']])){
						$new_refund_data[$refund_value['product_receipt_id']][$refund_value['product_id']]['refund_price'] += $refund_value['refund_price'];
					$new_refund_data[$refund_value['product_receipt_id']][$refund_value['product_id']]['quantity'] += $refund_value['quantity'];
					$new_refund_data[$refund_value['product_receipt_id']][$refund_value['product_id']]['refund_by'] .=   " , ".$users[$refund_value['refund_by']];	
					}else{
						$new_refund_data[$refund_value['product_receipt_id']][$refund_value['product_id']]['refund_price'] =   $refund_value['refund_price'];
					$new_refund_data[$refund_value['product_receipt_id']][$refund_value['product_id']]['quantity'] =   $refund_value['quantity'];
					$new_refund_data[$refund_value['product_receipt_id']][$refund_value['product_id']]['refund_by'] =   $users[$refund_value['refund_by']];	
					}
				}else{
					$new_refund_data[$refund_value['product_receipt_id']][$refund_value['product_id']] =   array();
					$new_refund_data[$refund_value['product_receipt_id']][$refund_value['product_id']]['refund_price'] =   $refund_value['refund_price'];
					$new_refund_data[$refund_value['product_receipt_id']][$refund_value['product_id']]['quantity'] =   $refund_value['quantity'];
					$new_refund_data[$refund_value['product_receipt_id']][$refund_value['product_id']]['refund_by'] =   $users[$refund_value['refund_by']];	
				}
			}	
		}
		
		
		
		
		//pr($orignal_amount);die;
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

		$this->set(compact('kiosk_id','orignal_amount','refundData','kiosks','categories','new_refund_data'));
		$this->set(compact('hint','kioskProductSales','products','users','actualVat','sumTotal','netAmount','totalVat'));
		//$this->layout = 'default';
		if($render){
			$this->render($render);
		}else{
			$this->render('index');
		}
	}
	
    
    public function searchSaleLog(){
		
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
							'conditions' => ['kiosk_id IN' => $managerKiosk],
							'limit' => ROWS_PER_PAGE,
							'order' => ['created DESC'],
							//'recursive' => 1
                          ];	
			   }else{
				$this->paginate = [
							'limit' => ROWS_PER_PAGE,
							'order' => ['created DESC'],
							//'recursive' => 1
                          ];	
			   }
		}else{
			$this->paginate = [
							'limit' => ROWS_PER_PAGE,
							'order' => ['created DESC'],
							//'recursive' => 1
                          ];	
		}
        //pr($this->request);die;
		
		$saleLog_query = $this->paginate('SaleLogs');
        if(!empty($saleLog_query)){
            $saleLog = $saleLog_query->toArray();
        }else{
            $saleLog = array();
        }
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
		$settingArr = $this->setting;
		$currency = $settingArr['currency_symbol'];
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
		
		$allowed_ids = $this->allowed_user();
		if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
			$allowed_users_query = $this->Users->find('list',[
                                                    'conditions' => [
																		'Users.group_id IN' => [ADMINISTRATORS,MANAGERS],
																		//'id IN' => $allowed_ids,
                                                                    ],
                                                    'keyField' => 'id',
                                                    'valueField' => 'username'
                                                   ]);	
		}else{
			$allowed_users_query = $this->Users->find('list',[
                                                    'conditions' => [
																		'Users.group_id IN' => [ADMINISTRATORS,MANAGERS],
																		'id IN' => $allowed_ids,
                                                                    ],
                                                    'keyField' => 'id',
                                                    'valueField' => 'username'
                                                   ]);
		}
		
		
        $allowed_users_query = $allowed_users_query->hydrate(false);
        if(!empty($allowed_users_query)){
            $allowed_users = $allowed_users_query->toArray();
        }else{
            $allowed_users = array();
        }
		
        $query_sale = $this->SaleLogs->find('all');
                  $query_sale
                          ->select(['totalsum' => $query_sale->func()->sum('(SaleLogs.modified_amount*SaleLogs.quantity)-(SaleLogs.orignal_amount*SaleLogs.quantity)')])
                          ->select(['totalMdfyAmt' => $query_sale->func()->sum('SaleLogs.modified_amount*SaleLogs.quantity')])
                          ->select(['totalOrgAmt' => $query_sale->func()->sum('SaleLogs.orignal_amount*SaleLogs.quantity')]);
        $result_sale = $query_sale->hydrate(false);
        if(!empty($result_sale)){
            $sumData = $result_sale->first();
        }else{
            $sumData = array();
        }
		$totalsum = $sumData['totalsum'];
		$totalModfyAmt = $sumData['totalMdfyAmt'];
		$totalOrgAmt = $sumData['totalOrgAmt'];
		$this->set(compact('saleLog','kiosks','users','currency','totalsum','totalModfyAmt','totalOrgAmt','allowed_users'));
		if($this->request->is('post','put')){
			//pr($this->request);die;
			$conditionArr = array();
			$searchKW = '';
			if(array_key_exists('search_kw', $this->request->data)){
				$search_kw = $this->request->data['search_kw'];
				$this->set(compact('search_kw'));
			}
			if(!empty($search_kw)){
				$conditionArr['OR']['LOWER(SaleLogs.product_code) like '] =  strtolower("%$search_kw%");
				$conditionArr['OR']['LOWER(SaleLogs.product_title) like '] =  strtolower("%$search_kw%");
			}
			
			$start_date = '';
			if(array_key_exists('start_date',$this->request->data)){
				$start_date = $this->request->data['start_date'];
				$this->set(compact('start_date'));
			}
			
			$end_date = '';
			if(array_key_exists('end_date',$this->request->data)){
				$end_date = $this->request->data['end_date'];
				$this->set(compact('end_date'));
			}
			
			if(array_key_exists('start_date',$this->request->data) &&
			   array_key_exists('end_date',$this->request->data) &&
			   !empty($this->request->data['start_date']) &&
			   !empty($this->request->data['end_date'])){
				$conditionArr[] = array(
							"SaleLogs.sale_date >=" => date('Y-m-d', strtotime($this->request->data['start_date'])),
							"SaleLogs.sale_date <" => date('Y-m-d', strtotime($this->request->data['end_date']. ' +1 Days')),			
							   );
			}
			if(array_key_exists('ProductSale',$this->request->data)){
				if(array_key_exists('kiosk_id',$this->request->data['ProductSale'])){
					$kioskId = $this->request->data['ProductSale']['kiosk_id'];
					$this->set(compact('kioskId'));
				}
			}
			if(!empty($kioskId)){
				if($kioskId != -1){
					$conditionArr[] = array(
											'SaleLogs.kiosk_id' => $kioskId,
										);
				}
			}
			if(array_key_exists('ProductSale',$this->request->data)){
				if(array_key_exists('user_id',$this->request->data['ProductSale'])){
					$allowed_users_id = $this->request->data['ProductSale']['user_id'];
					$this->set(compact('allowed_users_id'));
				}
			}
			if(!empty($allowed_users_id)){
				if($allowed_users_id != -1){
					$conditionArr[] = array(
											'SaleLogs.user_id' => $allowed_users_id,
										);
				}
			}
            
            $query_sale_log = $this->SaleLogs->find('all',['conditions' => $conditionArr]);
                $query_sale_log
                          ->select(['totalsum' => $query_sale_log->func()->sum('(SaleLogs.modified_amount*SaleLogs.quantity)-(SaleLogs.orignal_amount*SaleLogs.quantity)')])
                          ->select(['totalMdfyAmt' => $query_sale_log->func()->sum('SaleLogs.modified_amount*SaleLogs.quantity')])
                          ->select(['totalOrgAmt' => $query_sale_log->func()->sum('SaleLogs.orignal_amount*SaleLogs.quantity')]);
            $result_sale_log = $query_sale_log->hydrate(false);
            if(!empty($result_sale_log)){
                $sumData = $result_sale_log->first();
            }else{
                $sumData = array();
            }
			$totalsum = $sumData['totalsum'];
			$totalModfyAmt = $sumData['totalMdfyAmt'];
			$totalOrgAmt = $sumData['totalOrgAmt'];
			
			
			
			$this->paginate = [
                                'limit' => ROWS_PER_PAGE,
                                'conditions' => $conditionArr,
                                'order' => ['created DESC'],
                                //'recursive' => 1
							];
			$saleLog_query = $this->paginate('SaleLogs');
            if(!empty($saleLog_query)){
                $saleLog = $saleLog_query->toArray();
            }else{
                $saleLog = array();
            }
			$this->set(compact('saleLog','kiosks','users','currency','totalsum','totalModfyAmt','totalOrgAmt'));
		}
	}
	
	public function newSale($customerId = ''){
		//pr($_SESSION);
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		if(!empty($kiosk_id)){
			$productSource = "kiosk_{$kiosk_id}_products";
		}else{
			$productSource = "products";
		}
		$productTable = TableRegistry::get($productSource,[
																	'table' => $productSource,
																]);
		
        $CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
		$currencySymbol = $this->setting['currency_symbol'];
		$vat = $this->setting['vat'];
		$customerAccountDetails_query = $this->Customers->find('all',array(
									'conditions'=>array('Customers.id'=>$customerId)
									)
								);
		$customerAccountDetails_query = $customerAccountDetails_query->hydrate(false);
		$customerAccountDetails = $customerAccountDetails_query->first();
		//pr($customerAccountDetails);die;
		$country = $customerAccountDetails['country'];
		$this->paginate = [
						'limit' => 20,
						'order' => ['product' => 'ASC'],
						'conditions' => ['NOT'=>['quantity' => 0]]
					];
		//-----------------------------------------
		$categories_query = $this->Categories->find('all',array(
								'fields' => array('id', 'category','id_name_path'),
                                                                'conditions' => array('Categories.status' => 1),
								'order' => 'Categories.category asc',
								'recursive' => -1
								));
		$categories_query = $categories_query->hydrate(false);
		$categories = $categories_query->toArray();
		
		$categories = $this->CustomOptions->category_options($categories,true);
		
		//-----------------------------------------
		$session_basket = $this->request->Session()->read('new_sale_basket');
		$quantityError = '';
		if(array_key_exists('quantityError',$this->request->Session()->read())){
			$quantityError = $this->request->Session()->read('quantityError');
		}
		
		$bulkDiscountPercentage = 0;
		$bulkDiscountPercentage = $this->request->Session()->read('new_sale_bulk_discount');
		if(is_array($session_basket)){
			$specialInvoice = $this->request->Session()->read('special_invoice');
			if(empty($specialInvoice)){
					$specialInvoice = 0;
			}
			$productCodeArr = array();
			foreach($session_basket as $key => $basketItem){
				if($key == 'error')continue;
				$productCodeArr_query	= $productTable->find('all',
														  array('conditions'=>array('id'=>$key),
																'fields'=>array('id','product_code'))
														  );
				$productCodeArr_query = $productCodeArr_query->hydrate(false);
				$productCodeArr[] = $productCodeArr_query->first();
			}
			$productCode = array();
			if(!empty($productCodeArr)){
				foreach($productCodeArr as $k=>$productCodeData){
					$productCode[$productCodeData['id']]=$productCodeData['product_code'];
				}
			}
			$basketStr = "";
			$counter = 0;
			$totalBillingAmount = 0;
			$totalDiscountAmount = 0;
			$sub_total = $vatAmount = 0;
			foreach($session_basket as $key => $basketItem){
				if($basketItem['quantity'] == 0){
					unset($_SESSION['Basket'][$key]);
					$this->Flash->error("quantity cannot be zero");
					return;
				}
			}
			//pr($session_basket);die;
			foreach($session_basket as $key => $basketItem){
				if($key == 'error')continue;
				$counter++;
				$vat = $this->VAT;
				$vatItem = $vat/100;
				$discount = $basketItem['discount'];				
				$sellingPrice = $basketItem['selling_price'];
				$net_amount = $basketItem['net_amount'];
				$price_without_vat = $basketItem['price_without_vat'];
				$itemPrice = $basketItem['selling_price']/(1+$vatItem);
				$discountAmount = $price_without_vat * $basketItem['discount'] / 100 * $basketItem['quantity'];
				$totalDiscountAmount+= $discountAmount;
				$totalItemPrice = $price_without_vat * $basketItem['quantity'];
				//$pricebeforeVat = $itemPrice*$basketItem['quantity']-$discountAmount;
				$totalItemCost = $totalItemPrice-$discountAmount;
				$totalBillingAmount+=$totalItemCost;
				$vatperitem = $basketItem['quantity']*($sellingPrice-$itemPrice);
				$bulkDiscountValue = (float)$totalBillingAmount*(float)$bulkDiscountPercentage/100;
				$netBillingAmount = $totalBillingAmount-$bulkDiscountValue;
				$netPrice = $netBillingAmount;
				$vatAmount = $netBillingAmount * $vatItem;
				//round($netBillingAmount-$netPrice,2);
				
				if($country=="OTH" || $specialInvoice == 1){
					$finalAmount = $netBillingAmount;
				}else{
					$finalAmount = $netBillingAmount+$vatAmount;
				}
				
				$sub_total = $sub_total + $totalItemCost;
				
				$basketStr.="<tr>
						<td>{$counter})</td>
						<td>{$productCode[$key]}</td>
						<td>".$basketItem['product']."</td>
						<td>".$basketItem['quantity']."</td>
						<td>".$CURRENCY_TYPE.number_format($price_without_vat,2)."</td>
						<td> ".$discount."</td>
						<td>".$CURRENCY_TYPE.number_format($net_amount,2)."</td>
						<td>".$CURRENCY_TYPE.number_format($totalItemCost,2)."</td></tr>";
			}
			if(!empty($basketStr)){
				$basketStr = "<table><tr>
							<th style='width: 10px;'>Sr No</th>
							<th style='width: 132px;'>Product Code</th>
							<th style='width: 445px;'>Product</th>
							<th style='width: 30px;'>Qty</th>
							<th style='width: 99px;'>Sale Price</th>
							<th style='width: 40px;'>Disct %</th>
							<th style='width: 10px;'>Disct Value</th>
							<th style='width: 10px;'>Amount</th>
							</tr>".$basketStr."
							<tr><td colspan='7'>Sub Total</td><td>".$CURRENCY_TYPE.number_format($sub_total,2)."</td></tr>
							<tr><td colspan='7'>Bulk Discount ({$bulkDiscountPercentage}%)</td><td>".$CURRENCY_TYPE.number_format($bulkDiscountValue,2)."</td></tr>
							<tr><td colspan='7'>Sub Total(After bulk discount)</td><td>".$CURRENCY_TYPE.number_format($netBillingAmount,2)."</td></tr>
							<tr><td colspan='7'>Vat</td><td>".$CURRENCY_TYPE.number_format($vatAmount,2)."</td></tr>
							<tr><td colspan='7'>Net Amount</td><td>".$CURRENCY_TYPE.number_format($netPrice,2)."</td></tr>
							<tr><td colspan='7'>Total Amount</td><td>".$CURRENCY_TYPE.number_format($finalAmount,2)."</td></tr></table>";
							
							
				$productCounts = count($this->request->Session()->read('new_sale_basket'));
			
				if($productCounts){
					//$productCounts product(s) added to the cart.
					$flashMessage = "$quantityError <br/> Total item Count:$productCounts.<br/>$basketStr";
				}else{
					$flashMessage = "$quantityError <br/> No item added to the cart. Item Count:$productCounts";
				}
				if(isset($finalAmount)){
					$this->request->Session()->write('finalAmount', $finalAmount);
				}
				if(array_key_exists('error',$session_basket)){
					$flashMessage = $session_basket['error']."<br/>".$flashMessage;
				}
				$this->Flash->success($flashMessage,array('escape' => false));
			}elseif(!empty($quantityError)){
				$this->Flash->error($quantityError,array('escape' => false));
			}
			
			if(array_key_exists('quantityError',$this->request->Session()->read())){
				$this->request->Session()->delete('quantityError');
			}
		}
		//-----------------------------------------
		
		$products = $this->paginate($productTable);
		$categoryIdArr = array();
		foreach($products as $key=>$product){
			$categoryIdArr[] = $product['category_id'];
		}
		$categoryName_query = $this->Categories->find('list',
														[
															'keyField' => 'id',
															'valueField'=> 'category',
															'conditions'=>['Categories.id IN'=>$categoryIdArr],
														]
											  );
		$categoryName_query = $categoryName_query->hydrate(false);
        if(!empty($categoryName_query)){
            $categoryName = $categoryName_query->toArray();
        }else{
            $categoryName = array();
        }
		$this->set(compact('categories','customerAccountDetails','customerId','categoryName','products','vat'));
	}
	
	public function productsSelling($customerId = ''){
		$vat = $this->VAT;
		//	pr($this->request);die;
		$currencySymbol = $this->setting['currency_symbol'];
		$customerAccountDetails_query = $this->Customers->find('all',array(
									'conditions'=>array('Customers.id'=>$customerId)
									)
								);
		$customerAccountDetails_query = $customerAccountDetails_query->hydrate(false);
        if(!empty($customerAccountDetails_query)){
            $customerAccountDetails = $customerAccountDetails_query->first();
        }else{
            $customerAccountDetails = array();
        }
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
		$kiosk_id = $this->request->Session()->read('kiosk_id');		
		if(!empty($kiosk_id)){
			//wholesale retailer
			$receiptTable = "kiosk_{$kiosk_id}_product_receipts";
			$salesTable = "kiosk_{$kiosk_id}_product_sales";
			$productTable_source = "kiosk_{$kiosk_id}_products";
		}else{
			//admin
			$receiptTable = "product_receipts";
			$salesTable = "kiosk_product_sales";
			$productTable_source = "products";
		}
		
		//----------Kiosk database tables--------------------
		//$this->Product->setSource($productTable);
		$productTable = TableRegistry::get($productTable_source,[
																	'table' => $productTable_source,
																]);
		
		$user_id = $this->request->session()->read('Auth.User.id');
		//$user_id = $this->Auth->user('id');	//rasa
		//$this->initialize_tables($kiosk_id);
		$current_page = '';
		if(array_key_exists('current_page',$this->request['data'])){
			$current_page = $this->request['data']['current_page'];		
		}
		//if(empty($current_page)){$this->redirect(array('action' => "new_sale",$customerId));}		
		$productCounts = 0;
		$session_basket = $this->request->Session()->read('new_sale_basket');
		
		//--------------------------Add to basket case--------------
		if(array_key_exists('basket',$this->request['data'])){
			//pr($this->request['data']);die("--");;
			//here the sh
			$specialInvoice = $this->request->Session()->read('special_invoice');
			if(empty($specialInvoice)){
					$specialInvoice = 0;
			}
			$sessionReceiptRequired = 0;
			if(array_key_exists('receipt_required',$this->request['data'])){
				$receipt_required = $this->request['data']['receipt_required'];
				$this->request->Session()->write('receipt_required',$receipt_required);
				$sessionReceiptRequired = $this->request->Session()->read('receipt_required');
			}
			
			$sessionBulkDiscount = 0;
			if(array_key_exists('bulk_discount',$this->request['data'])){
				
				if($this->request['data']['bulk_discount']>100){
					$flashMessage = "Bulk discount percentage must be less than 100";
					$this->Flash->error($flashMessage);
					$this->redirect(array('action' => "new_sale/$customerId/page:$current_page"));
					die;
				}elseif($this->request['data']['bulk_discount']<0){
					$flashMessage = "Bulk discount percentage must be a positive number";
					$this->Flash->error($flashMessage);
					$this->redirect(array('action' => "new_sale/$customerId/page:$current_page"));
					die;
				}
			}
			//pr($this->request);die;
			$productArr = array();
			//pr($this->request['data']['KioskProductSale']);die;
			$error_str = "Not Sufficent Quantity for Product ";
			$error = array();
			foreach($this->request['data']['KioskProductSale']['item'] as $key => $item){				
				if((int)$item){
					//echo "net amount: ".$netAmount."\n";
					$discount = $this->request['data']['KioskProductSale']['discount'][$key];					
					$price = $this->request['data']['KioskProductSale']['selling_price'][$key];
					$discountStatus = $this->request['data']['KioskProductSale']['discount_status'][$key];
					$currentQuantity = $this->request['data']['KioskProductSale']['p_quantity'][$key];
					$productID = $this->request['data']['KioskProductSale']['product_id'][$key];
					$remarks = $this->request['data']['KioskProductSale']['remarks'][$key];
					$productTitle = $this->request['data']['KioskProductSale']['product'][$key];
					$quantity = $this->request['data']['KioskProductSale']['quantity'][$key];
					$netAmount = $this->request['data']['KioskProductSale']['net_amount'][$key];
					$priceWithoutVat = $this->request['data']['KioskProductSale']['price_without_vat'][$key];
					if(empty($netAmount)){
						$netAmount = $priceWithoutVat;
					}
					if($netAmount >= $priceWithoutVat){
						$priceWithoutVat = $netAmount;
						$price = $netAmount + $netAmount*($vat/100);
					}
					if(empty($productID)){
						$productID = array(0 => null);
					}
					$priceCheck_query = $productTable->find('all',array(
												  'conditions'=>array('id IN'=>$productID),
												  'fields'=>array('selling_price','product'),
												  )
										);
					$priceCheck_query = $priceCheck_query->hydrate(false);
                    if(!empty($priceCheck_query)){
                        $priceCheck = $priceCheck_query->first();
                    }else{
                        $priceCheck = array();
                    }
					$originalPrice = $priceCheck['selling_price'];
					//$product_name = $priceCheck['Product']['product'];
					//$discountValue = $originalPrice * $discount/100; change on 17th may 2016
					$discountValue = $priceWithoutVat * $discount/100;
					//echo $discountValue."\n";
					//$minPrice = round($originalPrice-$discountValue,2);
					$minPrice = round($priceWithoutVat-$discountValue,2);
					//echo "min price: ".$minPrice."\n";
					if($netAmount != $priceWithoutVat && $netAmount < $minPrice){
						$flashMessage = "Selling price cannot be less than the minimum allowed price";
						$this->Flash->error($flashMessage);
						$this->redirect(array('action' => "new_sale/$customerId/page:$current_page"));
						die;
					}
					
					if($quantity <= $currentQuantity && !empty($quantity)){
						$productArr[$productID] = array(
										'quantity' => $quantity,
										'selling_price' => $price,
										'net_amount' => $netAmount,
										'price_without_vat' => $priceWithoutVat,
										'remarks' => $remarks,
										'product' => $productTitle,
										'discount' => $discount,
										'discount_status' => $discountStatus,
										'receipt_required' => $this->request['data']['receipt_required'],
										'bulk_discount' => $this->request['data']['bulk_discount']
										);
						$productCounts++;
					}else{
						$error[] = $productTitle;
					}
				}
			}
			if(!empty($error)){
				$error_str .= implode(",",$error);
				$this->Flash->success($error_str,array('escape' => false));
				return $this->redirect(array('action' => "new_sale/$customerId/page:$current_page"));
			}
			
			if(array_key_exists('bulk_discount',$this->request['data'])){
				$bulk_discount = $this->request['data']['bulk_discount'];
				$this->request->Session()->write('new_sale_bulk_discount',$bulk_discount);
				$sessionBulkDiscount = $this->request->Session()->read('new_sale_bulk_discount');
			}
			
			if(array_key_exists('special_invoice', $this->request->data)){
				if($this->request->data['special_invoice'] == 1){
					$this->request->Session()->write('special_invoice', 1);
				}else{
					$this->request->Session()->delete('special_invoice');
				}
			}else{
				$this->request->Session()->delete('special_invoice');
			}
			
			$session_basket = $this->request->Session()->read('new_sale_basket');
			
			if(count($session_basket) >= 1){
				//adding item to the the existing session
				$sum_total = $this->add_arrays(array($productArr,$session_basket));
				$this->request->Session()->write('new_sale_basket', $sum_total);
				$session_basket = $this->request->Session()->read('new_sale_basket');				
			}else{
				//adding item first time to session
				if(count($productCounts))$this->request->Session()->write('new_sale_basket', $productArr);
			}
			$basketStr = "";
			$counter = $totalBillingAmount = $totalDiscountAmount = $vatAmount = 0;
			
			$session_basket = $this->request->Session()->read('new_sale_basket');
			if(is_array($session_basket)){
				//storing the session in session_backups table
				$this->SessionRestore->update_session_backup_table($this->request->params['controller'], 'new_sale', 'new_sale_basket', $session_basket, $kiosk_id);
				$productCodeArr = array();
				foreach($session_basket as $key => $basketItem){
					if($key == 'error')continue;
					$productCodeArr_query = $productTable->find('all',
															  array('conditions'=>array('id'=>$key),'fields'=>array('id','product_code')
																	));
					$productCodeArr_query = $productCodeArr_query->hydrate(false);
                    if(!empty($productCodeArr_query)){
                        $productCodeArr[] = $productCodeArr_query->first();
                    }else{
                        $productCodeArr[] = array();
                    }
				}
				$productCode = array();
				if(!empty($productCodeArr)){
					foreach($productCodeArr as $k=>$productCodeData){
						$productCode[$productCodeData['id']]=$productCodeData['product_code'];
					}
				}
				
				foreach($session_basket as $key => $basketItem){
					//pr($basketItem);
					$counter++;
					$vat = $this->VAT;
					$vatItem = $vat/100;
					$discount = $basketItem['discount'];				
					$sellingPrice = $basketItem['selling_price'];
					$net_amount = $basketItem['net_amount'];
					$price_without_vat = $basketItem['price_without_vat'];
					$itemPrice = $basketItem['selling_price']/(1+$vatItem);
					$discountAmount = $price_without_vat*$basketItem['discount']/100* $basketItem['quantity'];
					$totalDiscountAmount+= $discountAmount;
					$totalItemPrice = $price_without_vat * $basketItem['quantity'];				
					$bulkDiscountPercentage = $sessionBulkDiscount;
					$totalItemCost = $totalItemPrice-$discountAmount;
					$totalBillingAmount+=$totalItemCost;
					$vatperitem = $basketItem['quantity']*($sellingPrice-$itemPrice);
					$bulkDiscountValue = (float)$totalBillingAmount*(float)$bulkDiscountPercentage/100;
					$netBillingAmount = $totalBillingAmount-$bulkDiscountValue;
					$netPrice = $netBillingAmount;
					$vatAmount = $netBillingAmount*$vatItem;
					
					if($country=="OTH" || $specialInvoice == 1){
						$finalAmount = $netBillingAmount;
					}else{
						$finalAmount = $netBillingAmount+$vatAmount;
					}
					
					$basketStr.="<tr>
						<td>{$counter})</td>
						<td>{$productCode[$key]}</td>
						<td>".$basketItem['product']."</td>
						<td>".$basketItem['quantity']."</td>
						<td>".$currencySymbol.number_format($price_without_vat,2)."</td>
						<td> ".$discount."</td>
						<td>".$currencySymbol.number_format($discountAmount,2)."</td>
						<td>".$currencySymbol.number_format($totalItemCost,2)."</td></tr>";
				}
			}
			if(!empty($basketStr)){
				$basketStr = "<table><tr>
							<th style='width: 10px;'>Sr No</th>
							<th style='width: 132px;'>Product Code</th>
							<th style='width: 445px;'>Product</th>
							<th style='width: 30px;'>Qty</th>
							<th style='width: 99px;'>Price/Item</th>
							<th style='width: 40px;'>Disct %</th>
							<th style='width: 10px;'>Disct Value</th>
							<th style='width: 10px;'>Gross</th>
							</tr>".$basketStr."
							<tr><td colspan='7'>Bulk Discount ({$bulkDiscountPercentage}%)</td><td>".$currencySymbol.number_format($bulkDiscountValue,2)."</td></tr>
							<tr><td colspan='7'>Sub Total</td><td>".$currencySymbol.number_format($netBillingAmount,2)."</td></tr>
							<tr><td colspan='7'>Vat</td><td>".$currencySymbol.number_format($vatAmount,2)."</td></tr>
							<tr><td colspan='7'>Net Amount</td><td>".$currencySymbol.number_format($netPrice,2)."</td></tr>
							<tr><td colspan='7'>Total Amount</td><td>".$currencySymbol.number_format($finalAmount,2)."</td></tr></table>";
			}
			if(isset($finalAmount)){
				$this->request->Session()->write('finalAmount', $finalAmount);
			}
			$totalItems = count($this->request->Session()->read('new_sale_basket'));
			
			if($productCounts){
				//$productCounts product(s) added to the cart.
				$flashMessage = "Total item Count:$totalItems.<br/>$basketStr";
			}else{
				$flashMessage = "No item added to the cart. Item Count:$productCounts";
			}
			
			//$this->Flash->success($flashMessage,array('escape' => false));
			return $this->redirect(array('action' => "new_sale/$customerId/page:$current_page"));
		
		}elseif(array_key_exists('check_out',$this->request['data'])){
			//pr($_SESSION);die;
			$this->set(compact('customerId'));
			return $this->redirect(array('action'=>'new_sale_checkout',$customerId));
		}elseif(array_key_exists('empty_basket',$this->request['data'])){
			$this->request->Session()->delete('new_sale_bulk_discount');
			$this->request->Session()->delete('receipt_required');
			if($this->request->Session()->delete('new_sale_basket')){
				$this->SessionRestore->delete_from_session_backup_table($this->request->params['controller'], 'new_sale', 'new_sale_basket', $kiosk_id);
			}
			$this->request->Session()->delete('finalAmount');
			$flashMessage = "Basket is empty; Add new items to cart!";
			$this->Flash->success($flashMessage,array('escape' => false));
			return $this->redirect(array('action' => "new_sale/$customerId/page:$current_page"));			
		}elseif(array_key_exists('calculate',$this->request['data'])){
			return $this->redirect(array('action' => "new_sale/$customerId/page:$current_page"));
		}else{
			//echo "hi";die;
			$sessionBulkDiscount = 0;
			
			if((int)$this->request->Session()->read('new_sale_bulk_discount')){
					$sessionBulkDiscount = $this->Session->read('new_sale_bulk_discount');
			}
			
			$specialInvoice = $this->request->Session()->read('special_invoice');
			if(empty($specialInvoice)){
					$specialInvoice = 0;
			}
			
			if($country=="OTH" || $specialInvoice == 1){
				$vat = 0;
			}else{
				$vat = $this->VAT;
			}
					
			$sessionReceiptRequired = 0;
			if(array_key_exists('receipt_required',$this->request['data'])){
				$receipt_required = $this->request['data']['receipt_required'];
				$this->request->Session()->write('receipt_required',$receipt_required);
				$sessionReceiptRequired = $this->Session->read('receipt_required');
			}
			
			$specialInvoice = $this->request->Session()->read('special_invoice');
				if(empty($specialInvoice)){
						$specialInvoice = 0;
				}
			if($specialInvoice == 1){//case when temprd invoice is being created, changing the table source
						$receiptTable	=	't_product_receipts';
				}
			$productArr = array();
			//---------------------Step 1 code -------------------------------			
			//$customer = $this->request['data']['customer'];
			$receiptData = array(
						'customer_id' => $customerId,
						'address_1' => $address1,
						'address_2' => $address2,
						'city' => $del_city,
						'state' => $del_state,
						'zip' => $del_zip,
						'vat' => $vat,
						'processed_by' => $user_id,
						'fname' => $firstName,
						'lname' => $lastName,
						'mobile' => $mobileNum,
						'email' => $emailId,
						'status'=> 1//sending status 1, will modify it to 0 once the payment goes through and data gets saved
					     );
			$this->Receipt->setSource($receiptTable);
			$this->Receipt->create();
			$this->Receipt->save($receiptData);
			//--------code for reading cake query---
			$dbo = $this->Receipt->getDatasource();
			$logData = $dbo->getLog();
			$getLog = end($logData['log']);
			//echo $getLog['query'];
			//---------------------Step 1 code -------------------------------
			
			//---------------------Step 2 code -------------------------------
			$receiptId = $this->Receipt->id;
			$session_basket = $this->Session->read('new_sale_basket');
			// NORMAL SUBMIT CASE OTHER THAN BASKET
			if(array_key_exists('KioskProductSale',$this->request['data'])){
				foreach($this->request['data']['KioskProductSale']['item'] as $key => $item){
					$bulkDiscountPercentage = 0;
					if((int)$item){
						$currentQuantity = $this->request['data']['KioskProductSale']['p_quantity'][$key];
						$productID = $this->request['data']['KioskProductSale']['product_id'][$key];
						$remarks = $this->request['data']['KioskProductSale']['remarks'][$key];
						$productTitle = $this->request['data']['KioskProductSale']['product'][$key];
						$discount = $this->request['data']['KioskProductSale']['discount'][$key];
						$selling_price = $this->request['data']['KioskProductSale']['selling_price'][$key];
						$discountStatus = $this->request['data']['KioskProductSale']['discount_status'][$key];
						$quantity = $this->request['data']['KioskProductSale']['quantity'][$key];
						$bulkDiscountPercentage = $this->request['data']['bulk_discount'];
						$netAmount = $this->request['data']['KioskProductSale']['net_amount'][$key];
						$priceWithoutVat = $this->request['data']['KioskProductSale']['price_without_vat'][$key];
						if(empty($netAmount)){$netAmount = $priceWithoutVat;}
						if($netAmount >= $priceWithoutVat){
							$selling_price = $netAmount;
							$priceWithoutVat = $netAmount;
						}
						$priceCheck = $this->Product->find('first',array(
													  'conditions' => array('Product.id'=>$productID),
													  'fields' => array('selling_price','product'),
													  'recursive' => -1,
													  )
											);
						
						$originalPrice = $priceCheck['Product']['selling_price'];
						//$product_name = $priceCheck['Product']['product'];
						//$discountValue = $originalPrice * $discount/100;
						$discountValue = $priceWithoutVat * $discount/100;
						//$minPrice = round($originalPrice-$discountValue,2);
						$minPrice = round($priceWithoutVat-$discountValue,2);
						
						if($netAmount != $selling_price && $netAmount < $minPrice){
							$flashMessage = "Selling price cannot be less than the minimum allowed price";
							$this->Session->setFlash($flashMessage);
							$this->redirect(array('action' => "new_sale/$customerId/page:$current_page"));
							die;
						}
					}
					
					//pr($bulkDiscountPercentage);die;
					if($bulkDiscountPercentage > 0){
						if($bulkDiscountPercentage > 100){
							$flashMessage = "Bulk discount percentage must be less than 100";
							$this->Session->setFlash($flashMessage);
							$this->redirect(array('action' => "new_sale/$customerId/page:$current_page"));
							$this->Receipt->query("DELETE FROM `$receiptTable` WHERE `id` = '$receiptId'");
							$alterQuery = "ALTER TABLE `$receiptTable` AUTO_INCREMENT = $receiptId";
							$this->Receipt->query($alterQuery);
							$this->redirect(array('action' => "new_order"));
							die;
						}elseif($bulkDiscountPercentage < 0){
							$flashMessage = "Bulk discount percentage must be a positive number";
							$this->Session->setFlash($flashMessage);
							$this->redirect(array('action' => "new_sale/$customerId/page:$current_page"));
							$this->Receipt->query("DELETE FROM `$receiptTable` WHERE `id` = '$receiptId'");
							$alterQuery = "ALTER TABLE `$receiptTable` AUTO_INCREMENT = $receiptId";
							$this->Receipt->query($alterQuery);
							$this->redirect(array('action' => "new_order"));
							die;
						}
					}
					
					if(array_key_exists('bulk_discount',$this->request['data'])){
						$bulk_discount = $this->request['data']['bulk_discount'];
						$this->Session->write('new_sale_bulk_discount',$bulk_discount);
						$sessionBulkDiscount = $this->Session->read('new_sale_bulk_discount');
					}
					
					if((int)$item && $quantity <= $currentQuantity && !empty($quantity)){
						$productArr[$productID] = array(
										'quantity' => $quantity,
										'selling_price' => $selling_price,
										'net_amount' => $netAmount,
										'price_without_vat' => $priceWithoutVat,
										'remarks' => $remarks,
										'product' => $productTitle,
										'discount' => $discount,
										'discount_status' => $discountStatus,
										'bulk_discount' => $bulkDiscountPercentage
										);
						$productCounts++;
					}
					
					if(array_key_exists('special_invoice', $this->request->data)){
						if($this->request->data['special_invoice'] == 1){
							$this->Session->write('special_invoice', 1);
						}else{
							$this->Session->delete('special_invoice');
						}
					}else{
						$this->Session->delete('special_invoice');
					}
				}
			}
			//pr($productArr);
			//pr($session_basket);die;
			$sum_total = $this->add_arrays(array($productArr,$session_basket));
			$this->Session->write('new_sale_basket', $sum_total);
			$sum_total = $this->Session->read('new_sale_basket');
			//pr($sum_total);die;
			if(empty($sum_total)){
				$flashMessage = "Failed to create order. <br />Please select quantity atleast for one product!";
				$this->Session->setFlash($flashMessage);
				$redirectTo = array('action' => "new_sale/$customerId/page:$current_page");
				if(!isset($receiptId)){$receiptId = 0;}
				$this->rollback_sale($receiptId, $kiosk_id, 'products_selling', $redirectTo);
				$this->redirect($redirectTo);
				$this->Receipt->query("DELETE FROM `$receiptTable` WHERE `id` = '$receiptId'");
				die;
			}
			
			$datetime = date('Y-m-d H:i:s');
			
			$billingAmount = 0;
			//-----------------------------------------------------------------------------------------
			$basketStr = "";
			$counter = $totalBillingAmount = $totalDiscountAmount = $vatAmount = 0;
			
			if(is_array($sum_total)){ //pr($sum_total);
				$productCodeArr = array();
				foreach($sum_total as $key => $basketItem){
					if($key == 'error')continue;
					$productCodeArr[] = $this->Product->find('first',array(
																		   'conditions' => array('Product.id' => $key),
																		   'fields' => array('id','product_code'),
																		   'recursive' => -1));
				}
				$productCode = array();
				if(!empty($productCodeArr)){
					foreach($productCodeArr as $k=>$productCodeData){
						$productCode[$productCodeData['Product']['id']] = $productCodeData['Product']['product_code'];
					}
				}
				//pr($sum_total);die;
				foreach($sum_total as $key => $basketItem){
					$counter++;
					$vat = $this->VAT;
					$vatItem = $vat/100;
					$discount = $basketItem['discount'];				
					$sellingPrice = $basketItem['selling_price'];
					$netAmount = $basketItem['net_amount'];
					$price_without_vat = $basketItem['price_without_vat'];
					$itemPrice = $basketItem['selling_price']/(1+$vatItem);
					$discountAmount = $price_without_vat*$basketItem['discount']/100* $basketItem['quantity'];
					$totalDiscountAmount+= $discountAmount;
					$totalItemPrice = $price_without_vat * $basketItem['quantity'];
					$bulkDiscountPercentage = $sessionBulkDiscount;
					$totalItemCost = $totalItemPrice-$discountAmount;
					$totalBillingAmount+=$totalItemCost;
					$vatperitem = $basketItem['quantity']*($sellingPrice-$itemPrice);
					$bulkDiscountValue = $totalBillingAmount*$bulkDiscountPercentage/100;
					$netBillingAmount = $totalBillingAmount-$bulkDiscountValue;
					$netPrice = $netBillingAmount;
					$vatAmount = $netBillingAmount*$vatItem;
					
					if($country=="OTH" || $specialInvoice == 1){
						$finalAmount = $netBillingAmount;
					}else{
						$finalAmount = $netBillingAmount+$vatAmount;
					}
					
					$basketStr.="<tr>
						<td>{$counter})</td>
						<td>{$productCode[$key]}</td>
						<td>".$basketItem['product']."</td>
						<td>".$basketItem['quantity']."</td>
						<td>".$currencySymbol.number_format($price_without_vat,2)."</td>
						<td> ".$discount."</td>
						<td>".$currencySymbol.number_format($discountAmount,2)."</td>
						<td>".$currencySymbol.number_format($totalItemCost,2)."</td></tr>";
				}
			}
			if(!empty($basketStr)){
				$basketStr = "<table><tr>
							<th style='width: 10px;'>Sr No</th>
							<th style='width: 132px;'>Product Code</th>
							<th style='width: 445px;'>Product</th>
							<th style='width: 30px;'>Qty</th>
							<th style='width: 99px;'>Price/Item</th>
							<th style='width: 40px;'>Disct %</th>
							<th style='width: 10px;'>Disct Value</th>
							<th style='width: 10px;'>Gross</th>
							</tr>".$basketStr."
							<tr><td colspan='7'>Sub Total</td><td>".$currencySymbol.number_format($netBillingAmount,2)."</td></tr>
							<tr><td colspan='7'>Bulk Discount ({$bulkDiscountPercentage}%)</td><td>".$currencySymbol.number_format($bulkDiscountValue,2)."</td></tr>
							<tr><td colspan='7'>Vat</td><td>".$currencySymbol.number_format($vatAmount,2)."</td></tr>
							<!--tr><td colspan='7'>Net Amount</td><td>".$currencySymbol.number_format($netPrice,2)."</td></tr-->
							<tr><td colspan='7'>Total Amount</td><td>".$currencySymbol.number_format($finalAmount,2)."</td></tr></table>";
			}
			$this->Session->write('finalAmount', $finalAmount);
			$totalItems = count($this->Session->read('new_sale_basket'));
			//-------------------------------------------------------------
			$flashMessage = "Please review the order details and make payment:<br/>$basketStr";
			$this->Session->setFlash($flashMessage);					
			return $this->redirect(array('controller'=>'product_receipts','action' => "sale_payment",$receiptId,$customerId));
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
		
	public function finalWSale(){
		if(!empty($this->request->query) && array_key_exists('new_sale_basket',$_SESSION)){
			if(!empty($_SESSION['new_sale_basket'])){
				$basket = $_SESSION['new_sale_basket'];
			}else{
				echo json_encode(array('error' => 'basket is empty'));die;
			}
			
			if(array_key_exists('new_sale_bulk_discount',$_SESSION)){
				$bulkDiscount = $this->request->Session()->read('new_sale_bulk_discount');
			}else{
				$bulkDiscount = 0;
			}
			
			
			
			$customer_id = $this->request->query['customer_id'];
			if(!isset($customer_id) || empty($customer_id)){
				echo json_encode(array('error' => 'No Customer Id Found'));die;
			}
			
			
			$kiosk_id = $this->request->Session()->read('kiosk_id');		
			if(!empty($kiosk_id)){
				//wholesale retailer
				$receiptTable_source = "kiosk_{$kiosk_id}_product_receipts";
				$salesTable_source = "kiosk_{$kiosk_id}_product_sales";
				$productTable_source = "kiosk_{$kiosk_id}_products";
                $paymentdetails_source = "kiosk_{$kiosk_id}_payment_details";
				//$paymentTable = "kiosk_{$kiosk_id}_payment_details";
			}else{
				//admin
				$receiptTable_source = "product_receipts";
				$salesTable_source = "kiosk_product_sales";
				$productTable_source = "products";
                $paymentdetails_source = "payment_details";
				//$paymentTable = "kiosk_payment_details";
			}
			$paymentdetailsTable = TableRegistry::get($paymentdetails_source,[
                                                                                    'table' => $paymentdetails_source,
                                                                                ]);
			
			$specialInvoice = $this->request->Session()->read('special_invoice');
				if(empty($specialInvoice)){
						$specialInvoice = 0;
				}
			if($specialInvoice == 1){//case when temprd invoice is being created, changing the table source
						$receiptTable_source	=	't_product_receipts';
						$paymentTable_source = 't_payment_details';
						$paymentdetailsTable = TableRegistry::get($paymentTable_source,[
                                                                                    'table' => $paymentTable_source,
                                                                                ]);
			}
			
			$part_time = $this->request->query['part_time'];
			$payment_1 = $this->request->query['payment_1'];
			$payment_2 = $this->request->query['payment_2'];
			$payment_3 = $this->request->query['payment_3'];
			$method_1 = $this->request->query['method_1'];
			$method_2 = $this->request->query['method_2'];
			$method_3 = $this->request->query['method_3'];
			$final_amount = $this->request->query['final_amount'];
			if(empty($final_amount)){
				echo json_encode(array('error' => 'final amount is empty'));die;
			}
			if($part_time == 1){
				if($payment_1 + $payment_2 + $payment_3 != $final_amount){
					echo json_encode(array('error' => 'amount is not matching'));die;
				}
			}else{
				if($payment_1 != $final_amount){
					echo json_encode(array('error' => 'amount is not matching'));die;
				}
			}
			$vat = $this->VAT;
			$currencySymbol = $this->setting['currency_symbol'];
			$customerAccountDetails_query = $this->Customers->find('all',array(
										'conditions'=>array('Customers.id'=>$customer_id)
										)
									);
			$customerAccountDetails_query = $customerAccountDetails_query->hydrate(false);
            if(!empty($customerAccountDetails_query)){
                $customerAccountDetails = $customerAccountDetails_query->first();
            }else{
                $customerAccountDetails = array();
            }
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
			
			if($country=="OTH" || $specialInvoice == 1){
				$vat = 0;
			}else{
				$vat = $this->VAT;
			}
			$user_id = $this->request->session()->read('Auth.User.id');//$this->Auth->user('id');
			
			$receiptData = array(
						'customer_id' => $customer_id,
						'address_1' => $address1,
						'address_2' => $address2,
						'city' => $del_city,
						'state' => $del_state,
						'zip' => $del_zip,
						'vat' => $vat,
						'processed_by' => $user_id,
						'fname' => $firstName,
						'lname' => $lastName,
						'mobile' => $mobileNum,
						'email' => $emailId,
						'status'=> 1//sending status 1, will modify it to 0 once the payment goes through and data gets saved
					     );
			if($specialInvoice == 1){
				if(empty($kiosk_id)){
					$receiptData['kiosk_id'] = 0;
				}else{
					$receiptData['kiosk_id'] = $kiosk_id;
				}	
			}
			$receiptTable = TableRegistry::get($receiptTable_source,[
																		'table' => $receiptTable_source,
																	]);
            $receiptTable->behaviors()->load('Timestamp');
            $recit_entity = $receiptTable->newEntity();
            $recit_entity = $receiptTable->patchEntity($recit_entity, $receiptData);
			 
			$receiptTable->save($recit_entity);
			$receiptId = $recit_entity->id;
			if(!empty($receiptId)){
				$counter = 0;
				if($part_time == 1){
					if($payment_1 + $payment_2 + $payment_3 == $final_amount){
						$payment_status = 1;
						if($method_1 == "On Credit"){
							$payment_status = 0;
						}
						if($method_1 != "Select Payment Method"){
							$paymentDetailData = array(
															'product_receipt_id' => $receiptId,
															'payment_method' => $method_1,
															'amount' => $payment_1,
															'payment_status' => $payment_status,
															'status' => 1,
														);
							if($specialInvoice == 1){
								if(empty($kiosk_id)){
									$paymentDetailData['kiosk_id'] = 0;
								}else{
									$paymentDetailData['kiosk_id'] = $kiosk_id;
								}	
							}
							$paymentdetailsTable->behaviors()->load('Timestamp');
							$PaymentDetails_entity = $paymentdetailsTable->newEntity();
							$PaymentDetails_entity = $paymentdetailsTable->patchEntity($PaymentDetails_entity, $paymentDetailData);
							
							$paymentdetailsTable->save($PaymentDetails_entity);
						}
						if($method_2 == "On Credit"){
							$payment_status = 0;
						}
						if($method_2 != "Select Payment Method"){
							$paymentDetailData = array(
															'product_receipt_id' => $receiptId,
															'payment_method' => $method_2,
															'amount' => $payment_2,
															'payment_status' => $payment_status,
															'status' => 1,
														);
							if($specialInvoice == 1){
								if(empty($kiosk_id)){
									$paymentDetailData['kiosk_id'] = 0;
								}else{
									$paymentDetailData['kiosk_id'] = $kiosk_id;
								}	
							}
                            $paymentdetailsTable->behaviors()->load('Timestamp');
							$PaymentDetails_entity = $paymentdetailsTable->newEntity();
							$PaymentDetails_entity = $paymentdetailsTable->patchEntity($PaymentDetails_entity, $paymentDetailData);
							$paymentdetailsTable->save($PaymentDetails_entity);
						}
						if($method_3 == "On Credit"){
							$payment_status = 0;
						}
						if($method_3 != "Select Payment Method"){
							$paymentDetailData = array(
															'product_receipt_id' => $receiptId,
															'payment_method' => $method_3,
															'amount' => $payment_3,
															'payment_status' => $payment_status,
															'status' => 1,
														);
							if($specialInvoice == 1){
								if(empty($kiosk_id)){
									$paymentDetailData['kiosk_id'] = 0;
								}else{
									$paymentDetailData['kiosk_id'] = $kiosk_id;
								}	
							}
                            $paymentdetailsTable->behaviors()->load('Timestamp');
							$PaymentDetails_entity = $paymentdetailsTable->newEntity();
							$PaymentDetails_entity = $paymentdetailsTable->patchEntity($PaymentDetails_entity, $paymentDetailData);
							$paymentdetailsTable->save($PaymentDetails_entity);
						}
						$counter++;
					}else{
						echo json_encode(array('error' => 'amount is not matching'));
					}
				}else{
					if($payment_1== $final_amount){
						if($method_1 == "On Credit"){
							$payment_status = 0;
						}else{
							$payment_status = 1;
						}
						$paymentDetailData = array(
														'product_receipt_id' => $receiptId,
														'payment_method' => $method_1,
														'amount' => $payment_1,
														'payment_status' => $payment_status,
														'status' => 1,
													);
						if($specialInvoice == 1){
								if(empty($kiosk_id)){
									$paymentDetailData['kiosk_id'] = 0;
								}else{
									$paymentDetailData['kiosk_id'] = $kiosk_id;
								}	
							}
                        $paymentdetailsTable->behaviors()->load('Timestamp');
						$PaymentDetails_entity = $paymentdetailsTable->newEntity();
							$PaymentDetails_entity = $paymentdetailsTable->patchEntity($PaymentDetails_entity, $paymentDetailData);
							//pr($PaymentDetails_entity);die;
							$paymentdetailsTable->save($PaymentDetails_entity);
							$counter++;
					}else{
						echo json_encode(array('error' => 'amount is not matching'));die;
					}
				}
				if($counter > 0){
					$this->do_sale($basket,$receiptId,$kiosk_id,$user_id,$customer_id,$specialInvoice,$bulkDiscount,$final_amount);
				}
			}else{
				echo json_encode(array('error' => 'error in generating recipt'));die;
			}
		}else{
			echo json_encode(array('error' => 'either basket or query is empty'));die;
		}
	}
	
	public function do_sale($basket,$receiptId,$kiosk_id,$user_id,$customer_id,$specialInvoice,$bulkDiscount,$final_amount){
		$CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');		
		if(!empty($basket)){
			if(!empty($kiosk_id)){
				$receiptTable_source = "kiosk_{$kiosk_id}_product_receipts";
				$salesTable_source = "kiosk_{$kiosk_id}_product_sales";
				$productTable_source = "kiosk_{$kiosk_id}_products";
				$paymentTable_source = "kiosk_{$kiosk_id}_payment_details";
			}else{
				$receiptTable_source = "product_receipts";
				$salesTable_source = "kiosk_product_sales";
				$productTable_source = "products";
				$paymentTable_source = "payment_details";
			}
			//$this->KioskProductSale->setSource($salesTable);
			
			$KioskProductSaleTable = TableRegistry::get($salesTable_source,[
                                                                                    'table' => $salesTable_source,
                                                                                ]);
			$ProductReceiptTable = TableRegistry::get($receiptTable_source,[
                                                                                    'table' => $receiptTable_source,
                                                                                ]);
            $productTable = TableRegistry::get($productTable_source,[
                                                                                    'table' => $productTable_source,
                                                                                ]);
			
			if($specialInvoice == 1){
				$KioskProductSaleTable = TableRegistry::get("t_kiosk_product_sales",[
                                                                                    'table' => "t_kiosk_product_sales",
                                                                                ]);
				$ProductReceiptTable = TableRegistry::get("t_product_receipts",[
                                                                                    'table' => "t_product_receipts",
                                                                                ]);
				$PaymentDetailTable = TableRegistry::get("t_payment_details",[
                                                                                    'table' => "t_payment_details",
                                                                                ]);

			}else{
				$PaymentDetailTable = TableRegistry::get("payment_details",[
                                                                                    'table' => "payment_details",
                                                                                ]);
			}
			
			$settingArr = $this->setting;
			$customerData_query = $this->Customers->find('all',array(
														'conditions' => array('Customers.id'=>$customer_id)
														)
											);
			$customerData_query = $customerData_query->hydrate(false);
            if(!empty($customerData_query)){
                $customerData = $customerData_query->first();
            }else{
                $customerData = array();
            }
			$country = $customerData['country'];
			$vat = $this->VAT;
			
			$paymentDetails_query = $PaymentDetailTable->find('all',array('conditions' => array('product_receipt_id' => $receiptId),
																	));
			$paymentDetails_query = $paymentDetails_query->hydrate(false);
            if(!empty($paymentDetails_query)){
                $paymentDetails = $paymentDetails_query->toArray();
            }else{
                $paymentDetails = array();
            }
			$payment_method = array();
			foreach($paymentDetails as $key=>$paymentDetail){
				$payment_method[] = $paymentDetail['payment_method']." ".$CURRENCY_TYPE.$paymentDetail['amount'];
			}
			$userTypeData_query = $this->Users->find('all',array(
														'conditions' => array('Users.id' => $user_id),
														'fields' => array('user_type'),
														)
										  );
			$userTypeData_query = $userTypeData_query->hydrate(false);
            if(!empty($userTypeData_query)){
                $userTypeData = $userTypeData_query->first();
            }else{
                $userTypeData_query = array();
            }
			//pr($userTypeData);
			if(!empty($userTypeData)){
				if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER || $this->request->session()->read('Auth.User.group_id') == MANAGERS || $this->request->session()->read('Auth.User.group_id') == SALESMAN
				   ){
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
				if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER && $this->request->session()->read('Auth.User.group_id') == MANAGERS //|| $this->request->session()->read('Auth.User.group_id') == SALESMAN
				   ){
					$sale_type = 1;
				}else{
					$sale_type = 0;
				}
				
			}
			$totalCost = 0;
			$count = 0;
			if(empty($kiosk_id)){
				$kiosk_id = 0;
			}
			
			foreach($basket as $product_id => $productData){
				$quantity = $productData['quantity'];
				$discount = $productData['discount'];
				$costPrice_query = $productTable->find('list',
															[
																'keyField' => 'id',
																'valueField' => 'cost_price',
																'conditions' => ['id' => $product_id]
															]
												   );
				$costPrice_query = $costPrice_query->hydrate(false);
                if(!empty($costPrice_query)){
                    $costPrice = $costPrice_query->toArray();
                }else{
                    $costPrice = array();
                }
				$kioskProductSaleData = array(
											'kiosk_id' => $kiosk_id,
											'product_receipt_id' => $receiptId,
											'sale_price' => $productData['price_without_vat'],//selling price //sourabh
											'quantity' => $quantity,
											'cost_price' => $costPrice[$product_id],
											'product_id' => $product_id,
											'discount' => $discount,
											'sale_type' => $sale_type,
											'sold_by' => $user_id
										);
				$totalCost+=$costPrice[$product_id] * $quantity;
                $KioskProductSaleTable->behaviors()->load('Timestamp');
				 $KioskProductSale_entity = $KioskProductSaleTable->newEntity();
				 $KioskProductSale_entity = $KioskProductSaleTable->patchEntity($KioskProductSale_entity, $kioskProductSaleData);
				if($KioskProductSaleTable->save($KioskProductSale_entity)){
					$product_codes_query = $productTable->find('list',
																	[
																		'keyField' => 'id',
																		'valueField' => 'product_code',
																		'condition' => ['id' => $product_id]
																	]);
					$product_codes_query = $product_codes_query->hydrate(false);
					$product_codes = $product_codes_query->toArray();
					$p_kiosk_id = $kiosk_id;
					if($p_kiosk_id == 0 || $p_kiosk_id == ""){
						$p_kiosk_id = 10000;
					}
					if(!empty($bulkDiscount)){
						$bulk_value = $productData['net_amount'] * ($bulkDiscount/100);
						$after_bulk_value  = $productData['net_amount'] - $bulk_value;
						$selling_price_withot_vat = $after_bulk_value*$quantity;
					}else{
						$after_bulk_value = $productData['net_amount'];
						$selling_price_withot_vat = $productData['net_amount']*$quantity;
					}
					
					if($country != 'OTH'){
						$vat_amount = $after_bulk_value * ($vat/100);
						$total_vat = $vat_amount * $quantity;
					}else{
						$total_vat = 0;
					}
					
					$data = array(
						 'quantity' => $quantity,
						 'product_code' => $product_codes[$product_id],
						 'selling_price_withot_vat' => $selling_price_withot_vat,
						 'vat' => $total_vat,
					);
					$this->insert_to_ProductSellStats($product_id,$data,$p_kiosk_id,$operations = '+');
					$tempProductSalesData = array(
					//we are sending the warehouse sale to a temporary productsale table and at the day end, we are consolidating the product 
					//quantities and creating a new entry in reference_stock with a reference as dr_stock_out (through cron update_reference_stock)
													'product_id' => $product_id,
													'quantity' => $quantity,
													'cost_price' => $costPrice[$product_id]
																				);
					//$this->FaultyProducts->setSource('t_temp_product_sales');
					$t_temp_product_sales_table = TableRegistry::get("t_temp_product_sales",[
                                                                                    'table' => "t_temp_product_sales",
                                                                                ]);
                    $t_temp_product_sales_table->behaviors()->load('Timestamp');
					$t_temp_product_sales_entity = $t_temp_product_sales_table->newEntity();
					$t_temp_product_sales_entity = $t_temp_product_sales_table->patchEntity($t_temp_product_sales_entity,$tempProductSalesData);
					
					
					$t_temp_product_sales_table->save($t_temp_product_sales_entity);
					$count++;
				}
			}
			if(count($basket) == $count){
				//$this->ProductReceipt->id = $receiptId;
                $ProductReceiptTable->behaviors()->load('Timestamp');
				$ProductReceiptTable_entity = $ProductReceiptTable->get($receiptId);
				$data_to_save = array(
											'orig_bill_amount' => round($final_amount,2),
											'bill_amount' => round($final_amount,2),
											'sale_type' => $sale_type,
											'bill_cost' => round($totalCost,2),
											'bulk_discount' => $bulkDiscount,
											'status' => 0,
										);
				
				$ProductReceiptTable_entity = $ProductReceiptTable->patchEntity($ProductReceiptTable_entity, $data_to_save);
				$ProductReceiptTable->save($ProductReceiptTable_entity);
				$productData = array('quantity' => "Product.quantity - $quantity");
				
				foreach($basket as $productID => $productData){
					$quantity = $productData['quantity'];
					$updateQry = "UPDATE `$productTable_source` SET `quantity` = `quantity` - $quantity WHERE `$productTable_source`.`id` = '$productID'";
					$rand = rand(500,10000);
					//mail('kalyanrajiv@gmail.com', "Line #3554- $rand", $updateQry);
					 $conn = ConnectionManager::get('default');
					$stmt = $conn->execute($updateQry);
					//rasu
				}
			}else{
				//delete prduct records added to KioskProductSale and receipt id for failed case
				$del1 = "Delete from $paymentTable_source WHERE `product_receipt_id` = $receiptId";
				$conn = ConnectionManager::get('default');
				$stmt = $conn->execute($del1);
				
				$del2 = "Delete from $salesTable_source WHERE `product_receipt_id` = $receiptId";
				$conn = ConnectionManager::get('default');
				$stmt = $conn->execute($del2);
				
				$del3 = "Delete from $receiptTable_source WHERE `id` = $receiptId";
				$conn = ConnectionManager::get('default');
				$stmt = $conn->execute($del3);
				//$this->Session->setFlash("Failed to process invoice, please try again");
				echo json_encode(array("error" => "Failed to process invoice, please try again"));die;
				//return $this->redirect(array('controller'=>'customers','action'=>'index'));
			}
			if($count > 0){
				$new_kiosk_id = $kiosk_id;
				if(empty($new_kiosk_id)){
					$new_kiosk_id = 10000;
				}
				$new_kiosk_data = $this->Kiosks->find("all",['conditions' => ['id' => $new_kiosk_id]])->toArray();
				//Configure::load('common-arrays');
				$countryOptions = Configure::read('uk_non_uk');
				$fullAddress = $kioskDetails = $kioskName = $kioskAddress1 = $kioskAddress2 = $kioskCity = $kioskState = $kioskZip  = $kioskZip = $kioskContact = $kioskCountry = $kioskTable = "";
				if($this->request->session()->read('Auth.User.group_id')== KIOSK_USERS && $this->request->session()->read('Auth.User.user_type')=='wholesale'){
					$kioskDetails_query = $this->Kiosks->find('all',array('conditions'=>array('Kiosks.id'=>$kiosk_id),'recursive'=>-1,'fields'=>array('id','name','address_1','address_2','city','state','zip','contact','country')));
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
					
					if(!empty($kioskAddress1)){$fullAddress.=$kioskAddress1.", ";}
					if(!empty($kioskAddress2)){$fullAddress.=$kioskAddress2.", ";}
					if(!empty($kioskCity)){$fullAddress.=$kioskCity.", ";}
					if(!empty($kioskState)){$fullAddress.=$kioskState.", ";}
					if(!empty($kioskZip)){$fullAddress.=$kioskZip.", ";}
					if(!empty($kioskCountry)){$fullAddress.=$countryOptions[$kioskCountry];}
						
					$kioskTable = "<table>
						<tr><td style='color: chocolate;'>".$kioskName."</td></tr>
						<tr><td style='font-size: 11px;'>".$fullAddress."</td></tr>
						</table>";
				}
				
				$receiptRequired = $this->request->Session()->read('receipt_required');
				$options = array(
									'conditions' => array('id' => $receiptId),
									//'contain' => ['kiosk_product_sales']
								);
				
				$productReceipt_query = $ProductReceiptTable->find('all', $options);
				$productReceipt_query = $productReceipt_query->hydrate(false);
                if(!empty($productReceipt_query)){
                    $productReceipt = $productReceipt_query->first();
                }else{
                    $productReceipt = array();
                }
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
                    $userName_query = array();
                }
				$user_name = $userName['username'];
				
				$res_query = $KioskProductSaleTable->find('all',[
														'conditions' => ['product_receipt_id' => $receiptId] 
														]);
				$res_query = $res_query->hydrate(false);
                if(!empty($res_query)){
                    $res = $res_query->toArray();
                }else{
                    $res = array();
                }
				
				foreach($res as $key => $productDetail){
					$productIdArr[] = $productDetail['product_id'];
				}
				
				foreach($productIdArr as $product_id){
					
					$product_detail_query = $productTable->find('all', array(
																			'conditions' => array('id' => $product_id),
																			'fields' => array('id','product','product_code'),
																			)
															);
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
				$send_by_email = Configure::read('send_by_email');
				$emailSender = Configure::read('EMAIL_SENDER');
				if($specialInvoice != 1){
					if($receiptRequired == 1){
						$Email = new Email();
						$Email->config('default');
						$Email->viewVars(array
												(
												 'productReceipt' => $productReceipt,
												 'payment_method' => $payment_method,
												 'vat' => $vat,
												 'settingArr' =>$settingArr,
												 'user_name'=>$user_name,
												 'productName'=>$productName,
												 'productCode'=>$productCode,
												 'kioskTable'=>$kioskTable,
												 'kioskContact'=>$kioskContact,
												 'countryOptions'=>$countryOptions,
												 'cust_data'=>$customerData,
												 'sale_table'=>$res,
												 'new_kiosk_data' => $new_kiosk_data
												)
										);
						//$Email->config(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
						//$Email = new CakeEmail(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
						$emailTo = $customerData['email'];;
						$Email->template('receipt_new_sale');
						$Email->emailFormat('both');
						$Email->to($emailTo);
						$Email->transport(TRANSPORT);
						$Email->from([$send_by_email => $emailSender]);
						//$Email->sender("sales@oceanstead.co.uk");
						$Email->subject('Order Receipt');
						$Email->send();
					}
				}
				if($this->request->Session()->delete('new_sale_basket')){
					$this->SessionRestore->delete_from_session_backup_table($this->request->params['controller'], 'new_sale', 'new_sale_basket', $kiosk_id);
				}
				$this->request->Session()->delete('receipt_required');
				$this->request->Session()->delete('new_sale_bulk_discount');
				$this->Flash->success("Invoice has been saved");
				echo json_encode(array('status' => "Invoice has been saved"));die;
				//return $this->redirect(array('controller'=>'customers','action'=>'index'));
			}else{
				
			}
		}else{
			echo json_encode(array('error' => 'error'));die;
		}
	}
	
	public function searchNewSale($customerId = '', $keyword = ""){//search function in new_sale
        
         $kiosk_id = $this->request->Session()->read('kiosk_id');
		if($kiosk_id < 10000 && $kiosk_id != 0 && !empty($kiosk_id)){
			$productTable_source = "kiosk_{$kiosk_id}_products";
            
            
			//$this->Product->setSource("kiosk_{$kiosk_id}_products");
		}else{
			$productTable_source = "products";
		}
        
        $productTable = TableRegistry::get($productTable_source,[
                                                                            'table' => $productTable_source,
                                                                        ]);
        
		$vat = $this->setting['vat'];
		$this->set('vat',$vat);
		$customerAccountDetails_query = $this->Customers->find('all',array(
									'conditions'=>array('Customers.id'=>$customerId)
									)
								);
		$customerAccountDetails_query = $customerAccountDetails_query->hydrate(false);
        if(!empty($customerAccountDetails_query)){
            $customerAccountDetails = $customerAccountDetails_query->first();
        }else{
            $customerAccountDetails = array();
        }
		$searchKW = $this->request->query['search_kw'];		
		$categories_query = $this->Categories->find('all',array(
														'fields' => array('id', 'category','id_name_path'),
														'conditions' => array('Categories.status' => 1),
														'order' => 'Categories.category asc'
														)
											);
		$categories_query = $categories_query->hydrate(false);
        if(!empty($categories_query)){
            $categories = $categories_query->toArray();
        }else{
            $categories = array();
        }
       
		$conditionArr = array();
		//----------------------
		if(!empty($searchKW)){
			$conditionArr['OR']['LOWER(product) like '] =  strtolower("%$searchKW%");
			$conditionArr['OR']['LOWER(product_code) like '] =  strtolower("%$searchKW%");
			$conditionArr['OR']['LOWER(description) like '] =  strtolower("%$searchKW%");
			//'NOT'=>array('Product.quantity'=>0)
		}
		$conditionArr['NOT'] =  array('quantity' => 0);
		
		//----------------------
		if(array_key_exists('category',$this->request->query)){
			$category = $this->request->query['category'];
			if(isset($category)){
				$conditionArr['category_id IN'] =  $category;
			} 
		}
		 
		$this->paginate = [
						'conditions' => $conditionArr,
						'limit' => 20
					];
		$categories = $this->CustomOptions->category_options($categories,true);
		
		$products = $this->paginate($productTable);
		$categoryIdArr = array();
		foreach($products as $key=>$product){
			$categoryIdArr[] = $product['category_id'];
		}
        if(empty($categoryIdArr)){
            $categoryIdArr = array('0' =>'null');
        }
       // pr($categoryIdArr);die;
		$categoryName_query = $this->Categories->find('list',
														[
															'keyField' => 'id',
															'valueField' =>  'category',
															'conditions'=>['Categories.id IN'=>$categoryIdArr]
														]
											  );
		$categoryName_query = $categoryName_query->hydrate(false);
        if(!empty($categoryName_query)){
            $categoryName = $categoryName_query->toArray();
        }else{
            $categoryName = array();
        }
       // pr($categoryName);die;
		$this->set(compact('products','categories','customerAccountDetails','categoryName'));
		//$this->viewPath = 'Products';
		$this->render('new_sale');
		
	}
	
	public function new_sale($customerId = ''){
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		if(!empty($kiosk_id)){
			$productSource = "kiosk_{$kiosk_id}_products";
			$productSalesSource = "kiosk_{$kiosk_id}_product_sales";
			$recipt_source = "kiosk_{$kiosk_id}_product_receipts";
		}else{
			$productSource = "products";
			$productSalesSource = "kiosk_product_sales";
			$recipt_source = "product_receipts";
		}
		
		$productTable = TableRegistry::get($productSource,[
																	'table' => $productSource,
																]);
		$productSalesTable = TableRegistry::get($productSalesSource,[
																	'table' => $productSalesSource,
																]);
		$recipt_Table = TableRegistry::get($recipt_source,[
																	'table' => $recipt_source,
																]);
		$currencySymbol = $this->setting['currency_symbol'];
		$vat = $this->setting['vat'];
		$customerAccountDetails_query = $this->Customers->find('all',array(
									'conditions'=>array('Customers.id'=>$customerId)
									)
								);
		$customerAccountDetails_query = $customerAccountDetails_query->hydrate(false);
        if(!empty($customerAccountDetails_query)){
            $customerAccountDetails = $customerAccountDetails_query->first();
        }else{
            $customerAccountDetails = array();
        }
		//pr($customerAccountDetails);die;
		$country = $customerAccountDetails['country'];
		$this->paginate = [
						'limit' => 20,
						'model' => 'Product',
						'order' => ['product' => 'ASC'],
						'recursive' => -1,
						'conditions' => ['NOT'=>['quantity' => 0]]
				];
		//-----------------------------------------
		$categories = $this->Categories->find('all',array(
								'fields' => array('id', 'category','id_name_path'),
                                                                'conditions' => array('Categories.status' => 1),
								'order' => 'Categories.category asc'
								));
        $categories_query = $categories_query->hydrate(false);
        if(!empty($categories_query)){
            $categories = $categories_query->toArray();
        }else{
            $categories = array();
        }
		$categories = $this->CustomOptions->category_options($categories,true);
		
		//-----------------------------------------
		$session_basket = $this->request->Session()->read('new_sale_basket');
		$quantityError = '';
		if(array_key_exists('quantityError',$this->request->Session()->read())){
			$quantityError = $this->request->Session()->read('quantityError');
		}
		
		$bulkDiscountPercentage = 0;
		$bulkDiscountPercentage = $this->request->Session()->read('new_sale_bulk_discount');
		
		if(is_array($session_basket)){
			pr($session_basket);die;
			$specialInvoice = $this->request->Session()->read('special_invoice');
			if(empty($specialInvoice)){
					$specialInvoice = 0;
			}
			$productCodeArr = array();
			foreach($session_basket as $key => $basketItem){
				if($key == 'error')continue;
				$productCodeArr_query = $productTable->find('all',array('conditions'=>array('id'=>$key),'fields'=>array('id','product_code'),'recursive'=>-1));
				$productCodeArr_query = $productCodeArr_query->hydrate(false);
                if(!empty($productCodeArr_query)){
                    $productCodeArr[] = $productCodeArr_query->first();
                }else{
                    $productCodeArr[] = array();
                }
			}
			$productCode = array();
			if(!empty($productCodeArr)){
				foreach($productCodeArr as $k=>$productCodeData){
					$productCode[$productCodeData['id']]=$productCodeData['product_code'];
				}
			}
			$basketStr = "";
			$counter = 0;
			$totalBillingAmount = 0;
			$totalDiscountAmount = 0;
			$vatAmount = 0;
			foreach($session_basket as $key => $basketItem){
				if($basketItem['quantity'] == 0){
					unset($_SESSION['Basket'][$key]);
					$this->Flash->error("quantity cannot be zero");
					return;
				}
			}
			//pr($session_basket);die;
			foreach($session_basket as $key => $basketItem){
				if($key == 'error')continue;
				$counter++;
				$vat = $this->VAT;
				$vatItem = $vat/100;
				$discount = $basketItem['discount'];				
				$sellingPrice = $basketItem['selling_price'];
				$net_amount = $basketItem['net_amount'];
				$price_without_vat = $basketItem['price_without_vat'];
				$itemPrice = $basketItem['selling_price']/(1+$vatItem);
				$discountAmount = $price_without_vat * $basketItem['discount'] / 100 * $basketItem['quantity'];
				$totalDiscountAmount+= $discountAmount;
				$totalItemPrice = $price_without_vat * $basketItem['quantity'];
				//$pricebeforeVat = $itemPrice*$basketItem['quantity']-$discountAmount;
				$totalItemCost = $totalItemPrice-$discountAmount;
				$totalBillingAmount+=$totalItemCost;
				$vatperitem = $basketItem['quantity']*($sellingPrice-$itemPrice);
				$bulkDiscountValue = $totalBillingAmount*$bulkDiscountPercentage/100;
				$netBillingAmount = $totalBillingAmount-$bulkDiscountValue;
				$netPrice = $netBillingAmount;
				$vatAmount = $netBillingAmount * $vatItem;
				//round($netBillingAmount-$netPrice,2);
				
				if($country=="OTH" || $specialInvoice == 1){
					$finalAmount = $netBillingAmount;
				}else{
					$finalAmount = $netBillingAmount+$vatAmount;
				}
				
				$basketStr.="<tr>
						<td>{$counter})</td>
						<td>{$productCode[$key]}</td>
						<td>".$basketItem['product']."</td>
						<td>".$basketItem['quantity']."</td>
						<td>".$currencySymbol.number_format($price_without_vat,2)."</td>
						<td> ".$discount."</td>
						<td>".$currencySymbol.number_format($discountAmount,2)."</td>
						<td>".$currencySymbol.number_format($totalItemCost,2)."</td></tr>";
			}
			if(!empty($basketStr)){
				$basketStr = "<table><tr>
							<th style='width: 10px;'>Sr No</th>
							<th style='width: 132px;'>Product Code</th>
							<th style='width: 445px;'>Product</th>
							<th style='width: 30px;'>Qty</th>
							<th style='width: 99px;'>Sale Price</th>
							<th style='width: 40px;'>Disct %</th>
							<th style='width: 10px;'>Disct Value</th>
							<th style='width: 10px;'>Amount</th>
							</tr>".$basketStr."
							<tr><td colspan='7'>Bulk Discount ({$bulkDiscountPercentage}%)</td><td>".$currencySymbol.number_format($bulkDiscountValue,2)."</td></tr>
							<tr><td colspan='7'>Sub Total</td><td>".$currencySymbol.number_format($netBillingAmount,2)."</td></tr>
							<tr><td colspan='7'>Vat</td><td>".$currencySymbol.number_format($vatAmount,2)."</td></tr>
							<tr><td colspan='7'>Net Amount</td><td>".$currencySymbol.number_format($netPrice,2)."</td></tr>
							<tr><td colspan='7'>Total Amount</td><td>".$currencySymbol.number_format($finalAmount,2)."</td></tr></table>";
							
							
				$productCounts = count($this->request->Session()->read('new_sale_basket'));
			
				if($productCounts){
					//$productCounts product(s) added to the cart.
					$flashMessage = "$quantityError <br/> Total item Count:$productCounts.<br/>$basketStr";
				}else{
					$flashMessage = "$quantityError <br/> No item added to the cart. Item Count:$productCounts";
				}
				if(isset($finalAmount)){
					$this->request->Session()->write('finalAmount', $finalAmount);
				}
				if(array_key_exists('error',$session_basket)){
					$flashMessage = $session_basket['error']."<br/>".$flashMessage;
				}
                pr($flashMessage);die;
				$this->Flash->success($flashMessage,array('escape' => false));
			}elseif(!empty($quantityError)){
				$this->Flash->success($quantityError,array('escape' => false));
			}
			
			if(array_key_exists('quantityError',$this->request->Session()->read())){
				$this->request->Session()->delete('quantityError');
			}
		}
		//-----------------------------------------
		
		$products = $this->paginate($productTable);
		$categoryIdArr = array();
		foreach($products as $key=>$product){
			$categoryIdArr[] = $product['category_id'];
		}
		$categoryName_query = $this->Categories->find('list',[
                                                        'conditions'=>['Categories.id IN'=>$categoryIdArr],
                                                        'keyField' => 'id',
                                                        'valueField' => 'category'
                                                      ]
												);
        $categoryName_query = $categoryName_query->hydrate(false);
        if(!empty($categoryName_query)){
            $categoryName = $categoryName_query->toArray();
        }else{
            $categoryName = array();
        }
		$this->set(compact('categories','customerAccountDetails','customerId','categoryName','products','vat'));
	}
	
	public function newOrder(){
		$setting = $this->setting;
		//$this->helpers[] = 'chat.ajaxChat';
		$customerId ='';
		$customerdetail = array();
		if(!empty( $this->request->query)&&array_key_exists('customerId',$this->request->query)){
			$customerId = $this->request->query['customerId'] ;
		}
		if(empty($customerId)){
			$customer_basket = $this->request->Session()->read('session_basket');
			$customerId = $customer_basket['customer']['id'];
		}
		//pr($customerId);die;
		$customerdetail_query = $this->RetailCustomers->find('all',array(
																	'conditions' => array('RetailCustomers.id'=>$customerId),							 
																	 'fields' => array('id','fname','lname','email','mobile','city','country','state','zip','address_1','address_2')
							      ));
		
		$customerdetail_query = $customerdetail_query->hydrate(false);
		if(!empty($customerdetail_query)){
			$customerdetail = $customerdetail_query->toArray();
		}else{
			$customerdetail = array();
		}
		
		// pr($customerdetail);
		$this->set('customerdetail', $customerdetail);
		$vat = $this->VAT;//pass
		$flashMessage = "";
		$currencySymbol = $this->setting['currency_symbol'];
		$this->initialize_customer();
		$this->paginate = [
						'limit' => 20,
						'model' => 'Product',
						'order' => ['product' => 'ASC'],
						'recursive' => -1,
						'conditions' => ['NOT'=>['quantity'=>0]]
				];
		//-----------------------------------------
		$categories_query = $this->Categories->find('all',array(
								'fields' => array('id', 'category','id_name_path'),
                                                                'conditions' => array('Categories.status' => 1),
								'order' => 'Categories.category asc'
								));
		$categories_query = $categories_query->hydrate(false);
        if(!empty($categories_query)){
            $categories = $categories_query->toArray();
        }else{
            $categories = array();
        }
		$categories = $this->CustomOptions->category_options($categories,true);
		
		//-----------------------------------------
		$session_basket = $this->request->Session()->read('Basket');
		//pr($session_basket);
			if(!empty($session_basket)){
				foreach($session_basket as $key => $value){
					if($value["quantity"] == 0){
						
					}
			}
		}
		//$this->session_basket($session_basket);
		//-----------------------------------------
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		$productTable_source = "kiosk_{$kiosk_id}_products";
		$productTable = TableRegistry::get($productTable_source,[
																		'table' => $productTable_source,
																	]);
		
		$products_query = $this->paginate($productTable);
		$products = $products_query->toArray();
		
		$this->set(compact('categories','vat','setting'));
		$this->set('products',$products);
	}
	
	private function initialize_customer(){
		//------------------------------------------------------------------
		$customer_mobile = $customer_email = $customer_zip = $customer_fname = $customer_lname = "";
		$receipt_required = 0;
		$session_basket = $this->request->Session()->read('session_basket');
		$customerId = '';
		$customerdetail = array();
		if(count($this->request->query) && array_key_exists('customerId', $this->request->query) && !empty($this->request->query['customerId'])){
			$customerId = $this->request->query['customerId'] ;
			$customerdetail_query = $this->RetailCustomers->find('all',array(
								'conditions' => array('RetailCustomers.id'=>$customerId),							 
								 'fields' => array('id','fname','lname','email','mobile','city','country','state','zip','address_1','address_2')
							      ));
			$customerdetail_query = $customerdetail_query->hydrate(false);
			if(!empty($customerdetail_query)){
				$customerdetail = $customerdetail_query->first();
			}else{
				$customerdetail = array();
			}
			
		}else{
				$customer_basket = $this->request->Session()->read('customer_basket');
				$customerId = $customer_basket['customer'];
				$customerdetail_query = $this->RetailCustomers->find('all',array(
								'conditions' => array('RetailCustomers.id'=>$customerId),							 
								 'fields' => array('id','fname','lname','email','mobile','city','country','state','zip','address_1','address_2'),
								  'recursive'=>-1,
							      ));
				$customerdetail_query = $customerdetail_query->hydrate(false);
				if(!empty($customerdetail_query)){
					$customerdetail = $customerdetail_query->first();
				}else{
					$customerdetail = array();
				}
				
		}
		
		//set mobile
		if(is_numeric($customerId) && count($customerdetail)){
			$customer_mobile = $customerdetail['mobile'];
			$customer['mobile'] = $customer_mobile;
			//also adding the customer id to the customer array 4th may 2016
			$customer['id'] = $customerId;
			$session_basket['customer'] = $customer;
			$this->request->Session()->write('session_basket',$session_basket);
		}elseif(array_key_exists('customer',$this->request['data']) &&
			array_key_exists('mobile',$this->request['data']['customer'])){
			//post request
			$customer_mobile = $this->request['data']['customer']['mobile'];
			$customer['mobile'] = $customer_mobile;
			$session_basket['customer'] = $customer;
			$this->request->Session()->write('session_basket',$session_basket);
		}elseif(is_array($session_basket) && array_key_exists('customer',$session_basket)){
			//for paging purpose
			$customer = $session_basket['customer'];
			if(is_array($customer) && array_key_exists('mobile',$customer)){
				$customer_mobile = $customer['mobile'];
			}
		}
		$this->set('customer_mobile', $customer_mobile);
		
		//set address 1
		$address_1 = '';
		if(is_numeric($customerId) && count($customerdetail)){
			$address_1 = $customerdetail['address_1'];
			$customer['address_1'] = $address_1;
			$session_basket['customer'] = $customer;
			$this->request->Session()->write('session_basket',$session_basket);
		}elseif(array_key_exists('customer',$this->request['data']) &&
			array_key_exists('address_1',$this->request['data']['customer'])){
			//post request
			$address_1 = $this->request['data']['customer']['address_1'];
			$customer['address_1'] = $address_1;
			$session_basket['customer'] = $customer;
			$this->request->Session()->write('session_basket',$session_basket);
		}elseif(is_array($session_basket) && array_key_exists('customer',$session_basket)){
			//for paging purpose
			$customer = $session_basket['customer'];
			if(is_array($customer) && array_key_exists('address_1',$customer)){
				$address_1 = $customer['address_1'];
			}
		}
		$this->set('address_1', $address_1);
		
		//set address 2
		$address_2 = '';
		if(is_numeric($customerId) && count($customerdetail)){
			$address_2 = $customerdetail['address_2'];
			$customer['address_2'] = $address_2;
			$session_basket['customer'] = $customer;
			$this->request->Session()->write('session_basket',$session_basket);
		}elseif(array_key_exists('customer',$this->request['data']) &&
			array_key_exists('address_2',$this->request['data']['customer'])){
			//post request
			$address_2 = $this->request['data']['customer']['address_2'];
			$customer['address_2'] = $address_2;
			$session_basket['customer'] = $customer;
			$this->request->Session()->write('session_basket',$session_basket);
		}elseif(is_array($session_basket) && array_key_exists('customer',$session_basket)){
			//for paging purpose
			$customer = $session_basket['customer'];
			if(is_array($customer) && array_key_exists('address_2',$customer)){
				$address_2 = $customer['address_2'];
			}
		}
		$this->set('address_2', $address_2);
		
		//set city
		$city = '';
		if(is_numeric($customerId) && count($customerdetail)){
			$city = $customerdetail['city'];
			$customer['city'] = $city;
			$session_basket['customer'] = $customer;
			$this->request->Session()->write('session_basket',$session_basket);
		}elseif(array_key_exists('customer',$this->request['data']) &&
		   array_key_exists('city',$this->request['data']['customer'])){
			//post request
			$city = $this->request['data']['customer']['city'];
			$customer['city'] = $city;
			$session_basket['customer'] = $customer;
			$this->request->Session()->write('session_basket',$session_basket);
		}elseif(is_array($session_basket) && array_key_exists('customer',$session_basket)){
			//for paging purpose
			$customer = $session_basket['customer'];
			if(is_array($customer) && array_key_exists('city',$customer)){
				$city = $customer['city'];
			}
		}
		$this->set('city', $city);
		
		//set state
		$state = '';
		if(is_numeric($customerId) && count($customerdetail)){
			$state = $customerdetail['state'];
			$customer['state'] = $state;
			$session_basket['customer'] = $customer;
			$this->request->Session()->write('session_basket',$session_basket);
		}elseif(array_key_exists('customer',$this->request['data']) &&
		   array_key_exists('state',$this->request['data']['customer'])){
			//post request
			$state = $this->request['data']['customer']['state'];
			$customer['state'] = $state;
			$session_basket['customer'] = $customer;
			$this->request->Session()->write('session_basket',$session_basket);
		}elseif(is_array($session_basket) && array_key_exists('customer',$session_basket)){
			//for paging purpose
			$customer = $session_basket['customer'];
			if(is_array($customer) && array_key_exists('state',$customer)){
				$state = $customer['state'];
			}
		}
		$this->set('state', $state);
		
		//set email
		if(is_numeric($customerId) && count($customerdetail)){
			$customer_email = $customerdetail['email'];
			$customer['email'] = $customer_email;
			$session_basket['customer'] = $customer;
			$this->request->Session()->write('session_basket',$session_basket);
		}elseif(array_key_exists('customer',$this->request['data']) &&
		   array_key_exists('email',$this->request['data']['customer'])){
			//post request
			$customer_email = $this->request['data']['customer']['email'];
			$customer['email'] = $customer_email;
			$session_basket['customer'] = $customer;
			$this->request->Session()->write('session_basket',$session_basket);
		}elseif(is_array($session_basket) && array_key_exists('customer',$session_basket)){
			//for paging purpose
			$customer = $session_basket['customer'];
			if(is_array($customer) && array_key_exists('email',$customer)){
				$customer_email = $customer['email'];
			}
		}
		$this->set('customer_email', $customer_email);
		
		//set zip
		if(is_numeric($customerId) && count($customerdetail)){
			$customer_zip = $customerdetail['zip'];
			$customer['zip'] = $customer_zip;
			$session_basket['customer'] = $customer;
			$this->request->Session()->write('session_basket',$session_basket);
		}elseif(array_key_exists('customer',$this->request['data']) &&
				    array_key_exists('zip',$this->request['data']['customer'])){
			//post request
			$customer_zip = $this->request['data']['customer']['zip'];
			$customer['zip'] = $customer_zip;
			$session_basket['customer'] = $customer;
			$this->request->Session()->write('session_basket',$session_basket);
		}elseif(is_array($session_basket) && array_key_exists('customer',$session_basket)){
			//for paging purpose
			$customer = $session_basket['customer'];
			if(is_array($customer) && array_key_exists('zip',$customer)){
				$customer_zip = $customer['zip'];
			}
		}
		$this->set('customer_zip', $customer_zip);
		
		//set fname
		if(is_numeric($customerId) && count($customerdetail)){
			$customer_fname = $customerdetail['fname'];
			$customer['fname'] = $customer_fname;
			$session_basket['customer'] = $customer;
			$this->request->Session()->write('session_basket',$session_basket);
		}elseif(array_key_exists('customer',$this->request['data']) &&
				    array_key_exists('fname',$this->request['data']['customer'])){
			//post request
			$customer_fname = $this->request['data']['customer']['fname'];
			$customer['fname'] = $customer_fname;
			$session_basket['customer'] = $customer;
			$this->request->Session()->write('session_basket',$session_basket);
		}elseif(is_array($session_basket) && array_key_exists('customer',$session_basket)){
			//for paging purpose
			$customer = $session_basket['customer'];
			if(is_array($customer) && array_key_exists('fname',$customer)){
				$customer_fname = $customer['fname'];
			}
		}
		$this->set('customer_fname', $customer_fname);
		
		//set lname
		if(is_numeric($customerId) && count($customerdetail)){
			$customer_lname = $customerdetail['lname'];
			$customer['lname'] = $customer_lname;
			$session_basket['customer'] = $customer;
			$this->request->Session()->write('session_basket',$session_basket);
		}elseif(array_key_exists('customer',$this->request['data']) &&
				    array_key_exists('lname',$this->request['data']['customer'])){
			//post request
			$customer_lname = $this->request['data']['customer']['lname'];
			$customer['lname'] = $customer_lname;
			$session_basket['customer'] = $customer;
			$this->request->Session()->write('session_basket',$session_basket);
		}elseif(is_array($session_basket) && array_key_exists('customer',$session_basket)){
			//for paging purpose
			$customer = $session_basket['customer'];
			if(is_array($customer) && array_key_exists('lname',$customer)){
				$customer_lname = $customer['lname'];
			}
		}
		$this->set('customer_lname', $customer_lname);
		
		//set receipt option
		if(is_numeric($customerId) && count($customerdetail)){
			$receipt_required = $customer['receipt_required'] = '1';
			$session_basket['customer'] = $customer;
			$this->request->Session()->write('session_basket',$session_basket);
		}elseif(array_key_exists('receipt_required',$this->request['data'])){
			//post request
			$receipt_required = $customer['receipt_required'] = $this->request['data']['receipt_required'];
			$session_basket['customer'] = $customer;
			$this->request->Session()->write('session_basket',$session_basket);
		}elseif(is_array($session_basket) && array_key_exists('customer',$session_basket)){
			//for paging purpose
			$customer = $session_basket['customer'];
			if(is_array($customer) && array_key_exists('receipt_required',$customer)){
				$receipt_required = $customer['receipt_required'];
			}
		}
		$this->set('receipt_required', $receipt_required);
		//------------------------------------------------------------------
	}
	
	public function updateSessionAjax(){
        $CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		if(empty($kiosk_id)){
			echo json_encode(array('error'=>'kiosk id missing'));die;
		}
		$productTable_source = "kiosk_{$kiosk_id}_products";
		if(!empty($this->request->query)){
			$product_id = $this->request->query['prod_id'];
			$quantity = $this->request->query['qty'];
			$dis_status = $this->request->query['dis_status'];
			$product_code = $this->request->query['product_code'];
			$product = $this->request->query['product'];
			$dis_amt = $this->request->query['dis_amt'];
			$net_val = $this->request->query['net_val'];
			$remarks = $this->request->query['remarks'];
			if(array_key_exists('cust_id',$this->request->query)){
				$customer_id  = $this->request->query['cust_id'];
			}else{
				$customer_id  = "";
			}
			
			$selling_price_with_vat = $this->request->query['selling_price'];
			$discount_percentage = $this->request->query['discount_percentage'];
			//$this->Product->setSource($productTable);
			$productTable = TableRegistry::get($productTable_source,[
																		'table' => $productTable_source,
																	]);
			$qty_check_query = $productTable->find('list',
														[
															'keyField' => 'product_code',
															'valueField' => 'quantity',
															'conditions' => ['product_code IN' => $product_code]
														]
												);
			$qty_check_query = $qty_check_query->hydrate(false);
            if(!empty($qty_check_query)){
                $qty_check = $qty_check_query->toArray();
            }else{
                $qty_check = array();
            }
			 if($quantity > $qty_check[$product_code]){
				echo json_encode(array('error' => 'not sufficent quantity'));die;
			 }
			$product_array[$product_code] = array(
												  'quantity' => $quantity,
												  'selling_price' => $selling_price_with_vat,
												  'remarks' => $remarks,
												  'product' => $product,
												  'discount' => $discount_percentage,
												  'discount_status' => $dis_status,
												  'id' => $product_id,
												  );
			
			if(array_key_exists('Basket',$_SESSION)){
				$basket_items = $_SESSION['Basket'];
				if(array_key_exists($product_code,$basket_items)){
					unset($basket_items[$product_code]);
					$basket_items[$product_code] = $product_array[$product_code];
				}else{
					$basket_items[$product_code] = $product_array[$product_code];
				}
				$this->request->Session()->write('Basket',$basket_items);
			}else{
				$this->request->Session()->write('Basket',$product_array);
			}
		}
		
		if(!empty($customer_id)){
			$cust_array = array("customer"=> $customer_id);
			$this->request->Session()->write('session_basket',$cust_array);
		}
		//pr($_SESSION);die;
		$basket = $this->request->Session()->read('Basket');
		if(!empty($basket)){
			$this->SessionRestore->update_session_backup_table($this->request->params['controller'], 'new_order', 'Basket', $basket, $kiosk_id);
			$basketStr = "";$totalBillingAmount = $totalDiscountAmount = $counter = 0;
			$currencySymbol = $this->setting['currency_symbol'];
			//pr($basket);
			foreach($basket as $key => $basketItem){
						$counter++;
						$vat = $this->VAT;
						$vatItem = $vat/100;
						$discount = $basketItem['discount'];				
						$sellingPrice = $basketItem['selling_price'];
						$itemPrice = round($basketItem['selling_price']/(1+$vatItem),2);
						$discountAmount = round($sellingPrice*$basketItem['discount']/100* $basketItem['quantity'],2);
						$totalDiscountAmount+= $discountAmount;
						$totalItemPrice = round($basketItem['selling_price'] * $basketItem['quantity'],2);				
						//$pricebeforeVat = $itemPrice*$basketItem['quantity']-$discountAmount;
						$totalItemCost = round($totalItemPrice-$discountAmount,2);
						$totalBillingAmount+=$totalItemCost;
						$netPrice = round($totalBillingAmount/(1+$vatItem),2);
						$vatperitem = round($basketItem['quantity']*($sellingPrice-$itemPrice),2);
						$vatAmount = round($totalBillingAmount-$netPrice,2);
						/*$productCode[$key] rasu*/
						$basketStr.="<tr>
							<td>{$counter})</td>
							<td>{$key}</td>
							<td>".$basketItem['product']."</td>
							<td>".$basketItem['quantity']."</td>
							<td>".$CURRENCY_TYPE.number_format($sellingPrice,2)."</td>
							<td>".round($discount,2)."</td>
							<td>".$CURRENCY_TYPE.number_format($discountAmount,2)."</td>
							<td>".$CURRENCY_TYPE.number_format($totalItemCost,2)."</td></tr>";
					}
				
				if(!empty($basketStr)){
					$basketStr = "<table><tr><th style='width: 10px;'>Sr No</th><th style='width: 87px;'>Product Code</th><th style='width: 445px;'>Product</th><th style='width: 30px;'>Qty</th><th style='width: 99px;'>Price/Item</th><th style='width: 40px;'>Disct %</th><th style='width: 10px;'>Disct Value</th><th style='width: 10px;'>Gross</th></tr>".$basketStr."<tr><td colspan='7'>Sub Total</td><td>".$CURRENCY_TYPE.number_format($totalBillingAmount,2)."</td></tr><tr><td colspan='7'>Vat (".$vat."%)</td><td>".$CURRENCY_TYPE.number_format($vatAmount,2)."</td></tr><tr><td colspan='7'>Net Amount</td><td>".$CURRENCY_TYPE.number_format($netPrice,2)."</td></tr><tr><td colspan='7'>Total Amount</td><td>".$CURRENCY_TYPE.number_format($totalBillingAmount,2)."</td></tr></table>";
					//$basketStr = "submited";
				}
				$basketStr = trim(str_replace(array("\n", "\r", "\t"), '', $basketStr));
				echo json_encode(array("basket" => $basketStr));
		}else{
			echo json_encode(array('basket' => 'No Items in the basket'));
		}
		//$this->layout = false;
		die;
	}
	
	public function clearSession(){
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		if(array_key_exists('Basket',$_SESSION)){
			$this->SessionRestore->delete_from_session_backup_table($this->request->params['controller'], 'new_order', 'Basket', $kiosk_id);
			unset($_SESSION['Basket']);
		}
		echo json_encode(array('basket' => 'No Items in the basket'));
		//$this->layout = false;
		die;
	}
	
	public function makePaymentAjax(){
		$basket = $this->request->Session()->read('Basket');
		$final_price = 0;
		if(!empty($basket)){
			$kiosk_id = $this->request->Session()->read('kiosk_id');
			$fname = $this->request->query['fname'];
			$lname = $this->request->query['lname'];
			$email = $this->request->query['email'];
			$mobile = $this->request->query['mobile'];
			$zip = $this->request->query['zip'];
			$add1 = $this->request->query['add1'];
			$add2 = $this->request->query['add2'];
			$city = $this->request->query['city'];
			$state = $this->request->query['state'];
			
			$this->loadModel('RetailCustomer');
				$countDuplicate_query = $this->RetailCustomers->find('all', array('conditions' => array('RetailCustomers.email' => $email)));
				$countDuplicate_query = $countDuplicate_query->hydrate(false);
				if(!empty($countDuplicate_query)){
					$countDuplicate = $countDuplicate_query->first();
				}else{
					$countDuplicate  = array();
				}
				
				$customer_data = array(
												'kiosk_id' => $kiosk_id,
												'fname' => $fname,
												'lname' => $lname,
												'mobile' => $mobile,
												'email' => $email,
												'zip' => $zip,
												'address_1' => $add1,
												'address_2' => $add2,
												'city' => $city,
												'state' => $state,
											   );
				
				if(count($countDuplicate) == 0){
						//pr($customer_data);die;
						$RetailCustomersEntity = $this->RetailCustomers->newEntity($customer_data,['validate' => false]);
						$RetailCustomersEntity = $this->RetailCustomers->patchEntity($RetailCustomersEntity,$customer_data,['validate' => false]);
						$this->RetailCustomers->save($RetailCustomersEntity);
				}else{
						$custmor_id =  $countDuplicate["id"];
						$RetailCustomersEntity = $this->RetailCustomers->get($custmor_id);
						
						$RetailCustomersEntity = $this->RetailCustomers->patchEntity($RetailCustomersEntity,$customer_data,['validate' => false]);
						$this->RetailCustomers->save($RetailCustomersEntity);
				}
			
			
			foreach($basket as $key => $value){
				$product_code = $key;
				$qantity = $value['quantity'];
				$selling_price_with_vat = $value['selling_price'];
				$remarks = $value['remarks'];
				$product_name = $value['product'];
				$discount = $value['discount'];
				$discount_status = $value['discount_status']; 
				$product_id = $value['id'];
				
				if($discount > 0 ){
					$dis_value = $selling_price_with_vat * ($discount/100);
					$after_dis_value = $selling_price_with_vat - $dis_value;
					$orignal_price = $after_dis_value * $qantity;
				}else{
					$orignal_price = $selling_price_with_vat*$qantity;
				}
				$final_price += $orignal_price;
			}
			$return_array = array('final_price' => $final_price);
			echo json_encode($return_array);
		}else{
			$return_array = array('error' => 'No Item In Basket');
			echo json_encode($return_array);
		}
		
		//$this->layout = false;
		die;
	}
	
	public function finalStepAjax(){
		$basket_items = array();
		if(array_key_exists('Basket',$_SESSION)){
			$basket_items = $_SESSION['Basket'];
		}else{
			echo  json_encode(array('error' => 'no item in session'));die;
		}
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		if(empty($kiosk_id)){
			echo  json_encode(array('error' => 'no kiosk_id found'));die;
		}
		$customer_id = "";
		if(!empty($this->request->query)){
			$final_amount = $this->request->query['final_amount'];
			if(empty($final_amount)){
				echo  json_encode(array('error' => 'final amout not found'));die;
			}
			$customer_id = $this->request->query['cust_id'];
			$payment_1 = $this->request->query['payment_1'];
			$payment_2 = $this->request->query['payment_2'];
			$method_1 = $this->request->query['method_1'];
			$method_2 = $this->request->query['method_2'];
			$part_time = $this->request->query['part_time'];
			
			
			if($part_time == 1){
				$total = $payment_1 + $payment_2;
				if($total != $final_amount){
					echo  json_encode(array('error' => 'amount is not matching'));die;
				}
			}else{
				if($payment_1 != $final_amount){
					echo  json_encode(array('error' => 'amount is not matching'));die;
				}
			}
			
			$user_id = $this->request->session()->read('Auth.User.id');
            //echo $user_id;die;
			$userTypeData_query = $this->Users->find('all',array(
															'conditions' => array('Users.id' => $user_id),
															'fields' => array('user_type'))
											  );
			$userTypeData_query = $userTypeData_query->hydrate(false);
			if(!empty($userTypeData_query)){
				$userTypeData_query = $userTypeData_query->first();
			}else{
				$userTypeData_query = array();
			}
			if(!empty($userTypeData)){
				$userType = $userTypeData['user_type'];
				if($userType == 'wholesale'){
					$sale_type = 1;
				}else{
					$sale_type = 0;
				}
			}else{
				$sale_type = 0;
			}
			
			$cust_fname = $cust_lname = $cust_mobile = $cust_email = $cust_zip = $cust_address1 = $cust_address2 = $cust_city = $cust_state = "";
			if(!empty($customer_id)){
				$retCustomers_query = $this->RetailCustomers->find('all',array('conditions' => array('LOWER(RetailCustomers.id)'=> "$customer_id")
										  ));
				$retCustomers_query = $retCustomers_query->hydrate(false);
				if(!empty($retCustomers_query)){
					$retCustomers = $retCustomers_query->first();
				}else{
					$retCustomers = array();
				}
				//pr($retCustomers);die;
				if(!empty($retCustomers)){
					$cust_fname =   $retCustomers['fname'];
					$cust_lname = 	$retCustomers['lname'];
					$cust_mobile = 	$retCustomers['mobile'];
					$cust_email = 	$retCustomers['email'];
					$cust_zip = 	$retCustomers['zip'];
					$cust_address1 = 	$retCustomers['address_1'];
					$cust_address2 = 	$retCustomers['address_2'];
					$cust_city = 	$retCustomers['city'];
					$cust_state = 	$retCustomers['state'];
				}
			}
			
			
			$kiosk_id = $this->request->Session()->read('kiosk_id');
			$receiptTable_source = "kiosk_{$kiosk_id}_product_receipts";
		
			$receiptData = array(
									'processed_by' => $user_id,
									'status' => 0,
									'fname' => $cust_fname,
									'lname' => $cust_lname,
									'mobile' => $cust_mobile,
									'email' => $cust_email,
									'zip' => $cust_zip,
									'address_1' => $cust_address1,
									'address_2' => $cust_address2,
									'city' => $cust_city,
									'state' => $cust_state,
									'sale_type' => $sale_type,
									'vat' => $this->VAT
						     );
			//pr($receiptData);die;
			$receiptTable = TableRegistry::get($receiptTable_source,[
																		'table' => $receiptTable_source,
																	]);
			
            $receiptTable->behaviors()->load('Timestamp');
			$recit_entity = $receiptTable->newEntity();
			$recit_entity = $receiptTable->patchEntity($recit_entity,$receiptData);
			//pr($recit_entity);die;
			$receiptTable->save($recit_entity); //saving without amounts
			$product_receipt_id = $recit_entity->id;
			
			if(!empty($product_receipt_id)){
				$payment_status = 1;
				$counter = 0;
				if($part_time == 1){  // payment is divided
					$paymentDetailData = array(
													'kiosk_id' => $this->request->Session()->read('kiosk_id'),
													'user_id' => $user_id,
													'product_receipt_id'=> $product_receipt_id,
													'payment_method' => $method_1,
													'amount' => $payment_1,
													'payment_status' => $payment_status,
													'status' => 1,//this 1 currently does not have any relevance
												);
					
					$product_pay_entity = $this->ProductPayments->newEntity();
					$product_pay_entity = $this->ProductPayments->patchEntity($product_pay_entity,$paymentDetailData);
					if($this->ProductPayments->save($product_pay_entity)){
						$counter++;
					}
					
					$paymentDetailData1 = array(
													'kiosk_id' => $this->request->Session()->read('kiosk_id'),
													'user_id' => $user_id,
													'product_receipt_id'=> $product_receipt_id,
													'payment_method' => $method_2,
													'amount' => $payment_2,
													'payment_status' => $payment_status,
													'status' => 1,//this 1 currently does not have any relevance
												);
					$product_pay_entity1 = $this->ProductPayments->newEntity();
					$product_pay_entity1 = $this->ProductPayments->patchEntity($product_pay_entity1,$paymentDetailData1);
					if($this->ProductPayments->save($product_pay_entity1)){
						$counter++;
					}
					
				}else{ // single payment
					$paymentDetailData = array(
													'kiosk_id' => $this->request->Session()->read('kiosk_id'),
													'user_id' => $user_id,
													'product_receipt_id'=> $product_receipt_id,
													'payment_method' => $method_1,
													'amount' => $payment_1,
													'payment_status' => $payment_status,
													'status' => 1,//this 1 currently does not have any relevance
												);
					$product_pay_entity = $this->ProductPayments->newEntity();
					$product_pay_entity = $this->ProductPayments->patchEntity($product_pay_entity,$paymentDetailData);
					if($this->ProductPayments->save($product_pay_entity)){
						$counter++;
					}
				}
				if($counter > 0){
					$this->save_sale($product_receipt_id,$basket_items,$sale_type,$user_id,$kiosk_id,$final_amount,$customer_id);
				}
			}else{
				echo json_encode(array('error' => 'recit not generated'));die;
			}
		}else{
			echo json_encode(array('error' => 'query is empty'));die;
		}		
	}
	
	public function save_sale($product_receipt_id,$basket_items,$sale_type,$user_id,$kiosk_id,$final_amount,$customer_id){
		$datetime = date('Y-m-d H:i:s');
		$receiptTable_source = "kiosk_{$kiosk_id}_product_receipts";
		$salesTable_source = "kiosk_{$kiosk_id}_product_sales";
		$productTable_source = "kiosk_{$kiosk_id}_products";
		if(!empty($product_receipt_id) && !empty($basket_items)){
			$counter = 0;
			$total_items = count($basket_items);
			foreach($basket_items as $key => $value){
				$vat = $this->VAT;
				$vatItem = $vat/100;
				$dis_status = $value['discount_status'];
				if($dis_status == 1){
					$dis_amt = $value['selling_price'] * ($value['discount']/100);
					$after_dis_val = $value['selling_price'] - $dis_amt;
				}else{
					$after_dis_val = $value['selling_price'];
				}
				
				$without_vat_val = $after_dis_val/(1+$vatItem);
				$without_vat_val_to_submit = $without_vat_val * $value['quantity'];
				$vat_per_item = $after_dis_val - $without_vat_val;
				$vat_per_item_val_to_submit = $vat_per_item * $value['quantity'];
				$data = array(
								'quantity' => $value['quantity'],
								'product_code' => $key,
								'selling_price_withot_vat' => $without_vat_val_to_submit,
								'vat' => $vat_per_item_val_to_submit,
							);
						//pr($data);die;	
				$sale_array = 	array(
									'product_receipt_id' => $product_receipt_id,
									'product_id' => $value['id'],
									'quantity' => $value['quantity'],
									'sale_price' => $value['selling_price'],
									'kiosk_id' => $kiosk_id,
									'sold_by' => $user_id,
									'remarks' => $value['remarks'],
									'created' => $datetime,
									'status' => '1',
									'refund_status' => 0,
									'sale_type' => $sale_type,
									'discount' => $value['discount'],
									'discount_status' => $value['discount_status']
					);
				
				$salesTable = TableRegistry::get($salesTable_source,[
                                                                                    'table' => $salesTable_source,
                                                                                ]);
                
                $salesTable->behaviors()->load('Timestamp');
				$sale_entity = $salesTable->newEntity();
				$sale_entity = $salesTable->patchEntity($sale_entity,$sale_array);
				if($salesTable->save($sale_entity)){
					$this->insert_to_ProductSellStats($value['id'],$data,$kiosk_id,"+");
					$counter++;
					$product_table_data[$key] = $sale_array['quantity'];
				}
			}
			if($total_items == $counter){
				if(!empty($product_table_data)){
					$receiptTable = TableRegistry::get($receiptTable_source,[
                                                                                    'table' => $receiptTable_source,
                                                                                ]);
					$receiptId = $product_receipt_id;
					$data_to_save = array(
										  'bill_amount' => round($final_amount,2),
										  'orig_bill_amount' => round($final_amount,2)
										  );
					
					$receiptTable->behaviors()->load('Timestamp');
					$receiptId_entity = $receiptTable->get($product_receipt_id);
					$receiptId_entity = $receiptTable->patchEntity($receiptId_entity,$data_to_save);
					
					$receiptTable->save($receiptId_entity);
					
					$total_product = count($product_table_data);
					$count = 0;
					//$this->Product->setSource($productTable);
					foreach($product_table_data as $pdtCode => $qty){
						 $query = "UPDATE `$productTable_source` SET `quantity` = `quantity` - $qty WHERE `$productTable_source`.`product_code` = '$pdtCode'";
						//echo "</br>";
						$conn = ConnectionManager::get('default');
						$stmt = $conn->execute($query); 
							$count++;
					}
					//echo $count;
					if($total_product == $count){
						$todayDate = date("Y-m-d");
						$query_1 = "SELECT `id` FROM `$receiptTable_source` WHERE `modified` = '0000-00-00 00:00:00' AND `bill_amount` = 0 AND DATE(`created`) = '$todayDate' order by id desc limit 1";
						$conn = ConnectionManager::get('default');
						$stmt = $conn->execute($query_1); 
						$reciptResult = $stmt ->fetchAll('assoc');
						
						if(is_array($reciptResult) && count($reciptResult) >= 1){
							$orgRecID = $dupReceiptID = $reciptResult[0]['id'];
							$dupReceiptID = $dupReceiptID-1;
							//--------------------------------------------------
							$subject = $body = "Bug for accessory sale for kiosk id: $kiosk_id Receipt- $orgRecID";
							mail('kalyanrajiv@gmail.com', $subject, $body);
							mail('inderjit@mobilebooth.co.uk', $subject, $body);
							//--------------------------------------------------
							$this->Receipt->query("DELETE FROM `$receiptTable` WHERE `id` = '$orgRecID'");
							$alterQuery = "ALTER TABLE `$receiptTable` AUTO_INCREMENT = $dupReceiptID";
							$this->Receipt->query($alterQuery);
							//deleting record from product payments
							$this->ProductPayment->query("DELETE FROM `product_payments` WHERE `product_receipt_id` = $orgRecID AND `kiosk_id` = $kiosk_id");
						}
						$send_by_email = Configure::read('send_by_email');
						if(!empty($customer_id)){
							$retCustomers_query = $this->RetailCustomers->find('list',
																				[
																					'keyField' => 'id',
																					'valueField' => 'email',
																'conditions' => ['LOWER(RetailCustomers.id)'=> "$customer_id"]
																				]);
							$retCustomers_query = $retCustomers_query->hydrate(false);
							if(!empty($retCustomers_query)){
								$retCustomers = $retCustomers_query->toArray();
							}else{
								$retCustomers = array();
							}
							if(!empty($retCustomers)){
								$emailTo = $retCustomers[$customer_id];
							}else{
								$emailTo = "";
							}
							if(!empty($emailTo)){
									$settingArr = $this->setting;
									$options = array(
												'conditions' => array('id'=> $receiptId),
											);
									$kioskDetails_query = $this->Kiosks->find('all',array('conditions'=>array('Kiosks.id'=>$kiosk_id),//'fields'=>array('id','name','address_1','address_2','city','state','zip','contact','country')
																						  ));
									$kioskDetails_query = $kioskDetails_query->hydrate(false);
									if(!empty($kioskDetails_query)){
										$kioskDetails = $kioskDetails_query->first();
									}else{
										$kioskDetails = array();
									}
									$productReceipt_query = $receiptTable->find('all', $options);
									$productReceipt_query = $productReceipt_query->hydrate(false);
									if(!empty($productReceipt_query)){
										$productReceipt = $productReceipt_query->first();  
									}else{
										$productReceipt = array();
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
									
									$res_query =  $salesTable->find('all',array('conditions' => array(
																						'product_receipt_id' => $receiptId
																						)));
									$res_query = $res_query->hydrate(false);
									if(!empty($res_query)){
										$res = $res_query->toArray();
									}else{
										$res = array();
									}
									
									foreach($res as $key => $productDetail){
											$productIdArr[] = $productDetail['product_id'];
									}	
									foreach($productIdArr as $product_id){
										$product_detail_query = $this->Products->find('all', array(
																								'conditions' => array('Products.id' => $product_id),
																								'fields' => array('id','product','product_code'),
																								)
																				);
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
									
									$paymentDetails_query = $this->ProductPayments->find('all',array(
																								'conditions' => array('product_receipt_id' => $receiptId,'status' => 1,
																													  'Date(created) =' => date("Y-m-d",strtotime(date("Y-m-d")))
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
										$payment_method = array();
										foreach($paymentDetails as $key=>$paymentDetail){
											//pr($paymentDetail);
											$payment_method[] = $paymentDetail['payment_method']." ".$settingArr['currency_symbol'].$paymentDetail['amount'];
										}
										$payment_method1 = array();
										foreach($paymentDetails as $key=>$paymentDetail){
											$payment_method1[] = $paymentDetail['payment_method'];
										}
									$emailSender = Configure::read('EMAIL_SENDER');
									$Email = new Email();
									$Email->config('default');
									$Email->viewVars(array('settingArr'=>$settingArr,
														   'productReceipt' => $productReceipt,
														   'user_name' => $user_name,
														   'vat' => $vat,
														   'productCode' => $productCode,
														   'productName' => $productName,
														   'kioskDetails' => $kioskDetails,
														   'res' => $res,
														   'payment_method1' => $payment_method1,
														   )
													 );
									//$Email->config(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
									//$Email = new CakeEmail(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
									//$emailTo = $this->Session->read('session_basket.customer.email');
									$Email->template('receipt');
									$Email->emailFormat('both');
									$Email->to($emailTo);
									$Email->transport(TRANSPORT);
									$Email->from([$send_by_email => $emailSender]);
									//$Email->sender("sales@oceanstead.co.uk");
									$Email->subject('Order Receipt');
									$Email->send();
									if(array_key_exists('Basket',$_SESSION)){
										unset($_SESSION['Basket']);
									}
							}
						}else{
							if(array_key_exists('Basket',$_SESSION)){
								$this->SessionRestore->delete_from_session_backup_table('KioskProductSales', 'new_order', 'Basket', $kiosk_id);
									unset($_SESSION['Basket']);
								if(array_key_exists('session_basket',$_SESSION)){
									unset($_SESSION['session_basket']);
								}
							}
						}
						//pr($_SESSION);
						echo json_encode(array('status' => 'ok','id' => $receiptId,'kiosk_id' => $kiosk_id));
					}
				}
			}
		}else{
			echo  json_encode(array('error' => 'some error on 2nd step'));die;
		}
		//$this->layout = false;
		die;
	}
	
	public function unsetSessionAjax(){
		$CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
		if(!empty($this->request->query)){
			$product_code = $this->request->query['product_code'];
			if(array_key_exists('Basket',$_SESSION)){
				$basket_items = $_SESSION['Basket'];
				if(array_key_exists($product_code,$basket_items)){
					unset($basket_items[$product_code]);
						$this->request->Session()->write('Basket',$basket_items);
				}
			}
		}
		$basket = $this->request->Session()->read('Basket');
		if(!empty($basket)){
			$basketStr = "";$totalBillingAmount = $totalDiscountAmount = $counter = 0;
			$currencySymbol = $this->setting['currency_symbol'];
			foreach($basket as $key => $basketItem){
						$counter++;
						$vat = $this->VAT;
						$vatItem = $vat/100;
						$discount = $basketItem['discount'];				
						$sellingPrice = $basketItem['selling_price'];
						$itemPrice = round($basketItem['selling_price']/(1+$vatItem),2);
						$discountAmount = round($sellingPrice*$basketItem['discount']/100* $basketItem['quantity'],2);
						$totalDiscountAmount+= $discountAmount;
						$totalItemPrice = round($basketItem['selling_price'] * $basketItem['quantity'],2);				
						//$pricebeforeVat = $itemPrice*$basketItem['quantity']-$discountAmount;
						$totalItemCost = round($totalItemPrice-$discountAmount,2);
						$totalBillingAmount+=$totalItemCost;
						$netPrice = round($totalBillingAmount/(1+$vatItem),2);
						$vatperitem = round($basketItem['quantity']*($sellingPrice-$itemPrice),2);
						$vatAmount = round($totalBillingAmount-$netPrice,2);
						/*$productCode[$key] rasu*/
						$basketStr.="<tr>
							<td>{$counter})</td>
							<td>{$key}</td>
							<td>".$basketItem['product']."</td>
							<td>".$basketItem['quantity']."</td>
							<td>".$CURRENCY_TYPE.number_format($sellingPrice,2)."</td>
							<td>".number_format($discount,2)."</td>
							<td>".$CURRENCY_TYPE.number_format($discountAmount,2)."</td>
							<td>".$CURRENCY_TYPE.number_format($totalItemCost,2)."</td></tr>";
					}
				
				if(!empty($basketStr)){
					$basketStr = "<table><tr>
								<th style='width: 10px;'>Sr No</th>
								<th style='width: 87px;'>Product Code</th>
								<th style='width: 445px;'>Product</th>
								<th style='width: 30px;'>Qty</th>
								<th style='width: 99px;'>Price/Item</th>
								<th style='width: 40px;'>Disct %</th>
								<th style='width: 10px;'>Disct Value</th>
								<th style='width: 10px;'>Gross</th>
								</tr>".$basketStr."
								<tr><td colspan='7'>Sub Total</td><td>".$CURRENCY_TYPE.number_format($totalBillingAmount,2)."</td></tr>
								<tr><td colspan='7'>Vat (".$vat."%)</td><td>".$CURRENCY_TYPE.number_format($vatAmount,2)."</td></tr>
								<tr><td colspan='7'>Net Amount</td><td>".$CURRENCY_TYPE.number_format($netPrice,2)."</td></tr>
								<tr><td colspan='7'>Total Amount</td><td>".$CURRENCY_TYPE.number_format($totalBillingAmount,2)."</td></tr>
								</table>";
				}
				$basketStr = trim(str_replace(array("\n", "\r", "\t"), '', $basketStr));
				echo json_encode(array("basket" => $basketStr));
		}else{
				echo json_encode(array('basket' => 'No Items in the basket'));
		}
		//$this->layout = false;
		die;
	}
	private function initialize_tables($kiosk_id){
		//------------------------------------------------------
		$receiptTable = "kiosk_{$kiosk_id}_product_receipts";
		$saleTable = "kiosk_{$kiosk_id}_product_sales";
		$productTable = "kiosk_{$kiosk_id}_products";
		
		$receiptTableQuery = $this->TableDefinition->get_table_defination('product_receipt_table',$kiosk_id);
		$conn = ConnectionManager::get('default');
		$stmt = $conn->execute($receiptTableQuery); 
		
		$kioskProductTableQuery = $this->TableDefinition->get_table_defination('product_sale_table',$kiosk_id);		
		$conn = ConnectionManager::get('default');
		$stmt = $conn->execute($kioskProductTableQuery); 
		
		$tableQuery = $this->TableDefinition->get_table_defination('product_table',$kiosk_id);		
		$conn = ConnectionManager::get('default');
		$stmt = $conn->execute($tableQuery); 
		//------------------------------------------------------
	}
	
	public function restoreSession($currentController = '', $currentAction = '', $session_key = '', $kioskId = '', $redirectAction = ''){
     
		if(!$redirectAction){
           
		    $redirectAction = $currentAction;
		}
		$status = $this->SessionRestore->restore_from_session_backup_table($currentController, $currentAction, $session_key, $kioskId);
		if($currentAction == 'new_sale'){
		    $redirectAction = "new_sale/$redirectAction"; //we are passing customer id in redirect action from view, since passing full action "new_sale/$customer_id" was changing in url to % sign
		}
		
		if($status == 'Success'){
		    $msg = "Session succesfully retreived!";
		}else{
		    $msg = "Session could not be retreived!";
		}
		$this->Flash->error($msg);
		return $this->redirect(array('action' => $redirectAction));
	}
	
     public function restoreSession1($currentController = '', $currentAction = '', $session_key = '', $kiosk_id = '', $redirectAction = ''){
        if(!$redirectAction){
            $redirectAction = $currentAction;
        }
        $status = $this->SessionRestore->restore_from_session_backup_table($currentController, $currentAction, $session_key, $kiosk_id);
        if($currentAction == 'consolidate_faulty' && $status == 'Success'){
            //writing the reference number as well to the session
            $status = $this->SessionRestore->restore_from_session_backup_table($currentController, $currentAction, 'reference', $kiosk_id);
        }
        if($status == 'Success'){
            $msg = "Session succesfully retreived!";
        }else{
            $msg = "Session could not be retreived!";
        }
        $this->Flash->success($msg);
        return $this->redirect(array('action' => $redirectAction));
    }
	public function search($keyword = ""){
		$setting = $this->setting;
		$this->set(compact('setting'));
		$time_start = microtime(true);
		$session_basket = $this->request->Session()->read('Basket');
		$searchKW = '';
		if(array_key_exists('session_basket',$_SESSION)){
			if(array_key_exists('customer',$_SESSION['session_basket'])){
				$fieldArr = array('id','fname','lname','email','mobile','city','country','state','zip','address_1','address_2');
				$customer_id = $_SESSION['session_basket']['customer'];
				if((int)$customer_id){
					$customerdetail_query = $this->RetailCustomers->find('all',array(
																				'conditions' => array('RetailCustomers.id' => $customer_id),
																				'fields' => $fieldArr
																				));
					$customerdetail_query = $customerdetail_query->hydrate(false);
					$customerdetail = $customerdetail_query->first();
					if(!empty($customerdetail)){
						$custid = $customerdetail['id'];
						$customer_fname = $customerdetail['fname'];
						$customer_lname = $customerdetail['lname'];
						$customer_mobile = $customerdetail['mobile'];
						$customer_email = $customerdetail['email'];
						$customer_zip = $customerdetail['zip'];
						$receipt_required = 1;
						$address_1 = $customerdetail['address_1'];
						$address_2 = $customerdetail['address_2'];
						$city = $customerdetail['city'];
						$state = $customerdetail['country'];
						$this->set(compact('custid','customer_fname','customer_lname','customer_mobile','customer_email','customer_zip','receipt_required','address_1','address_2','city','state'));
						//$this->set('customerdetail', $customerdetail);	
					}
					
				}
			}
		}
		
		//$this->initialize_customer();
		if(array_key_exists('search_kw', $this->request->query)){
			$searchKW = $this->request->query['search_kw'];
		}
				
		
		$conditionArr = array();
		//----------------------
		if(!empty($searchKW)){
			$conditionArr['OR']['LOWER(product) like '] =  strtolower("%$searchKW%");
			$conditionArr['OR']['LOWER(product_code) like '] =  strtolower("%$searchKW%");
			$conditionArr['OR']['LOWER(description) like '] =  strtolower("%$searchKW%");
			//'NOT'=>array('Product.quantity'=>0)
		}
		$conditionArr['NOT'] =  array('quantity' => 0);
		
		//----------------------
		if(array_key_exists('category',$this->request->query)){
			$category = $this->request->query['category'];
			if(isset($category)){
				$conditionArr['category_id IN'] =  $category;
			}
		}
		
		
		$this->paginate = [
											'conditions' => $conditionArr,
											'limit' => ROWS_PER_PAGE
						  ];
		
		$categories_query = $this->Categories->find('all',array(
														'fields' => array('id', 'category','id_name_path'),
														'conditions' => array('Categories.status' => 1),
														'order' => 'Categories.category asc'
														)
											);
		$categories_query = $categories_query->hydrate(false);
        if(!empty($categories_query)){
            $categories = $categories_query->toArray();
        }else{
            $categories = array();
        }
		$categories = $this->CustomOptions->category_options($categories,true);
		
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		$productTable_source = "kiosk_{$kiosk_id}_products";
		$productTable = TableRegistry::get($productTable_source,[
																		'table' => $productTable_source,
																	]);
		
		$products_query = $this->paginate($productTable);
		if(!empty($products_query)){
            $products  =$products_query->toArray();
        }
		//pr($this->Product->getDatasource());die;
		//$this->Product->getDatasource()->disconnect();
		$this->set(compact('products','categories'));
		
		//20 CM FLAT MICRO USB SYNC CHARGING V8 CABLE FOR SAMSUNG HTC SONY NOKIA LG LAVA
		if($this->request->query['search_kw'] != ""){
			//echo "fsdafs";
			//pr($products);
			//die("rendered nicely and fast");
		}
		//die("rendered nicely and fast");
		$time_end = microtime(true);
		$execution_time = ($time_end - $time_start)/60;
		$rand = rand(10000,99999);
		//mail('kalyanrajiv@gmail.com', "Script execution time $rand", $execution_time. "for keyword".$this->request->query['search_kw'] );
		
		//$this->layout = "default";//'default'; //order_default
		$this->render('new_order');
		//ConnectionManager::drop('default');
		//$this->Product->find(1);

	}
	
	public function searchEditReceipt($orderId = '',$keyword = ''){
		
		if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
			$kiosk_id_to_set = $this->get_kiosk_for_invoice();
			
			if(!empty($kiosk_id_to_set)){
				if($kiosk_id_to_set == 10000){
					$productSource = "products";
					$productSalesSource = "kiosk_product_sales";
					$recipt_source = "product_receipts";
					$payment_source = "payment_details";
				}else{
					$kiosk_id = $kiosk_id_to_set;
					$productSource = "kiosk_{$kiosk_id}_products";
					$productSalesSource = "kiosk_{$kiosk_id}_product_sales";
					$recipt_source = "kiosk_{$kiosk_id}_product_receipts";
					$payment_source = "kiosk_{$kiosk_id}_payment_details";
				}
				
			}
		}else{
            $kiosk_id = $this->request->Session()->read('kiosk_id');
            $productSource = "kiosk_{$kiosk_id}_products";
					$productSalesSource = "kiosk_{$kiosk_id}_product_sales";
					$recipt_source = "kiosk_{$kiosk_id}_product_receipts";
					$payment_source = "kiosk_{$kiosk_id}_payment_details";
        }
		
		
		//$kiosk_id = $this->request->Session()->read('kiosk_id');
		//if(!empty($kiosk_id)){
		//	$productSource = "kiosk_{$kiosk_id}_products";
		//	$productSalesSource = "kiosk_{$kiosk_id}_product_sales";
		//	$recipt_source = "kiosk_{$kiosk_id}_product_receipts";
		//}else{
		//	$productSource = "products";
		//	$productSalesSource = "kiosk_product_sales";
		//	$recipt_source = "product_receipts";
		//}
		
		$productTable = TableRegistry::get($productSource,[
																	'table' => $productSource,
																]);
		$productSalesTable = TableRegistry::get($productSalesSource,[
																	'table' => $productSalesSource,
																]);
		$recipt_Table = TableRegistry::get($recipt_source,[
																	'table' => $recipt_source,
																]);
		
		$vat = $this->VAT;
		$orderDetails_query = $recipt_Table->find('all',array(
																	'conditions' => array('id' => $orderId)
															)
													);
		$orderDetails_query = $orderDetails_query->hydrate(false);
		if(!empty($orderDetails_query)){
			$orderDetails = $orderDetails_query->first();
		}else{
			$orderDetails = array();
		}
		
		$oldBlkDiscount = $orderDetails['bulk_discount'];
		$customerId = $orderDetails['customer_id'];
		
		$customerAccountDetails_query = $this->Customers->find('all',array(
																'conditions' => array('Customers.id' => $customerId)
														)
													);
		$customerAccountDetails_query = $customerAccountDetails_query->hydrate(false);
		if(!empty($customerAccountDetails_query)){
			$customerAccountDetails = $customerAccountDetails_query->first();
		}else{
			$customerAccountDetails =  array();
		}
		$searchKW = $this->request->query['search_kw'];		
		$categories_query = $this->Categories->find('all',array(
															'fields' => array('id', 'category','id_name_path'),
                                                            'conditions' => array('Categories.status' => 1),
															'order' => 'Categories.category asc'
														)
											);
		$categories_query = $categories_query->hydrate(false);
		if(!empty($categories_query)){
			$categories =  $categories_query->toArray();
		}else{
			$categories = array();
		}
		$categoryList = array();
		foreach($categories as $sngCat){
			$categoryList[$sngCat['id']] = $sngCat['category'];
		}
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
		
		$this->paginate = array(
						'conditions' => $conditionArr,
						'limit' => 50
					);
		$categories = $this->CustomOptions->category_options($categories,true);
        //pr($productTable);die;
		$products_query = $this->paginate($productTable);
		if(!empty($products_query)){
            $products = $products_query->toArray();
        }else{
            $products = array();
        }
		$this->set(compact('products','categories','customerAccountDetails','orderDetails','oldBlkDiscount', 'categoryList', 'vat'));
		//$this->layout = 'default'; 
		//$this->viewPath = 'Products';
		$this->render("edit_receipt");
	}
	
	public function saveUpdatedReceipt($orderId = ''){
		//pr($_SESSION);die;
		//pr($this->request);die;
		if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
			$kiosk_id_to_set = $this->get_kiosk_for_invoice();
			if(!empty($kiosk_id_to_set)){
				if($kiosk_id_to_set == 10000){
					$productSource = "products";
					$productSalesSource = "kiosk_product_sales";
					$recipt_source = "product_receipts";
					$payment_source = "payment_details";
				}else{
					$kiosk_id = $kiosk_id_to_set;
					$productSource = "kiosk_{$kiosk_id}_products";
					$productSalesSource = "kiosk_{$kiosk_id}_product_sales";
					$recipt_source = "kiosk_{$kiosk_id}_product_receipts";
					$payment_source = "kiosk_{$kiosk_id}_payment_details";
				}
				
				$receiptTable = TableRegistry::get($recipt_source,[
															'table' => $recipt_source,
														]);
				$kiosk_product_saleTable = TableRegistry::get($productSalesSource,[
																							'table' => $productSalesSource,
																						]);
				$payment_detailTable = TableRegistry::get($payment_source,[
																			'table' => $payment_source,
																		]);
				$productTable = TableRegistry::get($productSource,[
																'table' => $productSource,
															]);
				
			}
		}else{
            $kiosk_id = $this->request->Session()->read('kiosk_id');
            $productSource = "kiosk_{$kiosk_id}_products";
					$productSalesSource = "kiosk_{$kiosk_id}_product_sales";
					$recipt_source = "kiosk_{$kiosk_id}_product_receipts";
					$payment_source = "kiosk_{$kiosk_id}_payment_details";
                    
                    $receiptTable = TableRegistry::get($recipt_source,[
															'table' => $recipt_source,
														]);
				$kiosk_product_saleTable = TableRegistry::get($productSalesSource,[
																							'table' => $productSalesSource,
																						]);
				$payment_detailTable = TableRegistry::get($payment_source,[
																			'table' => $payment_source,
																		]);
				$productTable = TableRegistry::get($productSource,[
																'table' => $productSource,
															]);
        }
		$vat = $this->VAT;
        $currencySymbol = Configure::read('CURRENCY_TYPE');
		//$currencySymbol = $this->setting['currency_symbol'];
        
		if(array_key_exists('receiptId',$this->request['data'])){
			$orderId = $this->request['data']['receiptId'];
		}
		$orderDetails_query = $receiptTable->find('all',array(
							'conditions'=>array('id' => $orderId)
					)
				);
		$orderDetails_query = $orderDetails_query->hydrate(false);
		if(!empty($orderDetails_query)){
			$orderDetails = $orderDetails_query->first();
		}else{
			$orderDetails = array();
		}
		//pr($orderDetails);die;
		//Start block: cron dashboard code
		$_SESSION['amount_changed'] = $orderDetails['orig_bill_amount'];
		//End block: cron dashboard code
		$originalSaleDate = $orderDetails['created'];
		$customerId = $orderDetails['customer_id'];
		
		$customerAccountDetails_query = $this->Customers->find('all',array(
									'conditions'=>array('Customers.id' => $customerId)
									)
								);
		$customerAccountDetails_query = $customerAccountDetails_query->hydrate(false);
		if(!empty($customerAccountDetails_query)){
			$customerAccountDetails = $customerAccountDetails_query->first();
		}else{
			$customerAccountDetails = array();
		}
		if(!empty($customerAccountDetails)){
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
		}
		//----------Kiosk database tables--------------------
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		if(isset($kiosk_id) && !empty($kiosk_id)){
			$receiptTable = "kiosk_{$kiosk_id}_product_receipts";
			$salesTable = "kiosk_{$kiosk_id}_product_sales";
			$productTable_source = "kiosk_{$kiosk_id}_products";
			$payment_detail = "kiosk_{$kiosk_id}_payment_details";
		}else{
			$kiosk_id = 0;
			$receiptTable = "product_receipts";
			$salesTable = "kiosk_product_sales";
			$productTable_source = "products";
			$payment_detail = "payment_details";
		}
		//----------Kiosk database tables--------------------
		//$this->Product->setSource($productTable);
		
		
		$user_id = $this->request->session()->read("Auth.user.id");	//rasa
		//$this->initialize_tables($kiosk_id);
		$current_page = '';
		if(array_key_exists('current_page',$this->request['data'])){
			$current_page = $this->request['data']['current_page'];		
		}
		//if(empty($current_page)){$this->redirect(array('action' => "edit_receipt/$orderId"));}		
		$productCounts = 0;
		$session_basket = $this->request->Session()->read('Basket');
		
		//--------------------------
		//pr($_SESSION);
		//pr($this->request['data']);die;
		
		if(array_key_exists('basket',$this->request['data'])){
			$productArr = array();
			$bulkDiscountSession = 0;
			$bulkDiscount = 0;
			$vat = $this->VAT;
			if(array_key_exists('bulk_discount',$this->request['data'])){
				$bulkDiscount = $this->request['data']['bulk_discount'];
				$this->request->Session()->write('BulkDiscount', $bulkDiscount);
				$bulkDiscountSession = $this->request->Session()->read('BulkDiscount');
			}
			
			$receipt_required = $this->request['data']['receipt_required'];
			$this->request->Session()->write('receipt_required', $receipt_required);
			$receiptRequiredSession = $this->request->Session()->read('receipt_required');
			//pr($_SESSION);die;
			//pr($this->request);die;
			foreach($this->request['data']['KioskProductSale']['item'] as $key => $item){				
				if((int)$item){
					$discount = $this->request['data']['KioskProductSale']['discount'][$key];					
					$price = $this->request['data']['KioskProductSale']['selling_price'][$key];
					$discountStatus = $this->request['data']['KioskProductSale']['discount_status'][$key];
					$currentQuantity = $this->request['data']['KioskProductSale']['p_quantity'][$key];
					$productID = $this->request['data']['KioskProductSale']['product_id'][$key];
					$productTitle = $this->request['data']['KioskProductSale']['product'][$key];
					$quantity = $this->request['data']['KioskProductSale']['quantity'][$key];
					$netAmount = $this->request['data']['KioskProductSale']['net_amount'][$key];
					//$productCode = $this->request['data']['KioskProductSale']['product_code'][$key]; //rasu
					$priceWithoutVat = $this->request['data']['KioskProductSale']['price_without_vat'][$key];
					/*
					if(array_key_exists('minimum_discount', $this->request['data']['KioskProductSale'])){
						$minPrice = $this->request['data']['KioskProductSale']['minimum_discount'][$key];
					}
					repeated in the bottom same code again
					*/
					if(empty($netAmount)){$netAmount = $priceWithoutVat;}
					if($netAmount >= $priceWithoutVat){
						$price = $netAmount+$netAmount*($vat/100);
						$priceWithoutVat = $netAmount;
					}
					//--------------------------
					$priceCheck_query = $productTable->find('all',array(
																		'conditions' => array('id' => $productID),
																		'fields' => array('selling_price','product')
																	)
														);
					$priceCheck_query = $priceCheck_query->hydrate(false);
					if(!empty($priceCheck_query)){
						$priceCheck = $priceCheck_query->first();
					}else{
						$priceCheck = array();
					}
					
					$originalPrice = $priceCheck['selling_price'];echo "</br>";
				    $discountValue = $originalPrice * $discount/100;echo "</br>";
				    $minPrice = round($originalPrice - $discountValue, 2);echo "</br>";
					//e $minPrice;echo "</br>";die;
					//----------------------------------
					if(array_key_exists('minimum_discount', $this->request['data']['KioskProductSale'])){
						if(array_key_exists($key,$this->request['data']['KioskProductSale']['minimum_discount'])){
							$minPrice = $this->request['data']['KioskProductSale']['minimum_discount'][$key];	
						}
						
					}
					//----------------------------------
					
					if($netAmount != $priceWithoutVat && $netAmount < $minPrice){
						//echo "$netAmount != $priceWithoutVat && $netAmount < $minPrice";die;
						$flashMessage = "Selling price cannot be less than the minimum allowed price[$netAmount != $priceWithoutVat && $netAmount < $minPrice]";
						$this->Flash->error($flashMessage);
						$this->redirect(array('action' => "edit_receipt/$orderId/page:$current_page"));
						die;
					}
				}
				
				
				if((int)$item && $quantity <= $currentQuantity){
					$productArr[$productID] = array(
									'quantity' => $quantity,
									'selling_price' => $price,
									'net_amount' => $netAmount,//new added
									'price_without_vat' => $priceWithoutVat, //new added
									'product' => $productTitle,
									'discount' => $discount,
									'discount_status' => $discountStatus,
									'receipt_required' => $this->request['data']['receipt_required'],
									'bulk_discount' => $bulkDiscount
									);
					$productCounts++;
				}				
			}
			//pr($productArr);
			//pr($session_basket);die;
			// TEMPORARILY COMMENTED $session_old_basket = $this->Session->read('oldBasket');
			$session_basket = $this->request->Session()->read('Basket');
			
			if(count($session_basket) >= 1){
				//adding item to the the existing session
				$sum_total = $this->add_arrays(array($productArr,$session_basket));
				$this->request->Session()->write('Basket', $sum_total);
				$session_basket = $this->request->Session()->read('Basket');
				//pr($session_basket);die;
			}else{
				//adding old basket and the first item to the session
				// TEMPORARILY COMMENTED $productArr = $this->add_arrays(array($productArr,$session_old_basket));
				if(count($productCounts))$this->request->Session()->write('Basket', $productArr);
			}
			//pr($this->request);die;
			$basketStr = "";
			$counter = 0;
			$totalBillingAmount = 0;
			$totalDiscountAmount = 0;
			$vatAmount = 0;
			if(is_array($session_basket)){
				$productCodeArr = array();
				foreach($session_basket as $key => $basketItem){
					if($key == 'error')continue;
					
					$productCode_query = $productTable->find('all',array('conditions'=>array('id'=>$key),
																		  'fields'=>array('id','product_code')));
					$productCode_query = $productCode_query->hydrate(false);
					if(!empty($productCode_query)){
						$productCodeArr[] = $productCode_query->first();
					}else{
						$productCodeArr[] =array();
					}
				}
				$productCode = array();
				if(!empty($productCodeArr)){
					foreach($productCodeArr as $k=>$productCodeData){
						$productCode[$productCodeData['id']]=$productCodeData['product_code'];
					}
				}
				//pr($country);die;
				foreach($session_basket as $key => $basketItem){
					
					$counter++;
					$vatItem = $vat/100;
					$discount = $basketItem['discount'];				
					$sellingPrice = $basketItem['selling_price'];
					$itemPrice = round($basketItem['selling_price']/(1+$vatItem),2);
					$discountAmount = round($sellingPrice*$basketItem['discount']/100* $basketItem['quantity'],2);
					$totalDiscountAmount+= $discountAmount;
					$totalItemPrice = round($basketItem['selling_price'] * $basketItem['quantity'],2);				
					$bulkDiscountPercentage = $bulkDiscountSession;
					$totalItemCost = round($totalItemPrice-$discountAmount,2);
					$totalBillingAmount+=$totalItemCost;
					$vatperitem = round($basketItem['quantity']*($sellingPrice-$itemPrice),2);
					$bulkDiscountValue = round($totalBillingAmount*$bulkDiscountPercentage/100,2);
					$netBillingAmount = round($totalBillingAmount-$bulkDiscountValue,2);
				    $netPrice = round($netBillingAmount/(1+$vatItem),2);
					$vatAmount = round($netBillingAmount-$netPrice,2);
					
					if($country=="OTH"){
						$finalAmount = $netPrice;
					}else{
						$finalAmount = $netBillingAmount;
					}
					//echo $finalAmount;die;
					$basketStr.="<tr>
						<td>{$counter})</td>
						<td>{$productCode[$key]}</td>
						<td>".$basketItem['product']."</td>
						<td>".$basketItem['quantity']."</td>
						<td>".$currencySymbol.number_format($sellingPrice,2)."</td>
						<td> ".$discount."</td>
						<td>".$currencySymbol.number_format($discountAmount,2)."</td>
						<td>".$currencySymbol.number_format($totalItemCost,2)."</td></tr>";
				}
				
			}
			
			if(!empty($basketStr)){
				$basketStr = "<table><tr>
							<th style='width: 10px;'>Sr No</th>
							<th style='width: 132px;'>Product Code</th>
							<th style='width: 445px;'>Product</th>
							<th style='width: 30px;'>Qty</th>
							<th style='width: 99px;'>Price/Item</th>
							<th style='width: 40px;'>Disct %</th>
							<th style='width: 10px;'>Disct Value</th>
							<th style='width: 10px;'>Gross</th>
							</tr>".$basketStr."
							<tr><td colspan='7'>Bulk Discount ({$bulkDiscountPercentage}%)</td><td>".$currencySymbol.number_format($bulkDiscountValue,2)."</td></tr>
							<tr><td colspan='7'>Sub Total</td><td>".$currencySymbol.number_format($netBillingAmount,2)."</td></tr>
							<tr><td colspan='7'>Vat</td><td>".$currencySymbol.number_format($vatAmount,2)."</td></tr>
							<tr><td colspan='7'>Net Amount</td><td>".$currencySymbol.number_format($netPrice,2)."</td></tr>
							<tr><td colspan='7'>Total Amount</td><td>".$currencySymbol.number_format($finalAmount,2)."</td></tr></table>";
			}
		
			$totalItems = count($this->request->Session()->read('Basket'));
			//pr($basketStr);die;
			if($totalItems){
				//$productCounts product(s) added to the cart.
				$flashMessage = "Total item Count:$totalItems.<br/>$basketStr";
			}else{
				$flashMessage = "No item added to the cart. Item Count:$productCounts";
				$this->Flash->error($flashMessage,['escape' => false]);
			}
			
			
			return $this->redirect(array('action' => "edit_receipt/$orderId/page:$current_page"));
		
		}elseif(array_key_exists('empty_basket',$this->request['data'])){
			$this->request->Session()->delete('Basket');
			$this->request->Session()->delete('BulkDiscount');
			$this->request->Session()->delete('receipt_required');
			$flashMessage = "Basket is empty; Add new items to cart!";
			$this->Flash->error($flashMessage);
			return $this->redirect(array('action' => "edit_receipt/$orderId/page:$current_page"));			
		}elseif(array_key_exists('calculate',$this->request['data'])){
			return $this->redirect(array('action' => "edit_receipt/$orderId/page:$current_page"));
		}elseif(array_key_exists('check_out',$this->request['data'])){
			//pr($_SESSION);die;
			return $this->redirect(array('action'=>'edit_receipt_checkout',$orderId));
			
		}else{
			
			$paymentDetail_query = $payment_detailTable->find('all',array(
									'conditions'=>array('product_receipt_id'=>$orderId)
									)
								    );
			$paymentDetail_query = $paymentDetail_query->hydrate(false);
			if(!empty($paymentDetail_query)){
				$paymentDetail = $paymentDetail_query->toArray();
			}else{
				$paymentDetail =array();
			}
			//pr($paymentDetail);die;
			$customer_id = $customerAccountDetails['id'];
			$productArr = array();
			$bulkDiscountSession = 0;
			
			//---------------------Step 1 code -------------------------------
			
			//---------------------Step 2 code -------------------------------
			$saleId = $orderId;
			$session_basket = $this->request->Session()->read('Basket');
			// NORMAL SUBMIT CASE OTHER THAN BASKET
			//pr($this->request['data']);die;
			if(array_key_exists('KioskProductSale',$this->request['data']))
			foreach($this->request['data']['KioskProductSale']['item'] as $key => $item){
				if((int)$item){
					$currentQuantity = $this->request['data']['KioskProductSale']['p_quantity'][$key];
					$productID = $this->request['data']['KioskProductSale']['product_id'][$key];
					$productTitle = $this->request['data']['KioskProductSale']['product'][$key];
					$discount = $this->request['data']['KioskProductSale']['discount'][$key];
					$selling_price = $this->request['data']['KioskProductSale']['selling_price'][$key];
					$discountStatus = $this->request['data']['KioskProductSale']['discount_status'][$key];
					$quantity = $this->request['data']['KioskProductSale']['quantity'][$key];
					$price_without_vat1 = $this->request['data']['KioskProductSale']['price_without_vat'][$key];
					$netAmt =  $this->request['data']['KioskProductSale']['net_amount'][$key];
					$priceWithoutVat = $this->request['data']['KioskProductSale']['price_without_vat'][$key];
					if($netAmt > $price_without_vat1){
						$selling_price = $netAmt+ $netAmt*($vat/100);
						$price_without_vat1 = $netAmt;
					}
					
					if(empty($netAmt)){$netAmt = $priceWithoutVat;}
					if($netAmt >= $priceWithoutVat){
						$selling_price = $netAmt+$netAmt*($vat/100);
						$priceWithoutVat = $netAmt;
					}
				}
				
				$bulkDiscountPercentage = 0;
				$bulkDiscountSession = 0;
				
				if(array_key_exists('bulk_discount',$this->request['data'])){
					$bulkDiscountPercentage = $this->request['data']['bulk_discount'];
					if($bulkDiscountPercentage>100){
						$flashMessage = "Bulk discount percentage must be less than 100";
						$this->Flash->error($flashMessage);
						$this->redirect(array('action' => "edit_receipt/$orderId/page:$current_page"));
						die;
					}elseif($bulkDiscountPercentage<0){
						$flashMessage = "Bulk discount percentage must be a positive number";
						$this->Flash->error($flashMessage);
						$this->redirect(array('action' => "edit_receipt/$orderId/page:$current_page"));
						die;
					}
					
					$this->request->Session()->write('BulkDiscount', $bulkDiscountPercentage);
					$bulkDiscountSession = $this->request->Session()->read('BulkDiscount');
				}
				
				if((int)$item && $quantity <= $currentQuantity){
					$productArr[$productID] = array(
									'quantity' => $quantity,
									'selling_price' => $selling_price,
									'product' => $productTitle,
									'net_amount' => $netAmt,//new added
									'price_without_vat' => $priceWithoutVat, //new added
									'discount' => $discount,
									'discount_status' => $discountStatus,
									'bulk_discount' => $bulkDiscountPercentage,
									'price_without_vat' => $price_without_vat1,
									);
					$productCounts++;
				}				
			}
			
			if(empty($bulkDiscountSession)){
				//pr($_SESSION['BulkDiscount']);die;
				if(array_key_exists('BulkDiscount',$_SESSION)){
					$bulkDiscountSession = $_SESSION['BulkDiscount'];
				}
			}
			
			$sum_total = $this->add_arrays(array($productArr,$session_basket));
			$this->request->Session()->write('Basket', $sum_total);
				$session_basket = $this->request->Session()->read('Basket');
			if(empty($sum_total)){
				$flashMessage = "Failed to create order. <br />Please select quantity atleast for one product!";
				$this->Flash->error($flashMessage);
				$redirectTo = array('action' => "edit_receipt/$orderId/page:$current_page");
				if(!isset($orderId)){$orderId = 0;}
				$this->rollback_sale($orderId, $kiosk_id, 'save_updated_receipt', $redirectTo);
				return $this->redirect($redirectTo);
				die;
			}
			
			$datetime = date('Y-m-d H:i:s');
			
			$billingAmount = 0;
				
			$date = date("d/m/Y", $_SERVER['REQUEST_TIME']);
			$receiptRequired = '';
			
			if(isset($this->request['data']['receipt_required'])){
				$receipt_required = $this->request['data']['receipt_required'];
				$this->request->Session()->write('receipt_required', $receipt_required);
				$receiptRequiredSession = $this->request->Session()->read('receipt_required');	
			}else{
				$receiptRequiredSession = $this->request->Session()->read('receipt_required');
			}
			
			//-----------------------------------------------------------------------------------------
			
			//die;
			$basketStr = "";
			$counter = 0;
			$totalBillingAmount = 0;
			$totalDiscountAmount = 0;
			$vatAmount = 0;
			//pr($sum_total);die;
			if(is_array($sum_total)){
				//pr($sum_total);
				$productCodeArr = array();
				foreach($sum_total as $key => $basketItem){
					if($key == 'error')continue;
					$product_code_query = $productTable->find('all',array('conditions'=>array('id'=>$key),'fields'=>array('id','product_code')));
					$product_code_query = $product_code_query->hydrate(false);
					if(!empty($product_code_query)){
						$productCodeArr[] = $product_code_query->first();
					}else{
						$productCodeArr[] = array();
					}
					
				}
				$productCode = array();
				if(!empty($productCodeArr)){
					foreach($productCodeArr as $k=>$productCodeData){
						$productCode[$productCodeData['id']]=$productCodeData['product_code'];
					}
				}
			//	pr($sum_total);die;
			$sub_total = 0;
				foreach($sum_total as $key => $basketItem){
					$counter++;
					$vat = $this->VAT;
					$vatItem = $vat/100;
					$discount = $basketItem['discount'];				
					$sellingPrice = $basketItem['selling_price'];
					$price_without_vat = $basketItem['price_without_vat'];
					$net_amount = $basketItem['net_amount'];
					$itemPrice = round($basketItem['selling_price']/(1+$vatItem),2);
					//$discountAmount = round($sellingPrice*$basketItem['discount']/100* $basketItem['quantity'],2);
					$discountAmount = $price_without_vat * $basketItem['discount']/100 * $basketItem['quantity'];
					$totalDiscountAmount+= $discountAmount;
					//$totalItemPrice = round($basketItem['selling_price'] * $basketItem['quantity'],2);
					$totalItemPrice = $price_without_vat * $basketItem['quantity'];
					$bulkDiscountPercentage = $bulkDiscountSession;
					$totalItemCost = round($totalItemPrice-$discountAmount,2);
					$totalBillingAmount+=$totalItemCost;
					$vatperitem = round($basketItem['quantity']*($sellingPrice-$itemPrice),2);
					$bulkDiscountValue = round($totalBillingAmount*$bulkDiscountPercentage/100,2);
					$netBillingAmount = round($totalBillingAmount-$bulkDiscountValue,2);
					//$netPrice = round($netBillingAmount/(1+$vatItem),2);
					$netPrice = $netBillingAmount;
					//$vatAmount = round($netBillingAmount-$netPrice,2);
					$vatAmount = $netBillingAmount*$vatItem;
					$finalAmount = $netBillingAmount;
					if($country=="OTH"){
						$finalAmount = $netPrice;
					}else{
						$finalAmount = $netBillingAmount+$vatAmount;
					}
					
					$sub_total = $sub_total + $totalItemCost;
					
					$CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
					$basketStr.="<tr>
						<td>{$counter})</td>
						<td>{$productCode[$key]}</td>
						<td>".$basketItem['product']."</td>
						<td>".$basketItem['quantity']."</td>
						<td>".$CURRENCY_TYPE.number_format($price_without_vat,2)."</td>
						<td> ".$discount."</td>
						<td>".$CURRENCY_TYPE.number_format($net_amount,2)."</td>
						<td>".$CURRENCY_TYPE.number_format($totalItemCost,2)."</td></tr>";
				}
			}
			if(!empty($basketStr)){
				$basketStr = "<table><tr>
							<th style='width: 10px;'>Sr No</th>
							<th style='width: 132px;'>Product Code</th>
							<th style='width: 445px;'>Product</th>
							<th style='width: 30px;'>Qty</th>
							<th style='width: 99px;'>Sale Price</th>
							<th style='width: 40px;'>Disct %</th>
							<th style='width: 10px;'>Disct Value</th>
							<th style='width: 10px;'>Amount</th>
							</tr>".$basketStr."
							<tr><td colspan='7'>Sub Total</td><td>".$CURRENCY_TYPE.number_format($sub_total,2)."</td></tr>
							<tr><td colspan='7'>Bulk Discount ({$bulkDiscountPercentage}%)</td><td>".$CURRENCY_TYPE.number_format($bulkDiscountValue,2)."</td></tr>
							<tr><td colspan='7'>Sub Total(after bulk discount)</td><td>".$CURRENCY_TYPE.number_format($netBillingAmount,2)."</td></tr>
							<tr><td colspan='7'>Vat</td><td>".$CURRENCY_TYPE.number_format($vatAmount,2)."</td></tr>
							<tr><td colspan='7'>Net Amount</td><td>".$CURRENCY_TYPE.number_format($netPrice,2)."</td></tr>
							<tr><td colspan='7'>Total Amount</td><td>".$CURRENCY_TYPE.number_format($finalAmount,2)."</td></tr></table>";
					
			}
			$totalItems = count($this->request->Session()->read('Basket'));
			//-------------------------------------------------------------
			
			$this->request->Session()->write('finalAmount', $finalAmount);
			$flashMessage = "Please review the details and make payment<br/>$basketStr";
			$this->Flash->error($flashMessage,['escape' => false]);
			if($orderDetails['bulk_discount'] != $bulkDiscountPercentage){
				return $this->redirect(array('controller'=>'kiosk_product_sales','action' => "adjust_payment", $orderId));
			}else{
				return $this->redirect(array('controller'=>'product_receipts','action' => "make_payment", $orderId));
			}
			//return $this->redirect(array('controller'=>'product_receipts','action' => "make_payment", $orderId));
		}
	}
	
	public function adjustPayment($orderId){
		if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
			$kiosk_id_to_set = $this->get_kiosk_for_invoice();
			if(!empty($kiosk_id_to_set)){
				if($kiosk_id_to_set == 10000){
					$productSource = "products";
					$productSalesSource = "kiosk_product_sales";
					$recipt_source = "product_receipts";
					$payment_source = "payment_details";
				}else{
					$kiosk_id = $kiosk_id_to_set;
					$productSource = "kiosk_{$kiosk_id}_products";
					$productSalesSource = "kiosk_{$kiosk_id}_product_sales";
					$recipt_source = "kiosk_{$kiosk_id}_product_receipts";
					$payment_source = "kiosk_{$kiosk_id}_payment_details";
				}
				
				$receiptTable = TableRegistry::get($recipt_source,[
																		'table' => $recipt_source,
																	]);
			
				$kioskProdctSalesTable = TableRegistry::get($productSalesSource,[
																			'table' => $productSalesSource,
																		]);
				
				$paymentTable = TableRegistry::get($payment_source,[
																			'table' => $payment_source,
																		]);
				$productsTable = TableRegistry::get($productSource,[
																		'table' => $productSource,
																	]);
				
			}
		}else{
            $kiosk_id = $this->request->Session()->read('kiosk_id');
					$productSource = "kiosk_{$kiosk_id}_products";
					$productSalesSource = "kiosk_{$kiosk_id}_product_sales";
					$recipt_source = "kiosk_{$kiosk_id}_product_receipts";
					$payment_source = "kiosk_{$kiosk_id}_payment_details";
                    
                    $receiptTable = TableRegistry::get($recipt_source,[
																		'table' => $recipt_source,
																	]);
			
				$kioskProdctSalesTable = TableRegistry::get($productSalesSource,[
																			'table' => $productSalesSource,
																		]);
				
				$paymentTable = TableRegistry::get($payment_source,[
																			'table' => $payment_source,
																		]);
				$productsTable = TableRegistry::get($productSource,[
																		'table' => $productSource,
																	]);
        }
		$orderDetails_query = $receiptTable->find('all',array(
							'conditions'=>array('id' => $orderId)
					)
				);
		$orderDetails_query = $orderDetails_query->hydrate(false);
		//pr($orderDetails_query);die;
		if(!empty($orderDetails_query)){
			$orderDetails = $orderDetails_query->first();
		}else{
			$orderDetails = array();
		}
		//pr($orderDetails);die;
		if(!empty($orderDetails)){
			$id = $orderDetails['id'];
			$kioskTableData_query = $kioskProdctSalesTable->find('all',['conditions' => ['product_receipt_id'=>$id]]);
			$kioskTableData_query = $kioskTableData_query->hydrate(false);
			if(!empty($kioskTableData_query)){
				$kioskTableData = $kioskTableData_query->toArray();
			}else{
				$kioskTableData = array();
			}
			//pr($kioskTableData);die;
			$paymentTableData_query = $paymentTable->find('all',['conditions' => ['product_receipt_id'=>$id]]);
			$paymentTableData_query = $paymentTableData_query->hydrate(false);
			if(!empty($paymentTableData_query)){
				$paymentTableData = $paymentTableData_query->toArray();
			}else{
				$paymentTableData = array();
			}
			//pr($paymentTableData);die;
		}else{
			$kioskTableData = array();
			$paymentTableData = array();
		}
		$this->set(compact('kioskTableData','paymentTableData'));
		//pr($orderDetails);die;
		$bulkDiscount = $this->request->Session()->read('BulkDiscount');
		//pr($orderDetails);die;
		if(!empty($orderDetails)){
			$total_value = 0;
			foreach($kioskTableData as $key => $value){
				if(!empty($value['discount'])){
					$each_item_value = $value['sale_price'] - $value['sale_price'] * ($value['discount']/100);
					$item_value = $each_item_value * $value['quantity'];
				}else{
					$each_item_value = $value['sale_price'];
					$item_value = $each_item_value * $value['quantity'];
				}
				$total_value += $item_value;
			}
			//echo $total_value;die;
		}
		$final_amout = $total_value - $total_value * ($bulkDiscount/100);
		if(!empty($orderDetails['vat'])){
			$final_amout = $final_amout + ($final_amout*$orderDetails['vat']/100);
		}
		$final_price = round($final_amout,2);
		if($this->request->is('post')){
			//pr($this->request);die;
			$amount = $this->request->data['sale_amount'];
			$amoubt_arr = $this->request->data['old_amt'];
			$check_amt = 0;
			foreach($amoubt_arr as $key => $value){
				$check_amt+= $value;
			}
			$check_amt = round($check_amt,2);
			$amount = round($amount,2);
			
			if((float)$check_amt !== (float)$amount){
				$flashMessage = "amount is not matching";
				$this->Flash->error($flashMessage);
				return $this->redirect(array('controller'=>'kiosk_product_sales','action' => "adjust_payment", $orderId));
			}
			$count = 0;
			foreach($paymentTableData as $key => $paymentData){
				$new_pay_data = array(
										'id' => $paymentData['id'],
										'amount' => $amoubt_arr[$key],
										'modified' => $paymentData['modified']
										);
				$entity = $paymentTable->get($paymentData['id']);
				$entity = $paymentTable->patchEntity($entity,$new_pay_data);
				if($paymentTable->save($entity)){
					$count++;	
				}	
			}
			if($count >0){
				$dataArr = array('bill_amount'=>$amount,'orig_bill_amount'=>$amount);
				$Entity = $receiptTable->get($orderId);
				$Entity = $receiptTable->patchEntity($Entity,$dataArr);
				$receiptTable->save($Entity);
				//$this->ProductReceipt->saveField('orig_bill_amount',$amount);
				return $this->redirect(array('controller'=>'product_receipts','action' => "make_payment", $orderId));
			}
			
		}
		$this->set(compact('final_price','orderDetails'));
	}
	
	public function editReceiptCheckout($orderId = ""){
		
		
		if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
			$kiosk_id_to_set = $this->get_kiosk_for_invoice();
			
			if(!empty($kiosk_id_to_set)){
				if($kiosk_id_to_set == 10000){
					$productSource = "products";
					$productSalesSource = "kiosk_product_sales";
					$recipt_source = "product_receipts";
					$payment_source = "payment_details";
				}else{
					$kiosk_id = $kiosk_id_to_set;
					$productSource = "kiosk_{$kiosk_id}_products";
					$productSalesSource = "kiosk_{$kiosk_id}_product_sales";
					$recipt_source = "kiosk_{$kiosk_id}_product_receipts";
					$payment_source = "kiosk_{$kiosk_id}_payment_details";
				}
				
			}
		}else{
            $kiosk_id = $this->request->Session()->read('kiosk_id');
            $productSource = "kiosk_{$kiosk_id}_products";
					$productSalesSource = "kiosk_{$kiosk_id}_product_sales";
					$recipt_source = "kiosk_{$kiosk_id}_product_receipts";
					$payment_source = "kiosk_{$kiosk_id}_payment_details";
        }
		
		//$kiosk_id = $this->request->Session()->read('kiosk_id');
		//if(!empty($kiosk_id)){
		//	$productSource = "kiosk_{$kiosk_id}_products";
		//	$productSalesSource = "kiosk_{$kiosk_id}_product_sales";
		//	$recipt_source = "kiosk_{$kiosk_id}_product_receipts";
		//}else{
		//	$productSource = "products";
		//	$productSalesSource = "kiosk_product_sales";
		//	$recipt_source = "product_receipts";
		//}
		
		$productTable = TableRegistry::get($productSource,[
																	'table' => $productSource,
																]);
		$productSalesTable = TableRegistry::get($productSalesSource,[
																	'table' => $productSalesSource,
																]);
		$recipt_Table = TableRegistry::get($recipt_source,[
																	'table' => $recipt_source,
																]);
		//pr($_SESSION);die;
		$session_basket = $this->request->Session()->read('Basket');
		$productCodeArr = array();
		$productCode = array();
		if(!empty($session_basket)){
			$product_ids = array_keys($session_basket);
			$productCodeArr_query = $productTable->find('all',array('conditions'=>array('id IN'=>$product_ids),'fields'=>array('id','product_code','quantity')));
			$productCodeArr_query = $productCodeArr_query->hydrate(false);
			if(!empty($productCodeArr_query)){
				$productCodeArr = $productCodeArr_query->toArray();
			}else{
				$productCodeArr = array();
			}
			foreach($session_basket as $key => $basketItem){
			if($key == 'error')continue;
			//$productCodeArr[] = $this->Product->find('first',array('conditions'=>array('Product.id'=>$key),'fields'=>array('id','product_code'),'recursive'=>-1));
			}
//			pr($productCodeArr);
			if(!empty($productCodeArr)){
			 	foreach($productCodeArr as $k=>$productCodeData){
					  $productIds[$productCodeData['id']] = $productCodeData['product_code'];
					  $productCodes[$productCodeData['product_code']] = $productCodeData['quantity'];
					}
			}
		}
		$orderDetails_query = $recipt_Table->find('all',array(
							'conditions'=>array('id' => $orderId)
					)
				);
		$orderDetails_query = $orderDetails_query->hydrate(false);
		if(!empty($orderDetails_query)){
			$orderDetails = $orderDetails_query->first();
		}else{
			$orderDetails = array();
		}
        
		$customerId = $orderDetails['customer_id'];
		$customerAccountDetails_query = $this->Customers->find('all',array(
									'conditions'=>array('Customers.id' => $customerId)
									)
								);
		$customerAccountDetails_query = $customerAccountDetails_query->hydrate(false);
		if(!empty($customerAccountDetails_query)){
			$customerAccountDetails = $customerAccountDetails_query->first();
		}else{
			$customerAccountDetails = array();
		}
		$country = $customerAccountDetails['country'];
		$currencySymbol = $this->setting['currency_symbol'];
 		$vat = $this->VAT;
		
		if($this->request->is('post')){
			///pr($this->request);die;
			$error = array();
			if(array_key_exists('update_quantity',$this->request->data)){
				$lessProducts = array();
				$lowProducts = array();
				foreach($this->request->data['CheckOut'] as $productCode => $quantity){
					$availableQty = $productCodes[$productCode];
					if($quantity == 0 || !(int)$quantity){
							$lowProducts[] = $productCode;
					}
					if($quantity > $availableQty){
						$lessProducts[] = $productCode;
					}	
				}
				
				if(count($lessProducts) >= 1){
					$this->Flash->error("Please choose ".implode(",",$lessProducts)." quantity less than or equal to available stock",['validate' => false]);
					return $this->redirect(array('action'=>'edit_receipt_checkout',$orderId));
				}
				
				if(count($lowProducts) > 0){
					$this->Flash->error("Please choose  more than 0 for product : ".implode(",",$lowProducts),['validate' => false]);
					return $this->redirect(array('action'=>'edit_receipt_checkout',$orderId));
				}else{
					$requestedQuantity = $this->request->data['CheckOut'];
					$newArray = array();
					$counter = 0;
					$requestedQuantity = array_values($requestedQuantity);//die;
					//pr($session_basket);die;
					foreach($session_basket as $productCode => $productData){
						$qty = "";
						if(array_key_exists($counter,$requestedQuantity)){
							 $qty =  $requestedQuantity[$counter];
						}
						if(empty($productData['remarks'])){
							$productData['remarks'] = "";
						}
						$newArray[$productCode] = array(
							'quantity' =>  $qty   ,
							'selling_price' => $productData['selling_price'],
							//'remarks' => $productData['remarks'],
							'price_without_vat' => $productData['price_without_vat'],
							'net_amount' => $productData['net_amount'],
							'product'  => $productData['product'] ,
							'discount'  => $productData['discount'] ,
							'discount_status'  => $productData['discount_status'] ,
							'receipt_required'  => $productData['receipt_required'] ,
							'bulk_discount'  => $productData['bulk_discount'] 
													);
						$counter++;
					}
					$this->request->Session()->delete('Basket');
					$this->request->Session()->write('Basket',$newArray);
					$this->Flash->success("Quantity has been  successfully updated");
					return $this->redirect(array('action'=>'edit_receipt_checkout',$orderId));
				}
			}elseif(array_key_exists('edit_basket',$this->request->data)){
				return $this->redirect(array('action'=>"edit_receipt",$orderId));
			}
		}
		$this->set(compact('vat','country','currencySymbol','orderId','productCode','productCodeArr','productIds'));
	}
	
	public function delete_product_from_session2($product_id="",$orderId = ""){
		unset($_SESSION['Basket'][$product_id]);
		if(true){ //$this->request->Session()->delete("Basket.$product_id")
			$session_basket = $this->request->Session()->read('Basket');
			$kiosk_id = $this->request->Session()->read('kiosk_id');
			return $this->redirect(array('action'=>'edit_receipt_checkout',$orderId));
		}
	}
	
	private function rollback_sale($receiptID, $kiosk_id, $functionName, $redirectTo){
		//Failed to create order. <br />Please select quantity atleast for one product!
		//----------Kiosk database tables--------------------
		if(!empty($kiosk_id)){
			//wholesale retailer
			$receiptTable = "kiosk_{$kiosk_id}_product_receipts";
			$salesTable = "kiosk_{$kiosk_id}_product_sales";
			$productTable = "kiosk_{$kiosk_id}_products";
		}else{
			//admin
			$receiptTable = "product_receipts";
			$salesTable = "kiosk_product_sales";
			$productTable = "products";
		}
		//$this->Receipt->setSource($receiptTable);
		//$this->KioskProductSale->setSource($salesTable);
		//----------Kiosk database tables--------------------
		//Start:
		$todayDate = date("Y-m-d");
		
		if(!empty($receiptID)){
			$reciptResult_query = "SELECT `id` FROM `$receiptTable` WHERE `id` =  $receiptID AND `bill_amount` = 0 AND DATE(`created`) = '$todayDate' order by id desc limit 1";
		}else{
			$reciptResult_query = "SELECT `id` FROM `$receiptTable` WHERE `modified` = '0000-00-00 00:00:00' AND `bill_amount` = 0 AND DATE(`created`) = '$todayDate' order by id desc limit 1";
			//Deleting from warehouse receipts table
		}
		$conn = ConnectionManager::get('default');
		$stmt = $conn->execute($reciptResult_query); 
		$reciptResult = $stmt ->fetchAll('assoc');
		
		if(is_array($reciptResult) && count($reciptResult) >= 1){
			$orgRecID = $dupReceiptID = $reciptResult[0]['id'];
			$dupReceiptID = $dupReceiptID-1;
			if($orgRecID){
				//--------------------------------------------------
				$subject = $body = "Bug for accessory sale for kiosk id: $kiosk_id Receipt- $orgRecID [$functionName]";
				mail('kalyanrajiv@gmail.com', $subject, $body);
				mail('inderjit@mobilebooth.co.uk', $subject, $body);
				//--------------------------------------------------
				$query = "DELETE FROM `$receiptTable` WHERE `id` = '$orgRecID'";
				$conn = ConnectionManager::get('default');
				$stmt = $conn->execute($query); 
				//1 Deleting from Receipt Table
				$alterQuery = "ALTER TABLE `$receiptTable` AUTO_INCREMENT = $dupReceiptID";
				$conn = ConnectionManager::get('default');
				$stmt = $conn->execute($alterQuery); 
				
				//deleting record from product payments
				if($kiosk_id){
					//$this->ProductPayment->setSource('product_payments');
					$ProductPayment_query = "DELETE FROM `product_payments` WHERE `product_receipt_id` = $orgRecID AND `kiosk_id` = $kiosk_id";
					$conn = ConnectionManager::get('default');
					$stmt = $conn->execute($ProductPayment_query); 
					//2 Deleting from Product Payments Table
				}else{
					//deleting from warehouse payment details table
					//$this->ProductPayment->setSource('payment_details');
					$PaymentDetail_query = "DELETE FROM `payment_details` WHERE `product_receipt_id` = $orgRecID";
					$conn = ConnectionManager::get('default');
					$stmt = $conn->execute($PaymentDetail_query); 
				}
				$KioskProductSale_query = "DELETE FROM `$salesTable` WHERE `product_receipt_id` = '$orgRecID'";
				$conn = ConnectionManager::get('default');
				$stmt = $conn->execute($KioskProductSale_query); 
				//3 Deleting from sales Table
			}
		}
		return $this->redirect($redirectTo);
		//End:
	}
	
	public function saveInvoiceEditDetail($saleId = ''){
		$CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');        
		$vat = $this->VAT;
		$settingArr = $this->setting;
		$receiptRequiredSession = $this->request->Session()->read('receipt_required');
		$finalAmount = $this->request->Session()->read('finalAmount');
		$finalAmount = round($finalAmount,2);
		$newBasket = $this->request->Session()->read('Basket');
		$bulkDiscount = 0;
		$bulkDiscount = $this->request->Session()->read('BulkDiscount');
		$amount = 0;
		$amount = $finalAmount;
		//echo $amount; die;
        
		
		$sum_total = $this->request->Session()->read('Basket');
		$counter = 0;
		
		$kisk_id = $this->request->Session()->read('kiosk_id');
		$kiosk_id = 0;
		if((int)$kisk_id){
			$kiosk_id = $this->request->Session()->read('kiosk_id');
		}
		
		if(!empty($kiosk_id)){
			$salesTable = "kiosk_{$kiosk_id}_product_sales";
			$productsTable = "kiosk_{$kiosk_id}_products";
			$productreceiptsTable = "kiosk_{$kiosk_id}_product_receipts";
            $payment_source = "kiosk_{$kiosk_id}_payment_details";
		}else{
			$salesTable = "kiosk_product_sales";
			$productsTable = "products";
			$productreceiptsTable = "product_receipts";
			
			$kiosk_id_to_set = $this->get_kiosk_for_invoice();
			if(!empty($kiosk_id_to_set)){
				if($kiosk_id_to_set == 10000){
					$productsTable = "products";
					$salesTable = "kiosk_product_sales";
					$productreceiptsTable = "product_receipts";
					$payment_source = "payment_details";
				}else{
					$kiosk_id = $kiosk_id_to_set;
					$productsTable = "kiosk_{$kiosk_id}_products";
					$salesTable = "kiosk_{$kiosk_id}_product_sales";
					$productreceiptsTable = "kiosk_{$kiosk_id}_product_receipts";
					$payment_source = "kiosk_{$kiosk_id}_payment_details";
				}
			}
		}
        $ProductTable = TableRegistry::get($productsTable,[
                                                                'table' => $productsTable,
                                                            ]);
			
				$kioskProdctSalesTable = TableRegistry::get($salesTable,[
																				'table' => $salesTable,
																			]);
					
				$PaymentDetailTable = TableRegistry::get($payment_source,[
																				'table' => $payment_source,
																			]);
				$ProductReceiptTable = TableRegistry::get($productreceiptsTable,[
																		'table' => $productreceiptsTable,
																	]);
                
                
                $orderDetails_query = $ProductReceiptTable->find('all',array(
							'conditions'=>array('id'=>$saleId)
					)
				);
                $orderDetails_query = $orderDetails_query->hydrate(false);
                if(!empty($orderDetails_query)){
                    $orderDetails = $orderDetails_query->first();
                }else{
                    $orderDetails = array();
                }
                $originalSaleDate = $orderDetails['created'];
                $saleVat = $orderDetails['vat'];
                
                
		//if(!empty($kiosk_id)){
		//	$salesTable = "kiosk_{$kiosk_id}_product_sales";
		//	$productsTable = "kiosk_{$kiosk_id}_products";
		//	$productreceiptsTable = "kiosk_{$kiosk_id}_product_receipts";
		//}else{
		//	$salesTable = "kiosk_product_sales";
		//	$productsTable = "products";
		//	$productreceiptsTable = "product_receipts";
		//}
		
		$saleData_query = $kioskProdctSalesTable->find('all',array(
							'conditions'=>array('product_receipt_id'=>$saleId)
					)
				);
		//pr($saleData_query);die;
		$saleData_query = $saleData_query->hydrate(false);
		if(!empty($saleData_query)){
			$saleData = $saleData_query->toArray();
		}else{
			$saleData = array();
		}
		//pr($saleData);
		$orignal_price = 0;
		if(!empty($saleData)){
			foreach($saleData as $key => $value){
					 $sale_price = $value["sale_price"];
					 $discount = $value["discount"];
					 $quantity = $value["quantity"];
				 $orignal_price += $quantity * ($sale_price - ($sale_price*($discount/100)));
			}
		}
		$vat = $this->VAT;
		$discount = $orignal_price * ($bulkDiscount/100);
		$netprice = $orignal_price - $discount;
		if($saleVat > 0){
			$netprice = $netprice + ($netprice * ($vat/100));
		}
       // echo $netprice;die;
		$amount = $amount+$netprice;
		
		
		$totalCost = 0; // added by sourabh
		//$this->Product->setSource("products");//because cost price of product can be fetched only from warehouse products table
		$product_code = $this->Products->find("list",[
									  "keyField" => "id",
									  "valueField" => "product_code"
									  ])->toArray();
		
		foreach($sum_total as $productID => $productData){
			$vat_value = 0;
			if($productID == 'error')continue;
			$quantity = $productData['quantity'];
			$discount = $productData['discount'];
			
			//$this->ProductReceipt->clear();
			$costPrice_query = $ProductTable->find('list', array('conditions' => array('id' => $productID),
																   'keyField' => 'id',
																   'valueField' => 'cost_price',
																   ));// added by sourabh
			$costPrice_query = $costPrice_query->hydrate(false);
			if(!empty($costPrice_query)){
				$costPrice = $costPrice_query->toArray();
			}else{
				$costPrice = array();
			}
			//------added by rajiv case: when price_without_vat is not received
			if(!array_key_exists('price_without_vat', $productData)){
				$sellingPrice_query = $ProductTable->find('list', array('conditions' => array('id' => $productID),
																	'keyField' => 'id',
																	'valueField' => 'selling_price',
																	));// added by sourabh
				$sellingPrice_query = $sellingPrice_query->hydrate(false);
				if(!empty($sellingPrice_query)){
					$sellingPrice = $sellingPrice_query->toArray();
				}else{
					$sellingPrice = array();
				}
				$numerator = $sellingPrice[$productID] * 100;
				$denominator = $vat+100;
				$priceWithoutVat = $numerator/$denominator;
				$priceWithoutVat = number_format($priceWithoutVat,2);
				$vat_value=  $sellingPrice[$productID] - $priceWithoutVat;
			}else{
				$priceWithoutVat = $productData['price_without_vat'];
				$vat_value=  $priceWithoutVat*$vat/100;
			}
			
			//-----------------------------------------------------------------
				$user_id = $this->request->Session()->read('Auth.User.id');
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
					if($this->request->Session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->Session()->read('Auth.User.group_id') == MANAGERS //|| $this->request->session()->read('Auth.User.group_id') == SALESMAN
					   ){
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
					if($this->request->Session()->read('Auth.User.group_id') == ADMINISTRATORS && $this->request->Session()->read('Auth.User.group_id') == MANAGERS //|| $this->request->session()->read('Auth.User.group_id') == SALESMAN
					   ){
						$sale_type = 1;
					}else{
						$sale_type = 0;
					}
					
				}
			
				$kioskProductSaleData = array(
												'kiosk_id' => $kiosk_id,
												'product_receipt_id' => $saleId,
												'sale_price' => $priceWithoutVat,
												'quantity' => $quantity,
												'product_id' => $productID,
												'discount' => $discount,
												'sale_type' => $sale_type,
												'created' => $originalSaleDate,
												'sold_by' => $this->request->Session()->read('Auth.User.id')
											);		
				$totalCost+=$costPrice[$productID] * $quantity; // added by sourabh
                
                $kioskProdctSalesTable->behaviors()->load('Timestamp');
				$KioskProductSalesEntity = $kioskProdctSalesTable->newEntity($kioskProductSaleData,['validate' => false]);
				$KioskProductSalesEntity = $kioskProdctSalesTable->patchEntity($KioskProductSalesEntity,$kioskProductSaleData,['validate' => false]);
				if($kioskProdctSalesTable->save($KioskProductSalesEntity)){
					$data = array(
								'quantity' => $quantity,
								'product_code' => $product_code[$productID],
								'selling_price_withot_vat' => $priceWithoutVat,
								'vat' => $vat_value,
							);
					if($kiosk_id == 0){
						$kiosk_id_to_use = 10000;
					}else{
						$kiosk_id_to_use = $kiosk_id;
					}
					$this->insert_to_ProductSellStats($productID,$data,$kiosk_id_to_use,$operations = '+');
					
					$counter++;
					$productData = array('quantity' => "Product.quantity - $quantity");
					$updateQry = "UPDATE `$productsTable` SET `quantity` = `quantity` - $quantity WHERE `$productsTable`.`id` = '$productID'";
					$conn = ConnectionManager::get('default');
					$stmt = $conn->execute($updateQry); 

					$rand = rand(500,10000);
					//mail('kalyanrajiv@gmail.com', "Line #4231- $rand", $updateQry);
					///$this->Product->query($updateQry);
				}
				
			}

			if($counter > 0){
					//$this->ProductReceipt->id = $saleId;
				$ProductReceipt_query = "UPDATE `$productreceiptsTable` SET `bill_amount` =  $amount WHERE `$productreceiptsTable`.`id` = '$saleId'";
				$conn = ConnectionManager::get('default');
				$stmt = $conn->execute($ProductReceipt_query); 
				$ProductReceipt_query_1 = "UPDATE `$productreceiptsTable` SET `bulk_discount` = $bulkDiscount WHERE `$productreceiptsTable`.`id` = '$saleId'";
				$conn = ConnectionManager::get('default');
				$stmt = $conn->execute($ProductReceipt_query_1);
				
                
                $query_update = "UPDATE `$productreceiptsTable` SET `orig_bill_amount` =  $amount WHERE `$productreceiptsTable`.`id` = '$saleId'";
                $conn = ConnectionManager::get('default');
				$stmt = $conn->execute($query_update);
				
				//Start block: cron dashboard code
				if(array_key_exists('amount_changed',$_SESSION)){
					$amt = $amount - $_SESSION['amount_changed'];
					//$finalAmount = $amt;
					unset($_SESSION['amount_changed']);
				}else{
					$amt = 0;
				}
				
				if($amt >= 0){
					
					if($kiosk_id == 10000 || $kiosk_id == ""){
						$kioskid = 0;
					}else{
						$kioskid = $kiosk_id;
					}
					$this->loadModel('DashboardData');
					$conditionArr = array();
						$conditionArr[] = array(
									  "date >=" => date('Y-m-d', strtotime($originalSaleDate)),
									  "date <" => date('Y-m-d', strtotime($originalSaleDate. ' +1 Days')),			
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
								if(array_key_exists("product_sale",$dash_value)){
									$new_dash_data[$daash_key]['product_sale'] = (float)$dash_value['product_sale'] + (float)$amt;
								}
								if(array_key_exists("product_sale_desc",$dash_value)){
									$product_sale_desc = unserialize($dash_value['product_sale_desc']);
									$product_sale_desc['credit'] = (float)$product_sale_desc['credit'] + (float)$amt;
									$product_sale_desc_new = serialize($product_sale_desc);
									$new_dash_data[$daash_key]['product_sale_desc'] = $product_sale_desc_new;
								}
								if(array_key_exists("net_credit_desc",$dash_value)){
									$credit_desc = unserialize($dash_value['net_credit_desc']);
									$credit_desc[0] = (float)$credit_desc[0] + (float)$amt;
									$credit_desc_new = serialize($credit_desc);
									$new_dash_data[$daash_key]['net_credit_desc'] = $credit_desc_new;
								}
								
								if(array_key_exists("total_sale",$dash_value)){
									$new_dash_data[$daash_key]['total_sale'] = (float)$dash_value['total_sale'] + (float)$amt;
								}
								if(array_key_exists("net_credit",$dash_value)){
									$new_dash_data[$daash_key]['net_credit'] = (float)$dash_value['net_credit'] + (float)$amt;
								}
								if(array_key_exists("net_sale",$dash_value)){
									$new_dash_data[$daash_key]['net_sale'] = (float)$dash_value['net_sale'] + (float)$amt;
								}
							}
						}
						
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
				
				
                
				$billCost_query = $kioskProdctSalesTable->find('list', array(
															'conditions' => array('product_receipt_id' => $saleId),
															'keyField' => 'product_id',
															'valueField' => 'quantity',
															));
				$billCost_query = $billCost_query->hydrate(false);
				if(!empty($billCost_query)){
					$billCost = $billCost_query->toArray();
				}else{
					$billCost = array();
				}
				$totalBillCost = 0;
				if(count(array_keys($billCost)) > 0){
					$prodIds = array_keys($billCost);
					$prodCosts_query = $ProductTable->find('list', array(
																		'conditions' => array('id IN' => $prodIds),
																		'keyField' => 'id',
																		'valueField' => 'cost_price',
																	)
										);
					$prodCosts_query = $prodCosts_query->hydrate(false);
					if(!empty($prodCosts_query)){
						$prodCosts = $prodCosts_query->toArray();
					}else{
						$prodCosts = array();
					}
					
					foreach($billCost as $prodId => $prodQty){
						$totalBillCost += $prodCosts[$prodId] * $prodQty;
					}
				}
			//$this->ProductReceipt->query("UPDATE `$productreceiptsTable` SET `bill_cost` = `bill_cost` + $totalCost WHERE `$productreceiptsTable`.`id` = '$saleId'");// added by sourabh
			$ProductReceipt_query2 = "UPDATE `$productreceiptsTable` SET `bill_cost` = $totalBillCost WHERE `$productreceiptsTable`.`id` = '$saleId'";// modified by rajiv
			$conn = ConnectionManager::get('default');
			$stmt = $conn->execute($ProductReceipt_query2); 
			$productReceipt_query = $ProductReceiptTable->find('all',array(
																			'conditions' => array('id' => $saleId),
																			//'contain' => array('KioskProductSales'),
																		)
															);
			$productReceipt_query = $productReceipt_query->hydrate(false);
			if(!empty($productReceipt_query)){
				$productReceipt = $productReceipt_query->first();
			}else{
				$productReceipt = array();
			}

			$processed_by = $productReceipt['processed_by'];
			$userName_query = $this->Users->find('all',array('conditions'=>array('Users.id'=>$processed_by),
														 'fields'=>array('username')));
			$userName_query = $userName_query->hydrate(false);
			if(!empty($userName_query)){
				$userName = $userName_query->first();
			}else{
				$userName = array();
			}
			$user_name = $userName['username'];
            if(!empty($productReceipt)){
                $kioskProductSaleData_Id = $productReceipt['id'];
                $kioskProductSaleData_query = $kioskProdctSalesTable->find('all',[
                                                                                  'conditions' => ['product_receipt_id' => $kioskProductSaleData_Id]
                                                                                  ]);
                $kioskProductSaleData_query = $kioskProductSaleData_query->hydrate(false);
                if(!empty($kioskProductSaleData_query)){
                    $kioskProductSaleData = $kioskProductSaleData_query->toArray();
                }else{
                    $kioskProductSaleData = array();
                }
            }
            $this->set(compact('kioskProductSaleData'));
			foreach($kioskProductSaleData as $key => $productDetail){
				$productIdArr[] = $productDetail['product_id'];
			}
			foreach($productIdArr as $product_id){
				$product_detail_query = $ProductTable->find('all', array('conditions'=>array('id'=>$product_id),
																		 'fields' => array('id','product','product_code'))
														  );
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
				
			$vat = $productReceipt['vat'];
			
			$paymentDetails_query = $PaymentDetailTable->find('all',array('conditions' => array('product_receipt_id' => $saleId)
																	  )
														  );
			$paymentDetails_query = $paymentDetails_query->hydrate(false);
			if(!empty($paymentDetails_query)){
				$paymentDetails = $paymentDetails_query->toArray();
			}else{
				$paymentDetails = array();
			}
			$payment_method = array();
			foreach($paymentDetails as $key=>$paymentDetail){
				$payment_method[] = $paymentDetail['payment_method']." ".$CURRENCY_TYPE.$paymentDetail['amount'];
			}
					
			$countryOptions = Configure::read('uk_non_uk');
				
			$fullAddress = $kiosk_id = $kioskDetails = $kioskName = $kioskAddress1 = $kioskAddress2 = $kioskCity = $kioskState = $kioskZip  = $kioskZip = $kioskContact = $kioskCountry = $kioskTable = "";
			if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS &&
			   $this->request->session()->read('Auth.User.user_type') =='wholesale'){
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
			$send_by_email = Configure::read('send_by_email');
			if($receiptRequiredSession == 1){
				$emailSender = Configure::read('EMAIL_SENDER');
				$Email = new Email();
				$Email->config('default');
				$Email->viewVars(array('productReceipt' => $productReceipt,'payment_method' => $payment_method,'vat' => $vat,'settingArr' =>$settingArr,'user_name'=>$user_name,'productName'=>$productName,'productCode'=>$productCode,'kioskContact'=>$kioskContact,'kioskTable'=>$kioskTable,'countryOptions'=>$countryOptions));
				//$Email->config(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
				//$Email = new CakeEmail(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
				$emailTo = $productReceipt['email'];
				$Email->template('receipt_new_sale');//using same template for new sale and edit sale
				$Email->emailFormat('both');
				$Email->to($emailTo);
				$Email->transport(TRANSPORT);
				$Email->from([$send_by_email => $emailSender]);
				//$Email->sender("sales@oceanstead.co.uk");
				$Email->subject('Order Receipt');
				$Email->send();
			}
					
			$this->Flash->success("Invoice has been updated");
			$this->request->Session()->delete('Basket');
			$this->request->Session()->delete('BulkDiscount');
			$this->request->Session()->delete('receipt_required');
			$this->request->Session()->delete('oldBasket');
			$this->request->Session()->delete('session_basket');
			if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
				$kiosk_id_to_set = $this->get_kiosk_for_invoice();
				return $this->redirect(array('controller'=>'product_receipts','action'=>"search?kiosk_id=$kiosk_id_to_set&date_type=invoice"));
			}else{
				return $this->redirect(array('controller'=>'product_receipts','action'=>'index'));
			}
			//return $this->redirect(array('controller'=>'product_receipts','action'=>'index'));
		}
	}
	
	public function updateProductPayment($product_receipt_id = '', $kiosk_id = ''){
		
		$product_receipt_id;
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
		$paymentData_query = $this->ProductPayments->find('all',array(
													'conditions' => array(
																		  'ProductPayments.product_receipt_id' => $product_receipt_id,
																		  'ProductPayments.kiosk_id'  => $kiosk_id,
																		  ),
													'field'=>array('ProductPayments.product_receipt_id','ProductPayments.kiosk_id')
													)
												);
		$paymentData_query  = $paymentData_query->hydrate(false);
		if(!empty($paymentData_query)){
			$paymentData = $paymentData_query->toArray();
		}else{
			$paymentData = array();
		}
        //pr($paymentData);die;
		$product_receipt_id = $paymentData['0']['product_receipt_id'];
		
		//$kiosk_id = $paymentData['0']['ProductPayment']['kiosk_id'];
		$receiptTable_source = "kiosk_{$kiosk_id}_product_receipts";
		$receiptTable = TableRegistry::get($receiptTable_source,[
                                                                                    'table' => $receiptTable_source,
                                                                                ]);
		$saleData_query = $receiptTable->find('all', array(
														'conditions' => array(
																			   'id' => $product_receipt_id,
																					),
														));
		$saleData_query = $saleData_query->hydrate(false);
		if(!empty($saleData_query)){
			$saleData = $saleData_query->first();
		}else{
			$saleData = array();
		}
		//pr($saleData["ProductReceipt"]["orig_bill_amount"]);die;
		$orignal_amount = 0;
		$orignal_amount = $saleData["orig_bill_amount"];
		//pr($saleData);die;
		/*$dbo = $this->ProductReceipt->getDatasource();
		$logData = $dbo->getLog();
		$getLog = end($logData['log']);
		echo "Log Query:".$getLog['query'];
		//pr($saleData['ProductReceipt']['created']);*/
		
		$saleAmount = $saleData['bill_amount'];
		//code added on 1st June,16; new code will restrict user to edit payments only for same day
		$conn = ConnectionManager::get('default');
		$stmt = $conn->execute('SELECT CURDATE() as timeDate'); 
		$currentTime = $stmt ->fetchAll('assoc');
		
		$currentDate = strtotime($currentTime[0]['timeDate']);
		//$checkTime = strtotime('-24 hours',$time);
		if(count($paymentData) && $this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
			$created = getdate(strtotime($saleData['created']));
			$curDate =  $created["year"]."-".$created["mon"]."-".$created["mday"];
			$createdTime = strtotime($curDate);
			if($currentDate != $createdTime){//$checkTime > $createdTime
				//echo $receiptTable;
				//pr($currentTime);
				//echo "$curDate<br/>";
				//echo $paymentData['0']['ProductPayment']['created'];
				//echo "$currentDate != $createdTime";die;
				$this->Flash->error('Payment can only be updated within same day!');
				return $this->redirect(array('controller' => 'product_receipts', 'action' => 'kiosk_product_payments'));
				//return $this->redirect(array('action' => 'index'));
				die;
			}
		}
		
		if ($this->request->is(array('post', 'put'))){
		
			if(array_key_exists('cancel',$this->request->data)){
					$this->Flash->error('You have cancelled transaction!');
				    return $this->redirect(array('controller' => 'product_receipts', 'action' => 'kiosk_product_payments'));
					die;
			}
			if(is_array($this->request->data) && array_key_exists('UpdatePayment',$this->request->data) && count($this->request->data['UpdatePayment'])){
                //pr($this->request);die;
					$totalAmount = 0;
					$addedAmount = 0;
					$updatedPaymentData = $this->request->data['UpdatePayment'];
					//card or cash options
					$updatedAmountData = $this->request->data['updated_amount'];
					//card or carsh amounts
					$sale_amount = round($this->request->data['sale_amount'],2);
					//total updated amount
					if(array_key_exists('added_amount',$this->request->data)){
						$addedAmount = $this->request->data['added_amount'];
					}
					//if new row added for amount
					//echo "<br/>";
					foreach($updatedPaymentData as $paymentId => $paymentMode){
						$totalAmount += $updatedAmountData[$paymentId];
					}
					$totalAmount = $addedAmount + $totalAmount;//die;
					$totalAmount = round($totalAmount,2);
                    
					if($totalAmount != $sale_amount){
						//validation check
						$this->Flash->error('Payment could not be updated!');
						return $this->redirect(array('action' => 'update_product_payment', $product_receipt_id,$kiosk_id));
						die;
					}
					$saveAdminPayment = 0;
					//****saving newly added payment amount
					if(array_key_exists('added_amount',$this->request->data) && is_numeric($this->request->data['added_amount']) && $this->request->data['added_amount'] > 0){
						$paymntData_query = $this->ProductPayments->find('all',array(
																				'conditions' => array(
																									  'ProductPayments.product_receipt_id'=>$product_receipt_id,
																									  'ProductPayments.kiosk_id' => $kiosk_id,
																									  )
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
						//pr($this->request->data);
						//adding new fields
						$paymntData['payment_method'] = $this->request->data['new_change_mode'];
						$paymntData['amount'] = $this->request->data['added_amount'];
						$ProductPaymentsEntity = $this->ProductPayments->newEntity($paymntData,['validate' => false]);
						$ProductPaymentsEntity = $this->ProductPayments->patchEntity($ProductPaymentsEntity,$paymntData,['validate' => false]);
						//pr($paymntData);
						if($this->ProductPayments->save($ProductPaymentsEntity)){
							$saveAdminPayment++;
						}
					}
					 
					// saving new added payment till here*****
					$sale_amount = $this->request->data['sale_amount'];
					foreach($updatedPaymentData as $paymentId => $paymentMode){
						
						$paymentDetailData = array(
													'id' => $paymentId,
													'payment_method' => $paymentMode,
													'amount' => $updatedAmountData[$paymentId]
													);
						$ProductPaymentsEntity = $this->ProductPayments->get($paymentId);
						$ProductPaymentsEntity = $this->ProductPayments->patchEntity($ProductPaymentsEntity,$paymentDetailData,['validate' => false]);
                        //pr($ProductPaymentsEntity);die;
						//pr($paymentDetailData);
						if($this->ProductPayments->save($ProductPaymentsEntity)){
							$saveAdminPayment++;
						}
					}
					//pr($updatedPaymentData);
					//pr($this->request['data']);
					//die;
					if($saveAdminPayment > 0){
						$this->Flash->error('Payment has been successfully updated!');
						return $this->redirect(array('controller' => 'product_receipts', 'action' => 'kiosk_product_payments'));
					}else{
						$this->Flash->error('Payment could not be updated!');
                        return $this->redirect(array('action' => 'update_product_payment', $product_receipt_id,$kiosk_id));
						//return $this->redirect(array('action' => 'update_unlock_payment',$unlockId));
					}
				}
		}
		
		$this->set(compact('paymentData','paymentType','kiosks','users','saleAmount','orignal_amount'));
	}
    
    public function adminKioskProductRefund($kioskId = '' , $kioskProductSaleId = '', $receiptId = 0) {
		//pr($this->request['data']['KioskProductSale']['id']);die;
		$tableReceipt = "kiosk_{$kioskId}_product_receipts";
		$productTable = "kiosk_{$kioskId}_products";
		
		//$this->ProductReceipt->setSource("kiosk_{$kioskId}_product_receipts");
        $ProductReceipt_source = "kiosk_{$kioskId}_product_receipts";
        $ProductReceiptTable = TableRegistry::get($ProductReceipt_source,[
                                                                            'table' => $ProductReceipt_source,
                                                                        ]);
		//$this->KioskProductSale->setSource("kiosk_{$kioskId}_product_sales");
        $KioskProductSale_source = "kiosk_{$kioskId}_product_sales";
        $KioskProductSaleTable = TableRegistry::get($KioskProductSale_source,[
                                                                            'table' => $KioskProductSale_source,
                                                                        ]);
		//$this->Product->setSource("kiosk_{$kioskId}_products");
        $Product_source = "kiosk_{$kioskId}_products";
        $ProductTable = TableRegistry::get($Product_source,[
                                                                'table' => $Product_source,
                                                            ]);
		//$this->initialize_customer();
		//$kioskProductSaleId = $this->request->params['pass'][0];
		
		$product_id_query =  $KioskProductSaleTable->find('list',[
                                                                'keyField' => 'id',
                                                                'valueField' => 'product_id',
                                                                'conditions'  => ['id' => $kioskProductSaleId]
                                                           ]
                                                    );
		$product_id_query = $product_id_query->hydrate(false);
        if(!empty($product_id_query)){
            $product_id = $product_id_query->toArray();
        }else{
            $product_id = array();
        }
        if(empty($product_id)){
            $product_id = array(0 => null);
        }
		$products_query = $ProductTable->find('all' ,array(
				'fields' => array('product_code','product','image','id'),
				'conditions'=> array('id IN'=> $product_id)
						)
					);
		$products_query = $products_query->hydrate(false);
        if(!empty($products_query)){
            $products[] = $products_query->first();
        }else{
            $products = array();
        }
        //pr($products);die;
		$this->set(compact('products'));
		$refundOptions = Configure::read('refund_status');
		$settingArr = $this->setting;
		$currency = $settingArr['currency_symbol'];
		$this->set(compact('currency'));
		$queriesFired = array();
		
		if ($this->request->is(array('post', 'put'))) {
            //pr($this->request);die;
			$kioskDetails_query = $this->Kiosks->find('all',array('conditions'=>array('Kiosks.id'=>$kioskId),'fields'=>array('id','name','address_1','address_2','city','state','zip','contact','country')));
            $kioskDetails_query = $kioskDetails_query->hydrate(false);
            if(!empty($kioskDetails_query)){
                $kioskDetails = $kioskDetails_query->first();
            }else{
                $kioskDetails = array();
            }
            //pr($this->request['data']);die;
			//pr($this->request['data']['KioskProductSale']['customer_email']);die;
			$saleId = $this->request['data']['KioskProductSale']['id'];
			$productRecptId = $this->request['data']['KioskProductSale']['product_receipt_id'];
			$reqSalePrice = $this->request['data']['KioskProductSale']['sale_price'];
			$discount = $this->request['data']['KioskProductSale']['discount'];
			$discountedPrice = $reqSalePrice-($reqSalePrice * $discount/100);
			
			
			// ENTRY WITHOUT CUSTOMER EMAIL GIVES AN INTERNAL ERROR
			 
			//validation for first name
			$errorArray = array();
			if(empty($this->request['data']['KioskProductSale']['fname'])){
				$errorArray[] = "Please enter customer's First Name";
			}
			if(empty($this->request['data']['KioskProductSale']['lname'])){
				$errorArray[] = "Please enter customer's Last Name";
			}
			if(empty($this->request['data']['KioskProductSale']['email'])){
				$errorArray[] = "Please enter customer's email";
			}
			
			////validation for mobile
			$mobile = $this->request['data']['KioskProductSale']['mobile'];
			if(strlen($mobile) < 11){
				$errorArray[] = "Mobile number must be 11 digit long"; 
			}
			if(empty($this->request['data']['KioskProductSale']['zip'])){
				$errorArray[] = "Please enter customer's Zip code"; 
			}
			
			if(empty($this->request['data']['KioskProductSale']['address_1'])){
				$errorArray[] = "Please enter Customer Address";
			}
			
			if(empty($this->request['data']['KioskProductSale']['address_2'])){
				//$errorArray[] = "Please enter customer Address 2";
			}
			
			//validation for city
			if(empty($this->request['data']['KioskProductSale']['city'])){
				$errorArray[] = "Please enter customer City";
			}
			
			if(empty($this->request['data']['KioskProductSale']['state'])){
				$errorArray[] = "Please enter customer State";
			}
			if($this->request['data']['KioskProductSale']['refund_status'] == 0){
				$errorArray[] = "Refund could not be saved. Please choose a reason for the refund";
			}
			
			if($this->request->data['KioskProductSale']['refund_price'] > $discountedPrice){
				$errorArray[] = "Refund could not be saved. Refund amount cannot be more than the actual amount";
			}
			
			if($this->request->data['KioskProductSale']['refund_price'] <= 0){
				$errorArray[] = "Refund could not be saved. Refund amount must be more than zero";
			}
			
			if(count($errorArray) == 0){
				$quantitySold = $this->request->data['KioskProductSale']['quantity'];
				$quantityRefunded = $this->request->data['KioskProductSale']['quantity_returned'];
				$quantity = $quantitySold - $quantityRefunded;
				
				//updating current sale on refund in the receipt for the product returned and updating its quantity					
				$customerProductsData = array(
					'id' => $this->request->data['KioskProductSale']['id'],
					'kiosk_id' => $this->request->data['KioskProductSale']['kiosk_id'],
					'product_id' => $this->request->data['KioskProductSale']['product_id'],
					'quantity' => $quantity,
					'sale_price' => $this->request->data['KioskProductSale']['sale_price'],
					'refund_price' => '', //why we are not saving refund price
					'discount' => $this->request->data['KioskProductSale']['discount'],
					'discount_status' => $this->request->data['KioskProductSale']['discount_status'],
					'refund_gain' => '', //why we are not saving refund gain
					'sold_by' => $this->request->data['KioskProductSale']['sold_by'],
					'refund_by' => '', //why we are not saving refund by
					'status' => 1,
					'refund_status' => 0,
					'refund_remarks' => '',
					'product_receipt_id' => $this->request->data['KioskProductSale']['product_receipt_id']
				);
                $KioskProductSaleTable->behaviors()->load('Timestamp');
				$get_id = $KioskProductSaleTable->get($saleId);
                $patch_entity = $KioskProductSaleTable->patchEntity($get_id,$customerProductsData,['validate' => false]);
				if($KioskProductSaleTable->save($patch_entity)) {
					//***updating the payment table and decreasing amount from cash entries in Payment table
					//[For card entries it is not justifiable]
					$refundPrice = $this->request->data['KioskProductSale']['refund_price'];
					$quantityRefunded = $this->request->data['KioskProductSale']['quantity_returned'];
					$paymentRS_query = $this->ProductPayments->find('all', array(
																			   'conditions' => array(
																							'ProductPayments.product_receipt_id' => $receiptId,
																							'ProductPayments.payment_method' => 'Cash',
																							'ProductPayments.kiosk_id' => $kioskId,
																									),
																			   'order' => 'ProductPayments.amount desc'
																			   )
																);
                    $paymentRS_query = $paymentRS_query->hydrate(false);
                    if(!empty($paymentRS_query)){
                        $paymentRS = $paymentRS_query->first();
                    }else{
                        $paymentRS = array();
                    }
					$refundAmount = $refundPrice * $quantityRefunded;
					
					if(count($paymentRS) > 0){
						//pr($this->request->data['KioskProductSale']);die;
						//Note: > 0 implies we have atleast one cash entry in Payment table
						$paymentID = $paymentRS['id'];
						//$remaingAmount = $paymentRS['ProductPayment']['amount'] - $refundAmount;
						$paymentDetailData = array(
												'kiosk_id' => $paymentRS['kiosk_id'],
												'user_id' => $this->Auth->user('id'),
												'product_receipt_id'=> $paymentRS['product_receipt_id'],
												'payment_method' => $paymentRS['payment_method'],
												'description' => $this->request->data['KioskProductSale']['refund_remarks'],
												'amount' => -$refundAmount,
												'product_id' => $this->request->data['KioskProductSale']['product_id'],
												'payment_status' => 1, //done
												'refunded_quantity' => $quantityRefunded,
												'status' => 2,//2 means refunded
											);
						//echo "payment";pr($paymentDetailData);
                        $this->ProductPayments->behaviors()->load('Timestamp');
						$new_entity = $this->ProductPayments->newEntity($paymentDetailData,['validate' => false]);
						$patch_Entity = $this->ProductPayments->patchEntity($new_entity,$paymentDetailData,['validate' => false]);
						//pr($paymentDetailData);die;
						if($this->ProductPayments->save($patch_Entity)){
							
						}
						
						
					}else{
						//Note: if only have card entries
						$paymentData_query = $this->ProductPayments->find('all', array(
																				  'conditions' => array(
																							'ProductPayments.product_receipt_id' => $receiptId),
																   ));
						$paymentData_query = $paymentData_query->hydrate(false);
						if(!empty($paymentData_query)){
                            $paymentData = $paymentData_query->first();
                        }else{
                            $paymentData = array();
                        }
                        //pr($paymentData);die;
						//unsetting the unrequired fields
						unset($paymentData['id']);
						unset($paymentData['payment_method']);
						unset($paymentData['amount']);
						unset($paymentData['created']);
						unset($paymentData['modified']);
						
						//adding new fields
						$paymentData['payment_method'] = 'Cash';
						$paymentData['amount'] = -$refundAmount;
						$paymentData['status'] = 2; //for refund
                        $this->ProductPayments->behaviors()->load('Timestamp');
						$NewEntity = $this->ProductPayments->newEntity($paymentData,['validate' => false]);
                        $PatchEntity = $this->ProductPayments->patchEntity($NewEntity,$paymentData,['validate' => false]);
						$this->ProductPayments->save($PatchEntity);
						
					}
						
						
					if($this->request->data['KioskProductSale']['refund_status'] == 2){//faulty case
						//saving the details in faulty returned products table
						$faultyReturnedData = array(
														'kiosk_id' => $this->request->data['KioskProductSale']['kiosk_id'],
														'receipt_id' => $this->request->data['KioskProductSale']['product_receipt_id'],
														'credit_by' => $this->Auth->user('id'),
														'product_id' => $this->request->data['KioskProductSale']['product_id'],
														'quantity' => $this->request->data['KioskProductSale']['quantity_returned'],
														'sale_price' => $this->request->data['KioskProductSale']['sale_price'],
														'remarks' => $this->request->data['KioskProductSale']['refund_remarks'],
														'discount' => $this->request->data['KioskProductSale']['discount'],
														//in percent
													);
						$EntityNew = $this->FaultyProducts->newEntity($faultyReturnedData,['validate' =>false]);
                        $EntityPatch = $this->FaultyProducts->patchEntity($EntityNew,$faultyReturnedData,['validate' =>false]);
						$this->FaultyProducts->save($EntityPatch);
						
					}
						
					$discountedPrice = $this->request['data']['KioskProductSale']['sale_price']-($this->request['data']['KioskProductSale']['sale_price']*$this->request['data']['KioskProductSale']['discount']/100);					
					$refundGain = $discountedPrice*$this->request->data['KioskProductSale']['quantity_returned']-($this->request->data['KioskProductSale']['refund_price']*$this->request->data['KioskProductSale']['quantity_returned']);
					//creating new record in the receipt for the refunded product sale
					$returnedProductsData = array(
													'kiosk_id' => $this->request->data['KioskProductSale']['kiosk_id'],
													'product_id' => $this->request->data['KioskProductSale']['product_id'],
													'quantity' => $this->request->data['KioskProductSale']['quantity_returned'],
													'sale_price' => 0,
													'refund_price' => $this->request->data['KioskProductSale']['refund_price'],
													'discount' => $this->request->data['KioskProductSale']['discount'],
													'discount_status' => $this->request->data['KioskProductSale']['discount_status'],
													'refund_gain' => $refundGain,
													'sold_by' => $this->request->data['KioskProductSale']['sold_by'],
													'refund_by' => $this->Auth->user('id'),
													'status' => 0,
													'sale_type' => 0,
													'refund_status' => $this->request->data['KioskProductSale']['refund_status'],
													'refund_remarks' => $this->request->data['KioskProductSale']['refund_remarks'],
													'product_receipt_id' => $this->request->data['KioskProductSale']['product_receipt_id']
												);
                    $KioskProductSaleTable->behaviors()->load('Timestamp');
					$New_E = $KioskProductSaleTable->newEntity($returnedProductsData,['validate' => false]);
                    $New_E = $KioskProductSaleTable->patchEntity($New_E,$returnedProductsData,['validate' => false]);
					$KioskProductSaleTable->save($New_E);
					
					
					$amountRefunded = $this->request->data['KioskProductSale']['refund_price']*$this->request->data['KioskProductSale']['quantity_returned'];
					
					$fname = $this->request['data']['KioskProductSale']['fname'];
					$lname = $this->request['data']['KioskProductSale']['lname'];
					$email = $this->request['data']['KioskProductSale']['email'];
					$mobile = $this->request['data']['KioskProductSale']['mobile'];
					$address_1 = $this->request['data']['KioskProductSale']['address_1'];
					$address_2 = $this->request['data']['KioskProductSale']['address_2'];
					$city = $this->request['data']['KioskProductSale']['city'];
					$state = $this->request['data']['KioskProductSale']['state'];
					$zip = $this->request['data']['KioskProductSale']['zip'];
					$created = $this->request['data']['KioskProductSale']['created'];
                    
                    $created = date('Y-m-d h:i:s',strtotime($created));
                    //echo $created;die;
					//$this->Receipt->setSource($tableReceipt);
                    $ReceiptTable = TableRegistry::get($tableReceipt,[
                                                                        'table' => $tableReceipt,
                                                                    ]);
						
					//updating receipt total amount - refund case
					$updateQry = "UPDATE $tableReceipt SET `bill_amount`=`bill_amount`-'$amountRefunded' ,`fname`= '$fname',`lname`='$lname',`email` = '$email',`mobile` ='$mobile',`address_1`= '$address_1',`address_2`= '$address_2',`city` = '$city',`state` = '$state',`zip` = '$zip',`created` = '$created' WHERE `id`='$productRecptId'";
                    $conn = ConnectionManager::get('default');
                    $stmt = $conn->execute($updateQry); 
                   
					$send_by_email = Configure::read('send_by_email'); 
					if(isset($this->request['data']['KioskProductSale']['email'])){
						$receiptId = $this->request['data']['KioskProductSale']['product_receipt_id'];
						//$this->ProductReceipt->setSource($tableReceipt);
                        $ProductReceiptTable = TableRegistry::get($tableReceipt,[
                                                                        'table' => $tableReceipt,
                                                                    ]);
						$productReceipt_query = $ProductReceiptTable->find('all',array(
																					'conditions' => array('id'  => $receiptId),
																					)
																	);
						$productReceipt_query = $productReceipt_query->hydrate(false);
                        if(!empty($productReceipt_query)){
                            $productReceipt = $productReceipt_query->first();
                        }else{
                            $productReceipt = array();
                        }
                        if(!empty($productReceipt)){
							$kiosk_product_table_id = $productReceipt['id'];
							if(!empty($kiosk_product_table_id)){
								$this->loadModel("Customers");
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
                        $this->set(compact('kiosk_product_table_data'));
                        //pr($kiosk_product_table_data);die;
						//pr($productReceipt);die;
						//added later by Rajiv on 22 october
						$productReturnArr = $productSaleArr =  $productIdArr = array();
		
						foreach($kiosk_product_table_data as $key => $productDetail){
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
								$returnQuantityArr[$returnKey] = $productReturnDetail['quantity'];
							}
						}
							
						$qttyArr = array();
						if(!empty($productSaleArr)){
							foreach($productSaleArr as $key => $productSaleDetail){
								$saleProductId = $productSaleDetail['product_id'];
								$saleReceiptId = $productSaleDetail['product_receipt_id'];
								$saleKey = "$saleProductId|$saleReceiptId";
								
								if(array_key_exists($saleKey,$returnQuantityArr)){
									if(!array_key_exists($saleKey,$qttyArr)){
										$qttyArr[$saleKey] = 0;
									}
									$qttyArr[$saleKey]+= $returnQuantityArr[$saleKey];
								}else{
									$qttyArr[$saleKey] = $productSaleDetail['quantity'];
								}
							}
						}
						//---added later by Rajiv on 22 october}
							
						$vat = $this->VAT;							
						$productName_query = $ProductTable->find('list',[
                                                                    'keyField' => 'id',
                                                                    'valueField'=> 'product'
                                                                  ]
                                                           );
                        $productName_query = $productName_query->hydrate(false);
                        if(!empty($productName_query)){
                            $productName = $productName_query->toArray();
                        }else{
                            $productName = array();
                        }
						$kiosk_query = $this->Kiosks->find('list');
						$kiosk_query = $kiosk_query->hydrate(false);
                        if(!empty($kiosk_query)){
                            $kiosk = $kiosk_query->toArray();
                        }else{
                            $kiosk = array();
                        }
						
						$paymentDetails_query = $this->ProductPayments->find('all',array(
																 'conditions' => array('product_receipt_id' => $productReceipt['id'],'status' => 1,
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
						$productCode_query = $this->Products->find('list', array(
																					 'keyField' => 'id',
																					 'valueField' => 'product_code',
																					 //'fields' => array('id','product')
																					 ));
						$productCode_query = $productCode_query->hydrate(false);
						if(!empty($productCode_query)){
							$productCode = $productCode_query->toArray();
						}else{
							$productCode = array();
						}
						//pr($productSaleArr);die;
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
												'currency'=> $currency,
												'refundOptions' => $refundOptions,
												'kioskDetails' => $kioskDetails,
                                                 'sales_data' => $productSaleArr,
												 'payment_method1'=>$payment_method1,
												 'product_code' => $productCode
												)
										 );
						//$settingArr => setings
						// $productReceipt => containing sub-arrays for [ProductReceipt], [Customer], [KioskProductSale], [PaymentDetail]
						//$refundOptions => from config e.g.: [0] => Not Refunded
						//$vat => vat e.g.: 20
						//$kiosk => all kiosk array with kiosk title and their ids
						$emailTo = $this->request['data']['KioskProductSale']['email'];
						$Email->template('refund_receipt');
						$Email->emailFormat('html');
						$Email->to($emailTo);
						$Email->transport(TRANSPORT);
						$Email->from([$send_by_email => $emailSender]);
						//$Email->sender('sales@oceanstead.co.uk','Sales Team');
						//This should be added in config file
						$Email->subject('Order Receipt');
						$Email->send();
					}
						
					if($this->request->data['KioskProductSale']['refund_status'] == 1){
						$productID = $this->request->data['KioskProductSale']['product_id'];
                        //pr($productID);die;
						$quantityReturned = $this->request->data['KioskProductSale']['quantity_returned'];
						$updateQry = "UPDATE `$productTable` SET `quantity` = `quantity` + $quantityReturned WHERE `$productTable`.`id` = $productID";
						/*
						 UPDATE `kiosk_3_products` SET `quantity` = `quantity` + 1 WHERE `kiosk_3_products`.`id` = 5396
						*/
                        $conn_u = ConnectionManager::get('default');
                        $stmt_u = $conn_u->execute($updateQry); 
						
						$rand = rand(500,10000);
						//mail('kalyanrajiv@gmail.com', "Line #1285- $rand", json_encode($queriesFired));
					}
					
					$this->Flash->success(__('The product has been refunded.'));
					//die("---test---");
					return $this->redirect(array('controller' => 'product_receipts', 'action' => 'kiosk_product_payments'));
				}else{
					$this->Flash->error(__('The product receipt could not be saved. Please, try again.'));
				}
			}else{
                list($kioskId,$sale_id,$receipt_id) = $this->request->params['pass'];
			$options = array(
			   'conditions' => array('id' => $sale_id)
			   );
			$data_query = $KioskProductSaleTable->find('all', $options);
            $data_query = $data_query->hydrate(false);
            if(!empty($data_query)){
                $data = $data_query->first();
            }else{
                $data = array();
            }
            
            //pr($data);die;
			//if customer id, fetch from customer table, else from product receipts
			if(array_key_exists('customer_id',$data) &&
			   $data['customer_id'] > 0){
				$customerId = $data['customer_id'];
				$customerEmail = "";
				if($customerId){
					$options = array(
					'conditions' => array('Customers.id' => $customerId)
						 );
					$customerInformation_query = $this->Customers->find('all',$options);
                    $customerInformation_query = $customerInformation_query->hydrate(false);
                    if(!empty($customerInformation_query)){
                        $customerInformation = $customerInformation_query->first();
                    }else{
                        $customerInformation = array();
                    }
					$customerEmail = $customerInformation['email'];
				}
			}else{
				$customerDataReceipt = $ProductReceiptTable->find('all',array('conditions' => array('id'=>$receipt_id),'fields'=>array('id','email')));
                $customerDataReceipt = $customerDataReceipt->hydrate(false);
                if(!empty($customerDataReceipt)){
                    $customerDataReceipt = $customerDataReceipt->first();
                }else{
                    $customerDataReceipt = array();
                }
				$customerEmail = $customerDataReceipt['email'];
			}
			if($data['refund_status'] != 0){
				$this->Flash->error(__('The product has been already refunded to customer.'));
				return $this->redirect(array('action' => 'index'));
			}
			$discount = $data['discount'];
			$salePrice = $data['sale_price'];
			
			if(!empty($discount)){
				$data['refund_price'] = $salePrice - (($salePrice * $discount) / 100);
			}else{
				$data['refund_price'] = $salePrice;
			}
                if(!empty($data)){
                    $recepitTableId = $data['product_receipt_id'];
                }
                //pr($salesTableId);die;
                $product_receipt_data_query = $ProductReceiptTable->find('all',[
                                                    'conditions' => ['id' => $recepitTableId]  
                                                    ]);
                $product_receipt_data_query = $product_receipt_data_query->hydrate(false);
                if(!empty($product_receipt_data_query)){
                    $product_receipt_data = $product_receipt_data_query->toArray();
                }else{
                    $product_receipt_data = array();
                }
                //pr($product_receipt_data);die;
				$request_data = $this->request->data;
            $this->set(compact('product_receipt_data','request_data'));
			$this->request->data = $data;
			$this->set('customerEmail',$customerEmail);
				//print_r($errorArray);
				$this->Flash->error(implode("<br/> - ",$errorArray),['escape' => false]);
				//else of errorArray
			}
		} else {
			list($kioskId,$sale_id,$receipt_id) = $this->request->params['pass'];
			$options = array(
			   'conditions' => array('id' => $sale_id)
			   );
			$data_query = $KioskProductSaleTable->find('all', $options);
            $data_query = $data_query->hydrate(false);
            if(!empty($data_query)){
                $data = $data_query->first();
            }else{
                $data = array();
            }
            
            //pr($data);die;
			//if customer id, fetch from customer table, else from product receipts
			if(array_key_exists('customer_id',$data) &&
			   $data['customer_id'] > 0){
				$customerId = $data['customer_id'];
				$customerEmail = "";
				if($customerId){
					$options = array(
					'conditions' => array('Customers.id' => $customerId)
						 );
					$customerInformation_query = $this->Customers->find('all',$options);
                    $customerInformation_query = $customerInformation_query->hydrate(false);
                    if(!empty($customerInformation_query)){
                        $customerInformation = $customerInformation_query->first();
                    }else{
                        $customerInformation = array();
                    }
					$customerEmail = $customerInformation['email'];
				}
			}else{
				$customerDataReceipt = $ProductReceiptTable->find('all',array('conditions' => array('id'=>$receipt_id),'fields'=>array('id','email')));
                $customerDataReceipt = $customerDataReceipt->hydrate(false);
                if(!empty($customerDataReceipt)){
                    $customerDataReceipt = $customerDataReceipt->first();
                }else{
                    $customerDataReceipt = array();
                }
				$customerEmail = $customerDataReceipt['email'];
			}
			if($data['refund_status'] != 0){
				$this->Flash->error(__('The product has been already refunded to customer.'));
				return $this->redirect(array('action' => 'index'));
			}
			$discount = $data['discount'];
			$salePrice = $data['sale_price'];
			
			if(!empty($discount)){
				$data['refund_price'] = $salePrice - (($salePrice * $discount) / 100);
			}else{
				$data['refund_price'] = $salePrice;
			}
                if(!empty($data)){
                    $recepitTableId = $data['product_receipt_id'];
                }
                //pr($salesTableId);die;
                $product_receipt_data_query = $ProductReceiptTable->find('all',[
                                                    'conditions' => ['id' => $recepitTableId]  
                                                    ]);
                $product_receipt_data_query = $product_receipt_data_query->hydrate(false);
                if(!empty($product_receipt_data_query)){
                    $product_receipt_data = $product_receipt_data_query->toArray();
                }else{
                    $product_receipt_data = array();
                }
                //pr($product_receipt_data);die;
            $this->set(compact('product_receipt_data'));
			$this->request->data = $data;
			$this->set('customerEmail',$customerEmail);
		}  
	}
    
    public function editPayment($kioskId,$reciptId,$id){
		$tableReceipt = "kiosk_{$kioskId}_product_receipts";
		$productTable = "kiosk_{$kioskId}_products";
		
        $ProductReceipt_source = "kiosk_{$kioskId}_product_receipts";
        $ProductReceiptTable = TableRegistry::get($ProductReceipt_source,[
                                                                            'table' => $ProductReceipt_source,
                                                                        ]);
        $KioskProductSale_source = "kiosk_{$kioskId}_product_sales";
        $KioskProductSaleTable = TableRegistry::get($KioskProductSale_source,[
                                                                            'table' => $KioskProductSale_source,
                                                                        ]);
        $Product_source = "kiosk_{$kioskId}_products";
        $ProductTable = TableRegistry::get($Product_source,[
                                                                'table' => $Product_source,
                                                            ]);
		$kioskProductSale_query = $KioskProductSaleTable->find('all' ,array(
																			'conditions'=> array('product_receipt_id'=> $reciptId,
																									'id' => $id,
																									)
																		)
														);
        $kioskProductSale_query = $kioskProductSale_query->hydrate(false);
        if(!empty($kioskProductSale_query)){
            $kioskProductSale = $kioskProductSale_query->first();
        }else{
            $kioskProductSale = array();
        }
		$ProductPayment_query = $this->ProductPayments->find('all' ,array(
																			'conditions'=> array('product_receipt_id'=> $reciptId,
																								'kiosk_id' => $kioskId,
																								//'status' => 1
																								)
																		)
														);
        $ProductPayment_query = $ProductPayment_query->hydrate(false);
        if(!empty($ProductPayment_query)){
            $ProductPayment = $ProductPayment_query->toArray();
        }else{
            $ProductPayment = array();
        }
		$cashCardAmt = 0;
		foreach($ProductPayment as $key => $value){
			$cashCardAmt += $value['amount'];
		}
		$productId = $kioskProductSale['product_id'];
		$products_query = $ProductTable->find('all' ,array(
                                                        'conditions'=> array('id'=> $productId)
													)
										);
        $products_query = $products_query->hydrate(false);
        if(!empty($products_query)){
            $products[] = $products_query->first();
        }else{
            $products = array();
        }
		$selling_price = $products[0]['selling_price'];
		$discount = $products[0]['discount'];
		$lowest_price = $selling_price - ($selling_price*$discount/100);
		
		
		
		$kiosks_query = $this->Kiosks->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'name',
                                                'conditions' => ['Kiosks.status' => 1],
                                                'order' => ['Kiosks.name asc']
                                            ]
                                      );
        $kiosks_query = $kiosks_query->hydrate(false);
        if(!empty($kiosks_query)){
            $kiosks = $kiosks_query->toArray();
        }else{
            $kiosks = array();
        }
		$settingArr = $this->setting;
		$currency = $settingArr['currency_symbol'];
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
		$this->set(compact('products','kioskProductSale','kiosks','users','currency','cashCardAmt','lowest_price'));
		if($this->request->is('post','put')){
			$changed_amount = $this->request->data['changed_amount'];
			$org_amount = $this->request->data['org_amount'];
			$id = $this->request->data['id']; // kioskproduct sale id
			$kioskId = $this->request->data['kiosk_id'];
			$quantity = $this->request->data['quantity'];
			$recitId = $this->request->data['product_receipt_id'];
			$product_id = $this->request->data['product_id'];
			if($changed_amount == $org_amount){
				$this->Flash->error("There is no diff in payment");
				return $this->redirect(array('controller' => 'product_receipts', 'action' => 'kiosk_product_payments'));
			}else{
					$diff = $changed_amount - $org_amount;
					$this->redirect(array('action' => 'payment_method',$changed_amount,$recitId,$kioskId,$diff,$id));
			}
		}
	}
    
    public function paymentMethod($changed_amount,$recitId,$kioskId,$diff,$id){
		$tableReceipt = "kiosk_{$kioskId}_product_receipts";
		$productTable = "kiosk_{$kioskId}_products";
		
        $ProductReceipt_source = "kiosk_{$kioskId}_product_receipts";
        $ProductReceiptTable = TableRegistry::get($ProductReceipt_source,[
                                                                            'table' => $ProductReceipt_source,
                                                                        ]);
        $KioskProductSale_source = "kiosk_{$kioskId}_product_sales";
        $KioskProductSaleTable = TableRegistry::get($KioskProductSale_source,[
                                                                            'table' => $KioskProductSale_source,
                                                                        ]);
        $Product_source = "kiosk_{$kioskId}_products";
        $ProductTable = TableRegistry::get($Product_source,[
                                                                'table' => $Product_source,
                                                            ]);
		
		$kioskProductSale = $KioskProductSaleTable->find('all' ,array(
																		'conditions'=> array('product_receipt_id'=> $recitId,
																						'id'=>$id												
																							)
																		)
														);
        $kioskProductSale = $kioskProductSale->hydrate(false);
        if(!empty($kioskProductSale)){
            $kioskProductSale = $kioskProductSale->first();
        }else{
            $kioskProductSale = array();
        }
		$ProductPayment_query = $this->ProductPayments->find('all' ,array(
																			'conditions'=> array('product_receipt_id'=> $recitId,
																								'kiosk_id' => $kioskId,
																								//'status' => 1
																								)
																		)
														);
        $ProductPayment_query = $ProductPayment_query->hydrate(false);
        if(!empty($ProductPayment_query)){
            $ProductPayment = $ProductPayment_query->toArray();
        }else{
            $ProductPayment = array();
        }
		$cashCardAmt = 0;
		foreach($ProductPayment as $key => $value){
			$cashCardAmt += $value['amount'];
		}
	    $cashCardAmt;
		$totalAmt = $kioskProductSale['quantity']*$diff;
		$finalAmt = $cashCardAmt + $totalAmt;//die;
		$kiosks_query = $this->Kiosks->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'name',
                                                'conditions' => ['Kiosks.status' => 1],
                                                'order' => ['Kiosks.name asc']
                                            ]
                                    );
        $kiosks_query = $kiosks_query->hydrate(false);
        if(!empty($kiosks_query)){
            $kiosks = $kiosks_query->toArray();
        }else{
            $kiosks = array();
        }
		$settingArr = $this->setting;
		$currency = $settingArr['currency_symbol'];
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
		$this->set(compact('ProductPayment','kiosks','users','currency','changed_amount','finalAmt'));
		
		if($this->request->is('post')){
			if(array_key_exists('cancel',$this->request->data)){
				return $this->redirect(array('controller' => 'product_receipts', 'action' => 'kiosk_product_payments'));
				die;
			}
			//pr($this->request->data);die;
			$saleAmt = $this->request->data['sale_amount'];
			$finalAmt = $this->request->data['final_amount'];
			$upadtedAmt = $this->request->data['data']['updated_amount'];
			$updatedPayment = $this->request->data['data']['UpdatePayment'];
			if(array_key_exists('added_amount',$this->request->data)){
				$added_amount = $this->request->data['added_amount'];
			}
			//if(array_key_exists('KioskProductSale',$this->request->data)){
				if(array_key_exists('new_change_mode',$this->request->data)){
					$new_change_mode = $this->request->data['new_change_mode'];
				}
			//}
			
			$refundEntry_query = $this->ProductPayments->find('all' ,array(
																		'conditions'=> array('product_receipt_id'=> $recitId,
																							'kiosk_id' => $kioskId,
																							'status' => 2
																							),
																		'recursive' => -1
																		)
														);
			$refundEntry_query = $refundEntry_query->hydrate(false);
			if(!empty($refundEntry_query)){
				$refundEntry = $refundEntry_query->toArray();
			}else{
				$refundEntry = array();
			}
			$userId = $this->request->session()->read('Auth.User.id');
			if(!empty($refundEntry)){
				foreach($refundEntry as $key => $value){
					if($value['status'] == 2){
						$refundAmt = $value['amount'];
					}
				}
			}
			
			if(isset($refundAmt)){
				if(array_key_exists('added_amount',$this->request->data)){
					$status = $this->has_refund($refundAmt,$upadtedAmt,$finalAmt,$updatedPayment,$saleAmt,$id,$kioskId,$userId,$recitId,$new_change_mode,$added_amount);
				}else{
					$status = $this->has_refund($refundAmt,$upadtedAmt,$finalAmt,$updatedPayment,$saleAmt,$id,$kioskId,$userId,$recitId);
				}
				
			}else{
				
				if(array_key_exists('added_amount',$this->request->data)){
					$status = $this->without_refund($upadtedAmt,$finalAmt,$updatedPayment,$saleAmt,$id,$kioskId,$userId,$recitId,$new_change_mode,$added_amount);
				}else{
					$status = $this->without_refund($upadtedAmt,$finalAmt,$updatedPayment,$saleAmt,$id,$kioskId,$userId,$recitId);
				}
				
			}
			if($status){
				$this->save_log($recitId,$kioskId,$userId,$saleAmt,$id);
				$this->Flash->success("Payment Updated Successfully");
				return $this->redirect(array('controller' => 'product_receipts', 'action' => 'kiosk_product_payments'));
			}else{
				$this->Flash->error("Failed to update payment");
				return $this->redirect(array('controller' => 'product_receipts', 'action' => 'kiosk_product_payments'));
			}
			
		}
	}
	
	public function refund($id = 0) {
		//pr($this->request['data']['KioskProductSale']['id']);die;
		//pr($this->DefectiveKioskProduct->find('all'));
        
        
        $kiosk_id = $this->request->Session()->read('kiosk_id');
        if(!empty($kiosk_id)){
            $productSource = "kiosk_{$kiosk_id}_products";
            $productSalesSource = "kiosk_{$kiosk_id}_product_sales";
            $reciptTable_source = "kiosk_{$kiosk_id}_product_receipts";
			//$payment_Table_source = "kiosk_{$kiosk_id}_payment_details";
        }else{
            $productSource = "products";
            $productSalesSource = "kiosk_product_sales";
            $reciptTable_source = "product_receipts";
			//$payment_Table_source = "product_payment";
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
		
		 
        
        
        
		$this->initialize_customer();
		$kioskProductSaleId = $this->request->params['pass'][0];
		
		$product_id_query =  $salesTable->find('list',
													 array(
                                                            'valueField' => 'product_id',
															'conditions'  => array('id' => $kioskProductSaleId)
															));
        $product_id_query = $product_id_query->hydrate(false);
        if(!empty($product_id_query)){
            $product_id = $product_id_query->toArray();
        }else{
            $product_id = array();
        }
		if(empty($product_id)){
            $product_id = array(0 => null);
        }
		$products_query = $productTable->find('all' ,array(
												'fields' => array('product_code','product','image','id'),
												'conditions' => array('id IN'=> $product_id)
											)
										);
        $products_query = $products_query->hydrate(false);
        if(!empty($products_query)){
            $products = $products_query->toArray();
        }else{
            $products =array();
        }
		
		$this->set(compact('products'));
		$refundOptions = Configure::read('refund_status');
		$settingArr = $this->setting;
		$currency = $settingArr['currency_symbol'];
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		$this->set(compact('currency'));
		
		//----------Kiosk database tables--------------------  
		if((int)$kiosk_id){
			$productTable = "kiosk_{$kiosk_id}_products";
			$tableReceipt = "kiosk_{$kiosk_id}_product_receipts";
			//$this->KioskProductSale->setSource("kiosk_{$kiosk_id}_product_sales");
		}else{
			$productTable = "products";
			//$this->KioskProductSale->setSource("kiosk_product_sales");
			$tableReceipt = "product_receipts";
		}
		//----------Kiosk database tables--------------------
		
		if ($this->request->is(array('post', 'put'))) {
			$kioskDetails_query = $this->Kiosks->find('all',array('conditions'=>array('Kiosks.id'=>$kiosk_id),'fields'=>array('id','name','address_1','address_2','city','state','zip','contact','country')));
            $kioskDetails_query = $kioskDetails_query->hydrate(false);
            if(!empty($kioskDetails_query)){
                $kioskDetails = $kioskDetails_query->first();
            }else{
                $kioskDetails = array();
            }
			//pr($this->request['data']['KioskProductSale']['customer_email']);die;
			$saleId = $this->request['data']['KioskProductSale']['id'];
			$productRecptId = $this->request['data']['KioskProductSale']['product_receipt_id'];
			$discountedPrice = $this->request['data']['KioskProductSale']['sale_price']-($this->request['data']['KioskProductSale']['sale_price']*$this->request['data']['KioskProductSale']['discount']/100);
			
			
			// ENTRY WITHOUT CUSTOMER EMAIL GIVES AN INTERNAL ERROR
			 //pr($this->request);die;
			//validation for first name
			$errorArray = array();
			if(empty($this->request['data']['KioskProductSale']['fname'])){
				$errorArray[] = "Please enter customer's First Name";
			}
			if(empty($this->request['data']['KioskProductSale']['lname'])){
				$errorArray[] = "Please enter customer's Last Name";
			}
			if(empty($this->request['data']['KioskProductSale']['email'])){
				$errorArray[] = "Please enter customer's email";
			}
			////validation for mobile
			$mobile = $this->request['data']['KioskProductSale']['mobile'];
			if(strlen($mobile) < 11){
				$errorArray[] = "Mobile number must be 11 digit long"; 
			}
			if(empty($this->request['data']['KioskProductSale']['zip'])){
				$errorArray[] = "Please enter customer's Zip code"; 
			}
			
			if(empty($this->request['data']['KioskProductSale']['address_1'])){
				$errorArray[] = "Please enter Customer Address";
			}
			
			if(empty($this->request['data']['KioskProductSale']['address_2'])){
				//$errorArray[] = "Please enter customer Address 2";
			}
			
			//validation for city
			if(empty($this->request['data']['KioskProductSale']['city'])){
				$errorArray[] = "Please enter customer City";
			}
			
			if(empty($this->request['data']['KioskProductSale']['state'])){
				$errorArray[] = "Please enter customer State";
			}
			if($this->request['data']['KioskProductSale']['refund_status'] == 0){
				$errorArray[] = "Refund could not be saved. Please choose a reason for the refund";
			}
			
			if($this->request->data['KioskProductSale']['refund_price'] > $discountedPrice){
				$errorArray[] = "Refund could not be saved. Refund amount cannot be more than the actual amount";
			}
			
			if($this->request->data['KioskProductSale']['refund_price'] <= 0){
				$errorArray[] = "Refund could not be saved. Refund amount must be more than zero";
			}
			
			if(count($errorArray) == 0){
				$quantity = $this->request->data['KioskProductSale']['quantity'] - $this->request->data['KioskProductSale']['quantity_returned'];
				//updating current sale on refund in the receipt for the product returned and updating its quantity					
				$customerProductsData = array(
					'id' => $this->request->data['KioskProductSale']['id'],
					'kiosk_id' => $this->request->data['KioskProductSale']['kiosk_id'],
					'product_id' => $this->request->data['KioskProductSale']['product_id'],
					'quantity' => $quantity,
					'sale_price' => $this->request->data['KioskProductSale']['sale_price'],
					'refund_price' => '',
					'discount' => $this->request->data['KioskProductSale']['discount'],
					'discount_status' => $this->request->data['KioskProductSale']['discount_status'],
					'refund_gain' => '',
					'sold_by' => $this->request->data['KioskProductSale']['sold_by'],
					'refund_by' => '',
					'status' => 1,
					'refund_status' => 0,
					'refund_remarks' => '',
					'product_receipt_id' => $this->request->data['KioskProductSale']['product_receipt_id']
				);
				//echo $sale_id_s = (int)$this->request->data['KioskProductSale']['id'];die;
                $salesTable->behaviors()->load('Timestamp');
				$sale_entity = $salesTable->get($saleId);
				$sale_entity = $salesTable->patchEntity($sale_entity,$customerProductsData);
				if($salesTable->save($sale_entity)) {
					if($this->request->data['KioskProductSale']['refund_status'] == 2){//faulty case
						//saving the details in faulty returned products table
						$faultyReturnedData = array(
													'kiosk_id' => $this->request->data['KioskProductSale']['kiosk_id'],
													'receipt_id' => $this->request->data['KioskProductSale']['product_receipt_id'],
													'credit_by' => $this->request->session()->read('Auth.User.id'),
													'product_id' => $this->request->data['KioskProductSale']['product_id'],
													'quantity' => $this->request->data['KioskProductSale']['quantity_returned'],
													'sale_price' => $this->request->data['KioskProductSale']['sale_price'],
													'remarks' => $this->request->data['KioskProductSale']['refund_remarks'],
													'discount' => $this->request->data['KioskProductSale']['discount'],
													);
						
						$faultyTable = TableRegistry::get("faulty_products",[
                                                                                    'table' => "faulty_products",
                                                                                ]);
                        $faultyTable->behaviors()->load('Timestamp');
						$FaultyProductsEntity = $faultyTable->newEntity($faultyReturnedData,['validate' => false]);
						
						$FaultyProductsEntity = $faultyTable->patchEntity($FaultyProductsEntity,$faultyReturnedData,['validate' => false]);
						
						$faultyTable->save($FaultyProductsEntity);
						//****added on 15.03.2016 sending data to defective_kiosk_product table from here xyz
						if(empty($kioskId) && $this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER || $this->request->session()->read('Auth.User.group_id') == MANAGERS){
							$kskId = 10000;//just in case, otherwise refund is always done by kiosk
						}else{
							$kskId = $this->request->data['KioskProductSale']['kiosk_id'];
						}
						$defectiveProductData = array(
														'product_id' => $this->request->data['KioskProductSale']['product_id'],
														'quantity' => $this->request->data['KioskProductSale']['quantity_returned'],
														'kiosk_id' => $kskId,
														'user_id' => $this->Auth->user('id'),
														'status' => 0,//not moved to central_faulty_products table
														'remarks' => 1//reserved for faulty refund to customer
													);
						//not adjusting the kiosk quantity after moving to faulty as discussed by client
						$this->loadModel("DefectiveKioskProducts");
						$DefectiveKioskProductsEntity = $this->DefectiveKioskProducts->newEntity($defectiveProductData,['validate' => false]);
						$DefectiveKioskProductsEntity = $this->DefectiveKioskProducts->patchEntity($DefectiveKioskProductsEntity,$defectiveProductData,['validate' => false]);
						$this->DefectiveKioskProducts->save($DefectiveKioskProductsEntity);
						//****till here
					}
						
					$discountedPrice = $this->request['data']['KioskProductSale']['sale_price']-($this->request['data']['KioskProductSale']['sale_price']*$this->request['data']['KioskProductSale']['discount']/100);					
					$refundGain = $discountedPrice*$this->request->data['KioskProductSale']['quantity_returned']-($this->request->data['KioskProductSale']['refund_price']*$this->request->data['KioskProductSale']['quantity_returned']);
					//creating new record in the receipt for the refunded product sale
					$returnedProductsData = array(
						'kiosk_id' => $this->request->data['KioskProductSale']['kiosk_id'],
						'product_id' => $this->request->data['KioskProductSale']['product_id'],
						'quantity' => $this->request->data['KioskProductSale']['quantity_returned'],
						'sale_price' => 0,
						'refund_price' => $this->request->data['KioskProductSale']['refund_price'],
						'discount' => $this->request->data['KioskProductSale']['discount'],
						'discount_status' => $this->request->data['KioskProductSale']['discount_status'],
						'refund_gain' => $refundGain,
						'sold_by' => $this->request->data['KioskProductSale']['sold_by'],
						'refund_by' => $this->Auth->user('id'),
						'status' => 0,
						'sale_type' => 0,
						'refund_status' => $this->request->data['KioskProductSale']['refund_status'],
						'refund_remarks' => $this->request->data['KioskProductSale']['refund_remarks'],
						'product_receipt_id' => $this->request->data['KioskProductSale']['product_receipt_id']
					);			
					$salesTable->behaviors()->load('Timestamp');
					$KioskProductSaleEntity = $salesTable->newEntity($returnedProductsData,['validate' => false]);
					$KioskProductSaleEntity = $salesTable->patchEntity($KioskProductSaleEntity,$returnedProductsData,['validate' => false]);
					if($salesTable->save($KioskProductSaleEntity)){
						$kiosk_id = $this->request->data['KioskProductSale']['kiosk_id'];
						$refund_price = $this->request->data['KioskProductSale']['refund_price'];
						$qantity_returned = $this->request->data['KioskProductSale']['quantity_returned'];
						$product_id =  $this->request->data['KioskProductSale']['product_id'];
						$product_code_res_query = $this->Products->find('list',array('conditions' => array('id' => $product_id),
																					'keyField' => 'id',
																					'valueField' => 'product_code'
														  ));
						$product_code_res_query = $product_code_res_query->hydrate(false);
						if(!empty($product_code_res_query)){
							$product_code_res = $product_code_res_query->toArray();
						}else{
							$product_code_res = array();
						}
						$vat = $this->VAT;
						$vatItem = $vat/100;
						$prduct_code = $product_code_res[$product_id];
						$final_amout = $qantity_returned*$refund_price;
						$selling_price_without_vat = $final_amout/(1+$vatItem);
						$vat = $final_amout - $selling_price_without_vat;
						$data = array(
									 'quantity' =>$qantity_returned,
						             'product_code' =>$prduct_code,
						             'selling_price_withot_vat' => $selling_price_without_vat,
						             'vat' => $vat
									  );
						//pr($data);die;
						$this->insert_to_ProductSellStats($product_id,$data,$kiosk_id,$operations = '-');
						//------------------------------------------
						//Note: Saving product refund in ProductPayment
						$paymentDetailData = array(
												'kiosk_id' => $this->request->data['KioskProductSale']['kiosk_id'],
												'user_id' => $this->Auth->user('id'),
												'product_receipt_id'=> $this->request->data['KioskProductSale']['product_receipt_id'],
												'payment_method' => 'Cash',
												'description' => $this->request->data['KioskProductSale']['refund_remarks'],
												'amount' => (-1) * $this->request->data['KioskProductSale']['refund_price'],
												'product_id' => $this->request->data['KioskProductSale']['product_id'],
												'payment_status' => 1, //done
												'status' => 2,//2 means refunded
											);
						$payment_Table = TableRegistry::get("product_payments",[
																				'table' => "product_payments",
																			]);
                        $payment_Table->behaviors()->load('Timestamp');
						$paymentEntity = $payment_Table->newEntity($paymentDetailData,['validate' => false]);
						$paymentEntity = $payment_Table->patchEntity($paymentEntity,$paymentDetailData,['validate' => false]);
						if($payment_Table->save($paymentEntity)){
							//$dbo = $this->ProductPayment->getDatasource();
							//$logData = $dbo->getLog();
							//$getLog = end($logData['log']);
							//$queriesFired["PamentRefundQuery"] = $getLog['query'];
						}
						//------------------------------------------
					}
						
					$amountRefunded = $this->request->data['KioskProductSale']['refund_price']*$this->request->data['KioskProductSale']['quantity_returned'];
						
					$fname = $this->request['data']['KioskProductSale']['fname'];
					$lname = $this->request['data']['KioskProductSale']['lname'];
					$email = $this->request['data']['KioskProductSale']['email'];
					$mobile = $this->request['data']['KioskProductSale']['mobile'];
					$address_1 = $this->request['data']['KioskProductSale']['address_1'];
					$address_2 = $this->request['data']['KioskProductSale']['address_2'];
					$city = $this->request['data']['KioskProductSale']['city'];
					$state = $this->request['data']['KioskProductSale']['state'];
					$zip = $this->request['data']['KioskProductSale']['zip'];
					$created = $this->request['data']['KioskProductSale']['created'];			
					$created = date('y-m-d h:i:s',strtotime($created));
					
					
					
					$countDuplicate_query = $this->RetailCustomers->find('all', array('conditions' => array('RetailCustomers.email' => $email)));
					$countDuplicate_query = $countDuplicate_query->hydrate(false);
					if(!empty($countDuplicate_query)){
						$countDuplicate = $countDuplicate_query->first();
					}else{
						$countDuplicate = array();
					}
					
					$customer_data = array(
												'kiosk_id' =>  $kiosk_id,
												'fname' => $fname,
												'lname' => $lname,
												'mobile' => $mobile,
												'email' => $email,
												'zip' => $zip,
												'address_1' => $address_1,
												'address_2' => $address_2,
												'city' => $city,
												'state' => $state,
										   );
					
					if(count($countDuplicate) == 0){
						$retailCustomersEntity = $this->RetailCustomers->newEntity();
						$retailCustomersEntity = $this->RetailCustomers->patchEntity($retailCustomersEntity,$customer_data,['validation' => false]);
						$this->RetailCustomers->save($retailCustomersEntity);
					}else{	
						$custmor_id =  $countDuplicate["id"];
						$retailCustomersEntity = $this->RetailCustomers->get($custmor_id);
						$retailCustomersEntity = $this->RetailCustomers->patchEntity($retailCustomersEntity,$customer_data,['validate' => false]);
						$this->RetailCustomers->save($retailCustomersEntity);
					}
					
						
					//updating receipt total amount - refund case
					$update_query = "UPDATE $tableReceipt SET `bill_amount`=`bill_amount`-'$amountRefunded' ,`fname`= '$fname',`lname`='$lname',`email` = '$email',`mobile` ='$mobile',`address_1`= '$address_1',`address_2`= '$address_2',`city` = '$city',`state` = '$state',`zip` = '$zip',`created` = '$created' WHERE `id`='$productRecptId'";//die;
					 
				 $conn = ConnectionManager::get('default');
				$stmt = $conn->execute($update_query); 
				 $send_by_email = Configure::read('send_by_email');
					if(isset($this->request['data']['KioskProductSale']['email'])){
						$receiptId = $this->request['data']['KioskProductSale']['product_receipt_id'];
						//$this->ProductReceipt->setSource($tableReceipt);			
						$productReceipt_query = $reciptTable->find('all',array(
												'conditions' => array('id' => $receiptId)
																					)
																		);
						$productReceipt_query = $productReceipt_query->hydrate(false);
						if(!empty($productReceipt_query)){
							$productReceipt = $productReceipt_query->first();
						}else{
							$productReceipt = array();
						}
						if(!empty($productReceipt)){
							$customer_id = $productReceipt['customer_id'];
							if(!empty($customer_id)){
								$this->loadModel("Customers");
								$customer_data_query = $this->Customers->find('all',['conditions' => [
																								'id' => $customer_id,
																								]]);
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
						$sales_data_query = $salesTable->find('all',array(
												'conditions' => array('product_receipt_id' => $receiptId)
																					)
																		);
						$sales_data_query = $sales_data_query->hydrate(false);
						if(!empty($sales_data_query)){
							$sales_data = $sales_data_query->toArray();
						}else{
							$sales_data = array();
						}
							//added later by Rajiv on 22 october
							$productReturnArr = $productSaleArr =  $productIdArr = array();
		
							foreach($sales_data as $key => $productDetail){
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
							//---added later by Rajiv on 22 october}
							
							$vat = $this->VAT;							
							$productName_query = $this->Products->find('list', array(
																					 'keyField' => 'id',
																					 'valueField' => 'product',
																					 'fields' => array('id','product')));
							$productName_query = $productName_query->hydrate(false);
							if(!empty($productName_query)){
								$productName = $productName_query->toArray();
							}else{
								$productName = array();
							}
							$productCode_query = $this->Products->find('list', array(
																					 'keyField' => 'id',
																					 'valueField' => 'product_code',
																					 //'fields' => array('id','product')
																					 ));
							$productCode_query = $productCode_query->hydrate(false);
							if(!empty($productCode_query)){
								$productCode = $productCode_query->toArray();
							}else{
								$productCode = array();
							}
							$kiosk_query = $this->Kiosks->find('list');
							$kiosk_query = $kiosk_query->hydrate(false);
							if(!empty($kiosk_query)){
								$kiosk = $kiosk_query->toArray();
							}else{
								$kiosk = array();
							}
							$paymentDetails_query = $this->ProductPayments->find('all',array(
																 'conditions' => array('product_receipt_id' => $receiptId,'status' => 1,
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
								'currency'=> $currency,
								'refundOptions' => $refundOptions,
								'kioskDetails' => $kioskDetails,
								'customer_data' => $customer_data,
								'sales_data' => $sales_data,
								'payment_method1' => $payment_method1,
								'product_code' => $productCode,
							   ));
							//$settingArr => setings
							// $productReceipt => containing sub-arrays for [ProductReceipt], [Customer], [KioskProductSale], [PaymentDetail]
							//$refundOptions => from config e.g.: [0] => Not Refunded
							//$vat => vat e.g.: 20
							//$kiosk => all kiosk array with kiosk title and their ids
							$emailTo = $this->request['data']['KioskProductSale']['email'];
							$Email->template('refund_receipt');
							$Email->emailFormat('html');
							$Email->to($emailTo);
							$Email->transport(TRANSPORT);
							$Email->from([$send_by_email => $emailSender]);
							//$Email->sender('sales@oceanstead.co.uk','Sales Team');
							//This should be added in config file
							$Email->subject('Order Receipt');
							$Email->send();
						}
						
						if($this->request->data['KioskProductSale']['refund_status'] == 1){
							$productID = $this->request->data['KioskProductSale']['product_id'];
							$quantityReturned = $this->request->data['KioskProductSale']['quantity_returned'];
							$query = "UPDATE `$productTable` SET `quantity` = `quantity` + $quantityReturned WHERE `$productTable`.`id` = $productID";
							$conn = ConnectionManager::get('default');
							$stmt = $conn->execute($query); 
						}
					
						$this->Flash->success(__('The product has been refunded.'));
						//die("---test---");
						$print_type =  $this->setting['print_type'];
						if($print_type == 1){
							return $this->redirect(array('controller' => 'prints','action' => 'generate-receipt',$productRecptId));
						}else{
							return $this->redirect(array('action' => 'index'));	
						}
						
					}else {
						$this->Flash->error(__('The product receipt could not be saved. Please, try again.'));
					}
			}else{
				//print_r($errorArray);
				$requested_data = $this->request->data;
				$this->set('request_data',$requested_data);
				
				$this->Flash->error(implode("<br/> - ",$errorArray),['escape' => false]);
				list($sale_id,$receipt_id) = $this->request->params['pass'];
				$options = array(
				   'conditions' => array('id' => $sale_id)
				   );
				$data_query = $salesTable->find('all', $options);
				//pr($data_query);die;
				$data_query = $data_query->hydrate(false);
				if(!empty($data_query)){
					$data = $data_query->first();
				}else{
					$data = array();
				}
				
				if(!empty($data)){
					$id = $data['product_receipt_id'];
					$recipt_data_query = $reciptTable->find('all',[
											  'conditions' => [
												'id' => $id, 
											  ]
											  ]);
					$recipt_data_query = $recipt_data_query->hydrate(false);
					if(!empty($recipt_data_query)){
						$recipt_data = $recipt_data_query->toArray();
					}else{
						$recipt_data = array();
					}
					$this->set(compact('recipt_data'));
				}
				
				if(array_key_exists('customer_id',$data) &&
					$data['customer_id'] > 0){
					 $customerId = $data['customer_id'];
					 $customerEmail = "";
					 if($customerId){
						 $options = array(
						 'conditions' => array('Customer.id' => $customerId)
							  );
						 $customerInformation_query = $this->Customers->find('all',$options);
						 $customerInformation_query = $customerInformation_query->hydrate(false);
						 if(!empty($customerInformation_query)){
							 $customerInformation = $customerInformation_query->first();
						 }else{
							 $customerInformation = array();
						 }
						 $customerEmail = $customerInformation['email'];
					 }
				 }else{
					 $customerDataReceipt_query = $reciptTable->find('all',array('conditions' => array('id'=>$receipt_id),'fields'=>array('id','email')));
					 $customerDataReceipt_query = $customerDataReceipt_query->hydrate(false);
					 if(!empty($customerDataReceipt_query)){
						 $customerDataReceipt = $customerDataReceipt_query->first();
					 }else{
						 $customerDataReceipt = array();
					 }
					 $customerEmail = $customerDataReceipt['email'];
				 }
				if($data['refund_status'] != 0){
					$this->Flash->success(__('The product has been already refunded to customer.'));
					return $this->redirect(array('action' => 'index'));
				}
				$discount = $data['discount'];
				$salePrice = $data['sale_price'];
				
				if(!empty($discount)){
					$data['refund_price'] = $salePrice - (($salePrice * $discount) / 100);
				}else{
					$data['refund_price'] = $salePrice;
				}
				
				$this->request->data = $data;
				$this->set('customerEmail',$customerEmail);
				
				//else of errorArray
			}
		} else {
			//pr($this->request);
			list($sale_id,$receipt_id) = $this->request->params['pass'];
			$options = array(
			   'conditions' => array('id' => $sale_id)
			   );
			$data_query = $salesTable->find('all', $options);
			//pr($data_query);die;
            $data_query = $data_query->hydrate(false);
            if(!empty($data_query)){
                $data = $data_query->first();
            }else{
                $data = array();
            }
			if(!empty($data)){
				$id = $data['product_receipt_id'];
				$recipt_data_query = $reciptTable->find('all',[
										  'conditions' => [
											'id' => $id, 
										  ]
										  ]);
				$recipt_data_query = $recipt_data_query->hydrate(false);
				if(!empty($recipt_data_query)){
					$recipt_data = $recipt_data_query->toArray();
				}else{
					$recipt_data = array();
				}
				$this->set(compact('recipt_data'));
			}
			//if customer id, fetch from customer table, else from product receipts
			if(array_key_exists('customer_id',$data) &&
			   $data['customer_id'] > 0){
				$customerId = $data['customer_id'];
				$customerEmail = "";
				if($customerId){
					$options = array(
					'conditions' => array('Customer.id' => $customerId)
						 );
					$customerInformation_query = $this->Customers->find('all',$options);
                    $customerInformation_query = $customerInformation_query->hydrate(false);
                    if(!empty($customerInformation_query)){
                        $customerInformation = $customerInformation_query->first();
                    }else{
                        $customerInformation = array();
                    }
					$customerEmail = $customerInformation['email'];
				}
			}else{
				$customerDataReceipt_query = $reciptTable->find('all',array('conditions' => array('id'=>$receipt_id),'fields'=>array('id','email')));
                $customerDataReceipt_query = $customerDataReceipt_query->hydrate(false);
                if(!empty($customerDataReceipt_query)){
                    $customerDataReceipt = $customerDataReceipt_query->first();
                }else{
                    $customerDataReceipt = array();
                }
				$customerEmail = $customerDataReceipt['email'];
			}
			if($data['refund_status'] != 0){
				$this->Flash->success(__('The product has been already refunded to customer.'));
				return $this->redirect(array('action' => 'index'));
			}
			$discount = $data['discount'];
			$salePrice = $data['sale_price'];
			
			if(!empty($discount)){
				$data['refund_price'] = $salePrice - (($salePrice * $discount) / 100);
			}else{
				$data['refund_price'] = $salePrice;
			}
			
			$this->request->data = $data;
			
			$this->set('customerEmail',$customerEmail);
		}  
	}
	
	
	public function export(){
        
		$type = 0;
		if(array_key_exists('type',$this->request->query)){
			$type = $this->request->query['type'];
		}
		if(array_key_exists('kiosk_id',$this->request->query)){
			$kiosk_id = $this->request->query['kiosk_id'];
		}else{
			$kiosk_id = $this->request->Session()->read('kiosk_id');	
		}
        
		if($type == 1){
			$productSource = "products";
				$KioskProductSale_source = "t_kiosk_product_sales";
				$ProductReceipt_source = "t_product_receipts";
				$PaymentDetail_source = "t_payment_details";
		}else{
			if(!empty($kiosk_id)){
				if($kiosk_id == 10000){
					$productSource = "products";
					$KioskProductSale_source = "kiosk_product_sales";
					$ProductReceipt_source = "product_receipts";
					$PaymentDetail_source = "payment_details";
				}else{
					$productSource = "kiosk_{$kiosk_id}_products";
					$KioskProductSale_source = "kiosk_{$kiosk_id}_product_sales";
					$ProductReceipt_source = "kiosk_{$kiosk_id}_product_receipts";
					$PaymentDetail_source = "kiosk_{$kiosk_id}_payment_details";
				}
				
			}else{
				$productSource = "products";
				$KioskProductSale_source = "kiosk_product_sales";
				$ProductReceipt_source = "product_receipts";
				$PaymentDetail_source = "payment_details";
			}	
		}
     
        $KioskProductSaleTable = TableRegistry::get($KioskProductSale_source,[
                                                                                'table' => $KioskProductSale_source,
                                                                            ]);
        $ProductReceiptTable = TableRegistry::get($ProductReceipt_source,[
                                                                            'table' => $ProductReceipt_source,
                                                                        ]);
        $PaymentDetailTable = TableRegistry::get($PaymentDetail_source,[
                                                                            'table' => $PaymentDetail_source,
                                                                        ]);
        $productTable = TableRegistry::get($productSource,[
                                                                'table' => $productSource,
                                                            ]);
                                                                                    
        $conditionArr = array();
        //pr($this->request);die;
        if(array_key_exists('search_kw',$this->request->query)){
           // $conditionArr = $this->generate_condition_array();
        }
		
		if(array_key_exists('receipt_id',$this->request->query) && !empty($this->request->query['receipt_id'])){
			$receipt_id = $this->request->query['receipt_id'];
			$conditionArr['product_receipt_id IN'] =  $receipt_id;
		}
		
		$category_ids = "";
		if(array_key_exists('category',$this->request->query)&& !empty($this->request->query['category'][0])){
			$category_id = $this->request->query['category'];
			
			$category_ids = explode("_",$category_id);
			$ids_query = $this->Products->find('list',[
                                                'conditions' => ['category_id IN' => $category_id],
                                                'keyField' => 'id',
                                                'valueField' => 'id'
											    ]);
            $ids_query = $ids_query->hydrate(false);
            if(!empty($ids_query)){
                $ids = $ids_query->toArray();
            }else{
                $ids = array();
            }
		}
		if(array_key_exists('search_kw',$this->request->query) && !empty($this->request->query['search_kw'])){
			$searchKW = $this->request->query['search_kw'];
			$searchW = strtolower($searchKW);
            $conn = ConnectionManager::get('default');
            $stmt = $conn->execute("SELECT `id` from `products` WHERE LOWER(`product_code`) like ('%{$searchW}%') or LOWER(`product`) like ('%{$searchW}%')");
            $productResult = $stmt ->fetchAll('assoc');		
			$productIDs = array();
			
			foreach($productResult as $sngproductResult){
				$productIDs[] = $sngproductResult['id'];
			}
		}
		$p_ids = array();
		if(!empty($ids)&&!empty($productIDs)){
			$p_ids = array_merge($productIDs,$ids);
		}elseif(!empty($productIDs)){
			$p_ids = $productIDs;
		}elseif(!empty($ids)){
			$p_ids = $ids;
		}
		
		if(!empty($p_ids)){
			$conditionArr['OR']['product_id IN'] =  $p_ids;
		}
		$start_date = '';
		if(array_key_exists('start_date',$this->request->query)){
			$start_date = $this->request->query['start_date'];
			
		}
		
		$end_date = '';
		if(array_key_exists('end_date',$this->request->query)){
			$end_date = $this->request->query['end_date'];
			
		}
		
		if(array_key_exists('start_date',$this->request->query) &&
		   array_key_exists('end_date',$this->request->query) &&
		   !empty($this->request->query['start_date']) &&
		   !empty($this->request->query['end_date'])){
			$conditionArr[] = array(
						"created >=" => date('Y-m-d', strtotime($this->request->query['start_date'])),
						"created <" => date('Y-m-d', strtotime($this->request->query['end_date']. ' +1 Days')),			
					       );
		}
		
		if($type == 1){
			if($kiosk_id == 10000){
				$kiosk_id = 0;
			}
			$conditionArr['kiosk_id'] = $kiosk_id;
		}
		if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS &&
			    $this->request->session()->read('Auth.User.username')=='wholesale'){
			//$conditionArr['sale_type'] = 1;
		}else{
			//$conditionArr['sale_type'] = 0;
		}
		if($type == 0){
			$conditionArr['sale_type'] = 1;
			$conditionArr['refund_status'] = 0;
		}
		
		//pr($conditionArr);die;
        if(count($conditionArr) >= 1){
            $count_query = $KioskProductSaleTable->find('all');
                     $count = $count_query->count();
            
            $kioskProductSales_query = $KioskProductSaleTable->find('all',[
                                                                           'conditions' => $conditionArr,
                                                                           ]);
            $kioskProductSales_query = $kioskProductSales_query->hydrate(false);
            if(!empty($kioskProductSales_query)){
                $kioskProductSales = $kioskProductSales_query->toArray();
            }else{
                $kioskProductSales = array();
            }
          
        }else{
            $kioskProductSales_query = $KioskProductSaleTable->find('all');
            $kioskProductSales_query = $kioskProductSales_query->hydrate(false);
            if(!empty($kioskProductSales_query)){
                $kioskProductSales = $kioskProductSales_query->toArray();
            }else{
                $kioskProductSales = array();
            }
         
        }
		 $bul_dis_arr = $recipt_ids = array();
		 foreach($kioskProductSales as $data_key => $data_value){
			$recipt_ids[] = $data_value['product_receipt_id'];
		 }
		 
		 if(!empty($recipt_ids)){
			$res = $ProductReceiptTable->find("all",[
											  "conditions" => [
												"id IN" => $recipt_ids,
											  ]
											  ])->toArray();
			
			if(!empty($res)){
				foreach($res as $k => $v){
					$bul_dis_arr[$v->id] =$v->bulk_discount; 
				}
				
			}
			
		 }
		 
		 
		 
		$product_code_query = $this->Products->find('list',array(
										   'keyField' => 'id',
										   'valueField' => 'product_code',
										   ));
		$product_code_query = $product_code_query->hydrate(false);
		if(!empty($product_code_query)){
			$product_code = $product_code_query->toArray();
		}else{
			$product_code = array();
		}
		
		$users_res = $this->Users->find('list',[
                                            'keyField' => 'id',
                                            'valueField' => 'username'
                                           ])->toArray();
		
		$product_name = $this->Products->find('list',array(
										   'keyField' => 'id',
										   'valueField' => 'product',
										   ))->toArray();
		$users_res[0] = "--";
		foreach($kioskProductSales as $key => $value){
			if(array_key_exists($value['refund_by'],$users_res)){
				$kioskProductSales[$key]['refund_by'] = $users_res[$value['refund_by']];
			}else{
				$kioskProductSales[$key]['refund_by'] = "--";
			}
			
			$sale_price = $kioskProductSales[$key]['sale_price'];
			$dis = $kioskProductSales[$key]['discount'];
			
			$after_dis_amt = $sale_price - $sale_price * ($dis/100);
			
			$bul_dis_val = 0;
			if(array_key_exists($value['product_receipt_id'],$bul_dis_arr) && !empty($bul_dis_arr[$value['product_receipt_id']])){
					$bul_dis_val = $bul_dis_arr[$value['product_receipt_id']]."%";
			}
			
			$export_arr[$key] = array(
										"id" => $value["id"],
										"product_code" => $product_code[$value['product_id']],
										"product_name" => $product_name[$value['product_id']],
										"quantity" => $value['quantity'],
										"cost_price_per_item" => $value['cost_price'],
										"sale_price_per_item(without vat)" => $after_dis_amt,
										"refund_price" => $value['refund_price'],
										"bulk_discount" => $bul_dis_val,
										"refund_gain" => $value['refund_gain'],
										"sold_by" => $users_res[$value['sold_by']],
										"refund_by" => $users_res[$value['refund_by']],
										//"order_refund_value" => $value['order_refund_value'],
										"refund_remarks" => $value['refund_remarks'],
										"receipt_id" => $value['product_receipt_id'],
										"remarks" => $value['remarks'],
										"created" => date("d/m/y",strtotime($value['created'])),
									);
		}
		
		foreach($kioskProductSales as $key => $value){
			if(array_key_exists($value['refund_by'],$users_res)){
				$kioskProductSales[$key]['refund_by'] = $users_res[$value['refund_by']];
			}else{
				$kioskProductSales[$key]['refund_by'] = "--";
			}
			
			$sale_price = $kioskProductSales[$key]['sale_price'];
			$dis = $kioskProductSales[$key]['discount'];
			
			$after_dis_amt = $sale_price - $sale_price * ($dis/100);
			
			$kioskProductSales[$key]['sale_price'] = $after_dis_amt;
			
			$kioskProductSales[$key]['sold_by'] = $users_res[$value['sold_by']];
			$product_code_str = $product_code[$value['product_id']];
			$kioskProductSales[$key]['product_code'] = $product_code_str;
			$kioskProductSales[$key]['product_name'] = $product_name[$value['product_id']];
			$kioskProductSales[$key]['created'] = date("d/m/y",strtotime($kioskProductSales[$key]['created']));
			$kioskProductSales[$key]['discount'] = $bul_dis_arr[$kioskProductSales[$key]['product_receipt_id']];
			unset($kioskProductSales[$key]['modified']);
			unset($kioskProductSales[$key]['sale_type']);
			unset($kioskProductSales[$key]['status']);
			unset($kioskProductSales[$key]['refund_status']);
			unset($kioskProductSales[$key]['discount_status']);
			
		}

        $tmpkioskProductSales = array();
        $fileName = 'KioskProductSale_'.time().".csv";
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment;filename=' . $fileName);
        
        if(isset($export_arr['0'])){
            $fp = fopen('php://output', 'a+');
            //unset($kioskProductSales['0']['customer_id']);
            //unset($kioskProductSales['0']['kiosk_id']);
            fputcsv($fp, array_keys($export_arr['0']));
            foreach($export_arr as $key => $kioskProductSale){
                unset($kioskProductSale['customer_id']);
                unset($kioskProductSale['kiosk_id']);
               // foreach($kioskProductSale AS $values){
                    fputcsv($fp, $kioskProductSale);
                //}
            }
        }
       //$this->outputCsv('KioskProductSale_'.time().".csv" ,$tmpkioskProductSales);
       $this->autoRender = false;
    }
    
    private function generate_condition_array(){
		$start_date = $this->request->query['start_date'];
		$end_date = $this->request->query['end_date'];
		$receipt_id = $this->request->query['receipt_id'];
		$searchKW = trim(strtolower($this->request->query['search_kw']));
		$conditionArr = array();
		if(!empty($searchKW)){
			$conditionArr['OR']['LOWER(product) like '] =  strtolower("%$searchKW%");
			$conditionArr['OR']['LOWER(product_code) like '] =  strtolower("%$searchKW%");
			$conditionArr['OR']['LOWER(description) like '] =  strtolower("%$searchKW%");
		}
		$conditionArr['NOT'] =  array('quantity' => 0);
		
		if(!empty($start_date) && !empty($end_date)){
			$start = date('Y-m-d', strtotime($start_date));
			$end = date('Y-m-d', strtotime("+1 day", strtotime($end_date)));
			$conditionArr[] = array("date(created) >=" => $start,
									"date(created) <" => $end);
		}
		 
		if(!empty($receipt_id)){
			$conditionArr[] = array('product_receipt_id' => $receipt_id);
		}
		
		//----------------------
		if(array_key_exists('category',$this->request->query)){
			$category = $this->request->query['category'];
			if(isset($category)){
				$conditionArr['category_id'] =  $category;
			}
		}
		return $conditionArr;
	}
	
	public function newSaleCheckout($customerId=""){
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		if(!empty($kiosk_id)){
			$productSource = "kiosk_{$kiosk_id}_products";
		}else{
			$productSource = "products";
		}
		$productTable = TableRegistry::get($productSource,[
																	'table' => $productSource,
																]);
		if(count($this->request->Session()->read('Basket')) > 0){
			//$this->Session->write('new_sale_basket',$this->Session->read('Basket'));
		}
		$session_basket = $this->request->Session()->read('new_sale_basket');
		//pr($session_basket);die;
		$productCodeArr = array();
		$productCode = array();
		if(!empty($session_basket)){
			$product_ids = array_keys($session_basket);
			if(empty($product_ids)){
				$product_ids = array(0 => null);
			}
			$productCodeArr_query = $productTable->find('all',array('conditions'=>array('id IN'=>$product_ids),'fields'=>array('id','product_code','quantity'),'recursive'=>-1));
			$productCodeArr_query = $productCodeArr_query->hydrate(false);
			if(!empty($productCodeArr_query)){
				$productCodeArr = $productCodeArr_query->toArray();
			}else{
				$productCodeArr = array();
			}
			foreach($session_basket as $key => $basketItem){
			if($key == 'error')continue;
			//$productCodeArr[] = $this->Product->find('first',array('conditions'=>array('Product.id'=>$key),'fields'=>array('id','product_code'),'recursive'=>-1));
			}
//			pr($productCodeArr);
			if(!empty($productCodeArr)){
			 	foreach($productCodeArr as $k=>$productCodeData){
					  $productIds[$productCodeData['id']] = $productCodeData['product_code'];
					  $productCodes[$productCodeData['product_code']] = $productCodeData['quantity'];
					}
			}
		}
		
		$currencySymbol = $this->setting['currency_symbol'];
		$customerAccountDetails_query = $this->Customers->find('all',array(
									'conditions'=>array('Customers.id'=>$customerId)
									)
								);
		$customerAccountDetails_query = $customerAccountDetails_query->hydrate(false);
		if(!empty($customerAccountDetails_query)){
			$customerAccountDetails = $customerAccountDetails_query->first();
		}else{
			$customerAccountDetails = array();
		}
		$country = $customerAccountDetails['country'];
 		$vat = $this->VAT;
		if($this->request->is('post')){
			$error = array();
			if(array_key_exists('update_quantity',$this->request->data)){
				$lessProducts = array();
				$lowProducts = array();
				foreach($this->request->data['CheckOut'] as $productCode => $quantity){
						$availableQty = $productCodes[$productCode];
						if($quantity == 0 || !(int)$quantity){
								$lowProducts[] = $productCode;
						}
						if($quantity > $availableQty){
							$lessProducts[] = $productCode;
						}
						
					}
					if(count($lessProducts) >= 1){
						$this->Flash->error("Please choose ".implode(",",$lessProducts)." quantity less than or equal to available stock" );
						return $this->redirect(array('action'=>'new_sale_checkout',$customerId));
					}
					if(count($lowProducts) > 0){
						 $this->Flash->error("Please choose  more than 0 for product : ".implode(",",$lowProducts) );
						return $this->redirect(array('action'=>'new_sale_checkout',$customerId));
					}else{
						$requestedQuantity = $this->request->data['CheckOut'];
						$newArray = array();
						$counter = 0;
						$requestedQuantity = array_values($requestedQuantity);//die;
						foreach($session_basket as $productCode => $productData){
							$qty = "";
							if(array_key_exists($counter,$requestedQuantity)){
								 $qty =  $requestedQuantity[$counter];
							}
							$newArray[$productCode] = array(
							'quantity' =>  $qty   ,
							//'current_qtt' => $productData ['quantity'],
							'selling_price' => $productData['selling_price'],
							'net_amount' => $productData['net_amount'],
							'price_without_vat' => $productData['price_without_vat'],
							'remarks' => $productData['remarks'],
							'product'  => $productData['product'] ,
							'discount'  => $productData['discount'] ,
							'discount_status'  => $productData['discount_status'] ,
							'receipt_required'  => $productData['receipt_required'] ,
							'bulk_discount'  => $productData['bulk_discount'] 
							);
							$counter++;
						}
						$this->request->Session()->delete('new_sale_basket');
						if($this->request->Session()->write('new_sale_basket',$newArray)){
							$this->SessionRestore->update_session_backup_table($this->request->params['controller'], 'new_sale', 'new_sale_basket', $newArray, $kiosk_id);
						}
						$this->Flash->success("Quantity has been  successfully updated");
						return $this->redirect(array('action'=>'new_sale_checkout',$customerId));
					}
			}elseif(array_key_exists('edit_basket',$this->request->data)){
					return $this->redirect(array('action'=>"new_sale/$kiosk_id"));
			}
		}
			 
		$this->set(compact('vat','country','currencySymbol','customerId','productCode','productCodeArr','productIds'));
		// $this->set(compact('kiosks','products','productCodeArr','costArr','sellingArr','productNameArr'));
	}
    
    public function deleteProductFromSession($product_id="",$customerId = ""){
		//$this->request->Session()->delete("new_sale_basket.$product_id")
        unset($_SESSION['new_sale_basket'][$product_id]);
        if(true){
			$session_basket = $this->request->Session()->read('new_sale_basket');
			$kiosk_id = $this->request->Session()->read('kiosk_id');
			$this->SessionRestore->update_session_backup_table($this->request->params['controller'], 'new_sale', 'new_sale_basket', $session_basket, $kiosk_id);
			return $this->redirect(array('action'=>'new_sale_checkout',$customerId));
		}
	}
    
    public function drEditReceipt($receipt_id = ''){
		$CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
		if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
			//echo 'hi';die;
			$kiosk_id_to_set = $this->get_kiosk_for_dr_invoice();
			//pr($kiosk_id_to_set);die;
			if(!empty($kiosk_id_to_set)){
				
				if($kiosk_id_to_set == 10000){
					//echo'1';die;
					$productSource = "products";
					//$productSalesSource = "kiosk_product_sales";
					//$recipt_source = "product_receipts";
					//$payment_source = "payment_details";
				}else{
					//echo'2';die;
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
		$vat = $this->VAT;
		$currencySymbol = $this->setting['currency_symbol'];
		$orderDetails_query = $ProductReceiptTable->find('all',array(
																	'conditions' => array('id' => $receipt_id)
														)
													);
		$orderDetails_query = $orderDetails_query->hydrate(false);
		if(!empty($orderDetails_query)){
			$orderDetails = $orderDetails_query->first();
		}else{
			$orderDetails = array();
		}
		
		$customerId = $orderDetails['customer_id'];
		
		$customerAccountDetails_query = $this->Customers->find('all',array(
																	'conditions' => array('Customers.id' => $customerId)
																	)
														);
		$customerAccountDetails_query = $customerAccountDetails_query->hydrate(false);
		if(!empty($customerAccountDetails_query)){
			$customerAccountDetails = $customerAccountDetails_query->first();
		}else{
			$customerAccountDetails = array();
		}
		$country = $customerAccountDetails['country'];
		$products_query = $ProductTable->find('list',[
												'keyField' => 'id',
												'valueField' => 'product'
											   ]
										);
		$products_query = $products_query->hydrate(false);
		if(!empty($products_query)){
			$products = $products_query->toArray();
		}else{
			$products = array();
		}
		//deleting the already existing basket for the new entries
		//if(!empty($this->Session->read('oldBasket'))){
			$this->request->Session()->delete('oldBasket');
		//}
		
		$oldBlkDiscount = $orderDetails['bulk_discount'];
		
		$this->paginate = [
							'limit' => 50,
							//'model' => 'Product',
							'order' => ['product' => 'ASC'],
							//'recursive' => -1,
							'conditions' => ['NOT' => ['quantity' => 0]]
						  ];
		//-----------------------------------------
		$categories_query = $this->Categories->find('all',array(
								'fields' => array('id', 'category','id_name_path'),
                                                                'conditions' => array('Categories.status' => 1),
								'order' => 'Categories.category asc',
								//'recursive' => -1
								));
		$categories_query = $categories_query->hydrate(false);
		if(!empty($categories_query)){
			$categories = $categories_query->toArray();
		}else{
			$categories = array();
		}
		$categoryList = array();
		foreach($categories as $sngCat){
			$categoryList[$sngCat['id']] = $sngCat['category'];
		}
		$categories = $this->CustomOptions->category_options($categories,true);
		
		//-----------------------------------------
		
		//receipt for the added products
		$bulkDiscountSession = $this->request->Session()->read('BulkDiscount');
		$session_basket = $this->request->Session()->read('Basket');
		//pr($session_basket);die;
		if(is_array($session_basket)){
			$productCodeArr = array();
			foreach($session_basket as $key => $basketItem){
				if($key == 'error')continue;
				$productCodeArr_query = $ProductTable->find('all',array('conditions'=>array('id'=>$key),'fields'=>array('id','product_code')));
				$productCodeArr_query = $productCodeArr_query->hydrate(false);
				if(!empty($productCodeArr_query)){
					$productCodeArr[] = $productCodeArr_query->first();
				}
			}
			$productCode = array();
			if(!empty($productCodeArr)){
				foreach($productCodeArr as $k=>$productCodeData){
					$productCode[$productCodeData['id']]=$productCodeData['product_code'];
				}
			}
			$basketStr = "";
			$counter = 0;
			$totalBillingAmount = 0;
			$totalDiscountAmount = 0;
			$sub_total = $vatAmount = 0;
			///pr($session_basket);
			foreach($session_basket as $key => $basketItem){
				if($key == 'error')continue;
				$counter++;
				$vat = $this->VAT;
				$vatItem = $vat/100;
				$discount = $basketItem['discount'];				
				$sellingPrice = $basketItem['selling_price'];
				$net_amount = $basketItem['net_amount'];
				$itemPrice = $basketItem['selling_price']/(1+$vatItem);
				$price_without_vat = $basketItem['price_without_vat'];
				//added on Aug 1, 2016
				//$discountAmount = round($sellingPrice*$basketItem['discount']/100* $basketItem['quantity'],2);
				$discountAmount = $price_without_vat * $basketItem['discount']/100 * $basketItem['quantity'];
				//modified on Aug 1, 2016
				$totalDiscountAmount+= $discountAmount;
				//$totalItemPrice = round($basketItem['selling_price'] * $basketItem['quantity'],2);
				$totalItemPrice = $price_without_vat * $basketItem['quantity'];
				//modified on Aug 1, 2016
				$bulkDiscountPercentage = $bulkDiscountSession;
				$totalItemCost = round($totalItemPrice-$discountAmount,2);
				$totalItemCost = $totalItemPrice - $discountAmount;
				$totalBillingAmount+=$totalItemCost;
				$vatperitem = $basketItem['quantity']*($sellingPrice-$itemPrice);
				$vatAmount+= $vatperitem;
				$bulkDiscountValue = $totalBillingAmount*$bulkDiscountPercentage/100;
				$netBillingAmount = $totalBillingAmount-$bulkDiscountValue;
				//$netPrice = round($netBillingAmount/(1+$vatItem),2);
				$netPrice = $netBillingAmount;
				//modified on Aug 1, 2016
				//$vatAmount = round($netBillingAmount-$netPrice,2);
				$vatAmount = $netBillingAmount*$vatItem;
				//modified on Aug 1, 2016
				
				$finalAmount = $netBillingAmount;
				if($country == "OTH"){
					//$finalAmount = $netPrice;
				}else{
					//$finalAmount = $netBillingAmount+$vatAmount;
				}
				
				$sub_total = $sub_total + $totalItemCost;
				
				//modified on Aug 1, 2016. This can be conditional
				$basketStr.="<tr>
						<td>{$counter})</td>
						<td>{$productCode[$key]}</td>
						<td>".$basketItem['product']."</td>
						<td>".$basketItem['quantity']."</td>
						<td>".$CURRENCY_TYPE.number_format($price_without_vat,2)."</td>
						<td> ".$discount."</td>
						<td>".$CURRENCY_TYPE.number_format($net_amount,2)."</td>
						<td>".$CURRENCY_TYPE.number_format($totalItemCost,2)."</td></tr>";
			}
			if(!empty($basketStr)){
				$basketStr = "<table><tr>
							<th style='width: 10px;'>Sr No</th>
							<th style='width: 132px;'>Product Code</th>
							<th style='width: 445px;'>Product</th>
							<th style='width: 30px;'>Qty</th>
							<th style='width: 99px;'>Sale Price</th>
							<th style='width: 40px;'>Disct %</th>
							<th style='width: 10px;'>Disct Value</th>
							<th style='width: 10px;'>Amount</th>
							</tr>".$basketStr."
							<tr><td colspan='7'>Sub Total</td><td>".$CURRENCY_TYPE.number_format($sub_total,2)."</td></tr>
							<tr><td colspan='7'>Bulk Discount ({$bulkDiscountPercentage}%)</td><td>".$CURRENCY_TYPE.number_format($bulkDiscountValue,2)."</td></tr>
							<tr><td colspan='7'>Sub Total(after bulk discount)</td><td>".$CURRENCY_TYPE.number_format($netBillingAmount,2)."</td></tr>
							
							<tr><td colspan='7'>Net Amount</td><td>".$CURRENCY_TYPE.number_format($netPrice,2)."</td></tr>
							<tr><td colspan='7'>Total Amount</td><td>".$CURRENCY_TYPE.number_format($finalAmount,2)."</td></tr></table>";
							
							
				$productCounts = count($this->request->Session()->read('Basket'));
			
				if($productCounts){
					//$productCounts product(s) added to the cart.
					$flashMessage = "Total item Count:$productCounts.<br/>$basketStr";
				}else{
					$flashMessage = "No item added to the cart. Item Count:$productCounts";
				}
				
				if(array_key_exists('error',$session_basket)){
					$flashMessage = $session_basket['error']."<br/>".$flashMessage;
				}
				$this->Flash->error($flashMessage,['escape' => false]);
			}
		}
		//-----------------------------------------
		$this->set(compact('categories','customerAccountDetails','orderDetails','oldBlkDiscount', 'categoryList', 'vat'));
		$this->set('products', $this->paginate($ProductTable));
	}
	
	public function drSearchEditReceipt($orderId = '',$keyword = ''){
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
				//$this->KioskProductSale->setSource($productSalesSource);
				//
				//$this->ProductReceipt->setSource($recipt_source);
				//
				//$this->PaymentDetail->setSource($payment_source);
				
			}
		}else{
            $kiosk_id = $this->request->Session()->read('kiosk_id');            
            $productSource = "kiosk_{$kiosk_id}_products";
        }
        $ProductTable = TableRegistry::get($productSource,[
																'table' => $productSource,
															]);
		
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
		
		$vat = $this->VAT;
		$orderDetails_query = $ProductReceiptTable->find('all',array(
																	'conditions' => array('id' => $orderId)
															)
													);
		$orderDetails_query = $orderDetails_query->hydrate(false);
		if(!empty($orderDetails_query)){
			$orderDetails = $orderDetails_query->first();
		}else{
			$orderDetails = array();
		}
		$oldBlkDiscount = $orderDetails['bulk_discount'];
		$customerId = $orderDetails['customer_id'];
		
		$customerAccountDetails_query = $this->Customers->find('all',array(
																'conditions' => array('Customers.id' => $customerId)
														)
													);
		$customerAccountDetails_query = $customerAccountDetails_query->hydrate(false);
		if(!empty($customerAccountDetails_query)){
			$customerAccountDetails = $customerAccountDetails_query->first();
		}else{
			$customerAccountDetails = array();
		}
		$searchKW = $this->request->query['search_kw'];		
		$categories_query = $this->Categories->find('all',array(
															'fields' => array('id', 'category','id_name_path'),
                                                            'conditions' => array('Categories.status' => 1),
															'order' => 'Categories.category asc',
															//'recursive' => -1
														)
											);
		$categories_query = $categories_query->hydrate(false);
		if(!empty($categories_query)){
			$categories = $categories_query->toArray();
		}else{
			$categories = array();
		}
		$categoryList = array();
		foreach($categories as $sngCat){
			$categoryList[$sngCat['id']] = $sngCat['category'];
		}
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
				$conditionArr['category_id'] =  $category;
			}
		}
		
		$this->paginate = [
							'conditions' => $conditionArr,
							'limit' => 50,
							//'recursive' => -1
						];
		$categories = $this->CustomOptions->category_options($categories,true);
		$products = $this->paginate($ProductTable);
		$this->set(compact('products','categories','customerAccountDetails','orderDetails','oldBlkDiscount', 'categoryList', 'vat'));
		//$this->layout = 'default'; 
		//$this->viewPath = 'Products';
		$this->render("dr_edit_receipt");
	}
	
	public function drSaveUpdatedReceipt($orderId = ''){
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
				
				
				
				//$this->KioskProductSale->setSource($productSalesSource);
				//
				//$this->ProductReceipt->setSource($recipt_source);
				//
				//$this->PaymentDetail->setSource($payment_source);
				
			}
		}else{
            $kisk_id = $this->request->Session()->read('kiosk_id');
            if($kisk_id == 10000){
					$productSource = "products";
				}else{
					$productSource = "kiosk_{$kisk_id}_products";
				}
        }
		
        
        $ProductTable = TableRegistry::get($productSource,[
																'table' => $productSource,
															]);
        
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
		
		//pr($_SESSION);die;
		//pr($this->request);die;
		$vat = $this->VAT;
		$currencySymbol = $this->setting['currency_symbol'];
		if(array_key_exists('receiptId',$this->request['data'])){
			$orderId = $this->request['data']['receiptId'];
		}
		$orderDetails_query = $ProductReceiptTable->find('all',array(
							'conditions'=>array('id' => $orderId)
					)
				);
		$orderDetails_query = $orderDetails_query->hydrate(false);
		if(!empty($orderDetails_query)){
			$orderDetails = $orderDetails_query->first();
		}else{
			$orderDetails = array();
		}
		//Start block: cron dashboard code
		$_SESSION['amount_changed'] = $orderDetails['orig_bill_amount'];
		//End block: cron dashboard code
		
		//pr($orderDetails);die;
		$originalSaleDate = $orderDetails['created'];
		$customerId = $orderDetails['customer_id'];
		
		$customerAccountDetails_query = $this->Customers->find('all',array(
									'conditions'=>array('Customers.id' => $customerId)
									)
								);
		$customerAccountDetails_query = $customerAccountDetails_query->hydrate(false);
		if(!empty($customerAccountDetails_query)){
			$customerAccountDetails = $customerAccountDetails_query->first();
		}else{
			$customerAccountDetails = array();
		}
		if(!empty($customerAccountDetails)){
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
		}
		//----------Kiosk database tables--------------------
		if(isset($kiosk_id) && !empty($kiosk_id)){
			$receiptTable = "kiosk_{$kiosk_id}_product_receipts";
			$salesTable = "kiosk_{$kiosk_id}_product_sales";
			$productTable = "kiosk_{$kiosk_id}_products";
		}else{
			$kiosk_id = 0;
			$receiptTable = "product_receipts";
			$salesTable = "kiosk_product_sales";
			$productTable = "products";
		}
		//----------Kiosk database tables--------------------
		$ProductTable = TableRegistry::get($productTable,[
																'table' => $productTable,
															]);
		
		$user_id = $this->Auth->user('id');	//rasa
		//$this->initialize_tables($kiosk_id);
		$current_page = '';
		if(array_key_exists('current_page',$this->request['data'])){
			$current_page = $this->request['data']['current_page'];		
		}
		//if(empty($current_page)){$this->redirect(array('action' => "edit_receipt/$orderId"));}		
		$productCounts = 0;
		$session_basket = $this->request->Session()->read('Basket');
		
		//--------------------------
		//pr($_SESSION);
		//pr($this->request['data']);die;
		//pr($this->request);die;
		if(array_key_exists('basket',$this->request['data'])){
			//echo'hi';die;
			$productArr = array();
			$bulkDiscountSession = 0;
			$bulkDiscount = 0;
			$vat = $this->VAT;
			if(array_key_exists('bulk_discount',$this->request['data'])){
				$bulkDiscount = $this->request['data']['bulk_discount'];
				$this->request->Session()->write('BulkDiscount', $bulkDiscount);
				$bulkDiscountSession = $this->request->Session()->read('BulkDiscount');
			}
			
			$receipt_required = $this->request['data']['receipt_required'];
			$this->request->Session()->write('receipt_required', $receipt_required);
			$receiptRequiredSession = $this->request->Session()->read('receipt_required');
			//pr($_SESSION);die;
			//pr($this->request);die;
			foreach($this->request['data']['KioskProductSale']['item'] as $key => $item){				
				if((int)$item){
					$discount = $this->request['data']['KioskProductSale']['discount'][$key];					
					$price = $this->request['data']['KioskProductSale']['selling_price'][$key];
					$discountStatus = $this->request['data']['KioskProductSale']['discount_status'][$key];
					$currentQuantity = $this->request['data']['KioskProductSale']['p_quantity'][$key];
					$productID = $this->request['data']['KioskProductSale']['product_id'][$key];
					$productTitle = $this->request['data']['KioskProductSale']['product'][$key];
					$quantity = $this->request['data']['KioskProductSale']['quantity'][$key];
					$netAmount = $this->request['data']['KioskProductSale']['net_amount'][$key];
					//$productCode = $this->request['data']['KioskProductSale']['product_code'][$key]; //rasu
					$priceWithoutVat = $this->request['data']['KioskProductSale']['price_without_vat'][$key];
					/*
					if(array_key_exists('minimum_discount', $this->request['data']['KioskProductSale'])){
						$minPrice = $this->request['data']['KioskProductSale']['minimum_discount'][$key];
					}
					repeated in the bottom same code again
					*/
					if(empty($netAmount)){$netAmount = $priceWithoutVat;}
					if($netAmount >= $priceWithoutVat){
						//$price = $netAmount+$netAmount*($vat/100);
						$priceWithoutVat = $netAmount;
					}
					//--------------------------
					$priceCheck_query = $ProductTable->find('all',array(
																		'conditions' => array('id' => $productID),
																		'fields' => array('selling_price','product'),
																		//'recursive' => -1
																	)
														);
					$priceCheck_query = $priceCheck_query->hydrate(false);
					if(!empty($priceCheck_query)){
						$priceCheck = $priceCheck_query->first();
					}else{
						$priceCheck = array();
					}
					$originalPrice = $priceCheck['selling_price'];echo "</br>";
				    $discountValue = $originalPrice * $discount/100;echo "</br>";
				    $minPrice = round($originalPrice - $discountValue, 2);echo "</br>";
					//e $minPrice;echo "</br>";die;
					//----------------------------------
					if(array_key_exists('minimum_discount', $this->request['data']['KioskProductSale'])){
						if(array_key_exists($key,$this->request['data']['KioskProductSale']['minimum_discount'])){
							$minPrice = $this->request['data']['KioskProductSale']['minimum_discount'][$key];
						}
					}
					//----------------------------------
					
					if($netAmount != $priceWithoutVat && $netAmount < $minPrice){
						//echo "$netAmount != $priceWithoutVat && $netAmount < $minPrice";die;
						$flashMessage = "Selling price cannot be less than the minimum allowed price[$netAmount != $priceWithoutVat && $netAmount < $minPrice]";
						$this->Flash->error($flashMessage);
						$this->redirect(array('action' => "dr_edit_receipt/$orderId/page:$current_page"));
						die;
					}
				}
				
				
				if((int)$item && $quantity <= $currentQuantity){
					$productArr[$productID] = array(
									'quantity' => $quantity,
									'selling_price' => $price,
									'net_amount' => $netAmount,//new added
									'price_without_vat' => $priceWithoutVat, //new added
									'product' => $productTitle,
									'discount' => $discount,
									'discount_status' => $discountStatus,
									'receipt_required' => $this->request['data']['receipt_required'],
									'bulk_discount' => $bulkDiscount
									);
					$productCounts++;
				}				
			}
			//pr($productArr);die;
			//pr($session_basket);die;
			// TEMPORARILY COMMENTED $session_old_basket = $this->Session->read('oldBasket');
			$session_basket = $this->request->Session()->read('Basket');
			
			if(count($session_basket) >= 1){
				//adding item to the the existing session
				$sum_total = $this->add_arrays(array($productArr,$session_basket));
				$this->request->Session()->write('Basket', $sum_total);
				$session_basket = $this->request->Session()->read('Basket');
				//pr($session_basket);die;
			}else{
				//adding old basket and the first item to the session
				// TEMPORARILY COMMENTED $productArr = $this->add_arrays(array($productArr,$session_old_basket));
				if(count($productCounts))$this->request->Session()->write('Basket', $productArr);
			}
			//pr($_SESSION);die;
			//pr($this->request);die;
			$basketStr = "";
			$counter = 0;
			$totalBillingAmount = 0;
			$totalDiscountAmount = 0;
			$vatAmount = 0;
			if(is_array($session_basket)){
				$productCodeArr = array();
				foreach($session_basket as $key => $basketItem){
					if($key == 'error')continue;
					$productCodeArr_query = $ProductTable->find('all',array('conditions'=>array('id'=>$key),'fields'=>array('id','product_code')));
					$productCodeArr_query = $productCodeArr_query->hydrate(false);
					if(!empty($productCodeArr_query)){
						$productCodeArr[] = $productCodeArr_query->first();
					}
				}
				$productCode = array();
				if(!empty($productCodeArr)){
					foreach($productCodeArr as $k=>$productCodeData){
						$productCode[$productCodeData['id']]=$productCodeData['product_code'];
					}
				}
				//pr($country);die;
				foreach($session_basket as $key => $basketItem){
					
					$counter++;
					$vatItem = $vat/100;
					$discount = $basketItem['discount'];				
					$sellingPrice = $basketItem['selling_price'];
					$itemPrice = round($basketItem['selling_price']/(1+$vatItem),2);
					$discountAmount = round($sellingPrice*$basketItem['discount']/100* $basketItem['quantity'],2);
					$totalDiscountAmount+= $discountAmount;
					$totalItemPrice = round($basketItem['selling_price'] * $basketItem['quantity'],2);				
					$bulkDiscountPercentage = $bulkDiscountSession;
					$totalItemCost = round($totalItemPrice-$discountAmount,2);
					$totalBillingAmount+=$totalItemCost;
					$vatperitem = round($basketItem['quantity']*($sellingPrice-$itemPrice),2);
					$bulkDiscountValue = round($totalBillingAmount*$bulkDiscountPercentage/100,2);
					$netBillingAmount = round($totalBillingAmount-$bulkDiscountValue,2);
				    $netPrice = round($netBillingAmount/(1+$vatItem),2);
					$vatAmount = round($netBillingAmount-$netPrice,2);
					
					if($country=="OTH"){
						$finalAmount = $netBillingAmount;
					}else{
						//$finalAmount = $netPrice;
						$finalAmount = $netBillingAmount;
					}
					//echo $finalAmount;die;
					$basketStr.="<tr>
						<td>{$counter})</td>
						<td>{$productCode[$key]}</td>
						<td>".$basketItem['product']."</td>
						<td>".$basketItem['quantity']."</td>
						<td>".$currencySymbol.number_format($sellingPrice,2)."</td>
						<td> ".$discount."</td>
						<td>".$currencySymbol.number_format($discountAmount,2)."</td>
						<td>".$currencySymbol.number_format($totalItemCost,2)."</td></tr>";
				}
				
			}
			
			if(!empty($basketStr)){
				$basketStr = "<table><tr>
							<th style='width: 10px;'>Sr No</th>
							<th style='width: 132px;'>Product Code</th>
							<th style='width: 445px;'>Product</th>
							<th style='width: 30px;'>Qty</th>
							<th style='width: 99px;'>Price/Item</th>
							<th style='width: 40px;'>Disct %</th>
							<th style='width: 10px;'>Disct Value</th>
							<th style='width: 10px;'>Gross</th>
							</tr>".$basketStr."
							<tr><td colspan='7'>Bulk Discount ({$bulkDiscountPercentage}%)</td><td>".$currencySymbol.number_format($bulkDiscountValue,2)."</td></tr>
							<tr><td colspan='7'>Sub Total</td><td>".$currencySymbol.number_format($netBillingAmount,2)."</td></tr>
							
							<tr><td colspan='7'>Net Amount</td><td>".$currencySymbol.number_format($netPrice,2)."</td></tr>
							<tr><td colspan='7'>Total Amount</td><td>".$currencySymbol.number_format($finalAmount,2)."</td></tr></table>";
			}
		
			$totalItems = count($this->request->Session()->read('Basket'));
			//pr($totalItems);die;
			if($totalItems){
				//$productCounts product(s) added to the cart.
				$flashMessage = "Total item Count:$totalItems.<br/>$basketStr";
			}else{
				$flashMessage = "No item added to the cart. Item Count:$productCounts";
				$this->Flash->error($flashMessage,['escape'=>false]);
			}
			
			
			return $this->redirect(array('action' => "dr_edit_receipt/$orderId/page:$current_page"));
		
		}elseif(array_key_exists('empty_basket',$this->request['data'])){
			$this->request->Session()->delete('Basket');
			$this->request->Session()->delete('BulkDiscount');
			$this->request->Session()->delete('receipt_required');
			$flashMessage = "Basket is empty; Add new items to cart!";
			$this->Flash->success($flashMessage);
			return $this->redirect(array('action' => "dr_edit_receipt/$orderId/page:$current_page"));			
		}elseif(array_key_exists('calculate',$this->request['data'])){
			return $this->redirect(array('action' => "dr_edit_receipt/$orderId/page:$current_page"));
		}elseif(array_key_exists('check_out',$this->request['data'])){
			return $this->redirect(array('action'=>'dr_edit_receipt_checkout',$orderId));
			
		}else{
			$paymentDetail_query = $PaymentDetailTable->find('all',array(
									'conditions'=>array('product_receipt_id'=>$orderId)
									)
								    );
			$paymentDetail_query = $paymentDetail_query->hydrate(false);
			if(!empty($paymentDetail_query)){
				$paymentDetail = $paymentDetail_query->toArray();
			}else{
				$paymentDetail = array();
			}
			//pr($paymentDetail);die;
			$customer_id = $customerAccountDetails['id'];
			$productArr = array();
			$bulkDiscountSession = 0;
			//--------code for reading cake query---
			//$dbo = $this->Receipt->getDatasource();
			//$logData = $dbo->getLog();
			//$getLog = end($logData['log']);
			//echo $getLog['query'];
			//---------------------Step 1 code -------------------------------
			
			//---------------------Step 2 code -------------------------------
			$saleId = $orderId;
			$session_basket = $this->request->Session()->read('Basket');
			// NORMAL SUBMIT CASE OTHER THAN BASKET
			//pr($this->request['data']);die;
			if(array_key_exists('KioskProductSale',$this->request['data']))
			foreach($this->request['data']['KioskProductSale']['item'] as $key => $item){
				if((int)$item){
					$currentQuantity = $this->request['data']['KioskProductSale']['p_quantity'][$key];
					$productID = $this->request['data']['KioskProductSale']['product_id'][$key];
					$productTitle = $this->request['data']['KioskProductSale']['product'][$key];
					$discount = $this->request['data']['KioskProductSale']['discount'][$key];
					$selling_price = $this->request['data']['KioskProductSale']['selling_price'][$key];
					$discountStatus = $this->request['data']['KioskProductSale']['discount_status'][$key];
					$quantity = $this->request['data']['KioskProductSale']['quantity'][$key];
					$price_without_vat1 = $this->request['data']['KioskProductSale']['price_without_vat'][$key];
					$netAmt =  $this->request['data']['KioskProductSale']['net_amount'][$key];
					if($netAmt > $price_without_vat1){
						$selling_price = $netAmt+ $netAmt*($vat/100);
						$price_without_vat1 = $netAmt;
					}
				
				
					if(empty($netAmt)){$netAmt = $price_without_vat1;}
						if($netAmt >= $price_without_vat1){
							//$price = $netAmount+$netAmount*($vat/100);
							$price_without_vat1 = $netAmt;
						}
					
					
					$bulkDiscountPercentage = 0;
					$bulkDiscountSession = 0;
					
					if(array_key_exists('bulk_discount',$this->request['data'])){
						$bulkDiscountPercentage = $this->request['data']['bulk_discount'];
						if($bulkDiscountPercentage>100){
							$flashMessage = "Bulk discount percentage must be less than 100";
							$this->Flash->error($flashMessage);
							$this->redirect(array('action' => "dr_edit_receipt/$orderId/page:$current_page"));
							die;
						}elseif($bulkDiscountPercentage<0){
							$flashMessage = "Bulk discount percentage must be a positive number";
							$this->Flash->error($flashMessage);
							$this->redirect(array('action' => "dr_edit_receipt/$orderId/page:$current_page"));
							die;
						}
						
						$this->request->Session()->write('BulkDiscount', $bulkDiscountPercentage);
						$bulkDiscountSession = $this->request->Session()->read('BulkDiscount');
					}
					
					if((int)$item && $quantity <= $currentQuantity){
						$productArr[$productID] = array(
										'quantity' => $quantity,
										'selling_price' => $selling_price,
										'net_amount' => $netAmt,//new added
										'product' => $productTitle,
										'discount' => $discount,
										'discount_status' => $discountStatus,
										'bulk_discount' => $bulkDiscountPercentage,
										'price_without_vat' => $price_without_vat1,
										);
						$productCounts++;
					}
				}
			}
			
			if(empty($bulkDiscountSession)){
				//pr($_SESSION['BulkDiscount']);die;
				if(array_key_exists('BulkDiscount',$_SESSION)){
					$bulkDiscountSession = $_SESSION['BulkDiscount'];
				}
			}
			
			$sum_total = $this->add_arrays(array($productArr,$session_basket));
			$this->request->Session()->write('Basket', $sum_total);
				$session_basket = $this->request->Session()->read('Basket');
			if(empty($sum_total)){
				$flashMessage = "Failed to create order. <br />Please select quantity atleast for one product!";
				$this->Flash->error($flashMessage,['escape'=>false]);
				$redirectTo = array('action' => "dr_edit_receipt/$orderId/page:$current_page");
				if(!isset($orderId)){$orderId = 0;}
				$this->rollback_sale($orderId, $kiosk_id, 'save_updated_receipt', $redirectTo);
				return $this->redirect($redirectTo);
				die;
			}
			
			$datetime = date('Y-m-d H:i:s');
			
			$billingAmount = 0;
				
			$date = date("d/m/Y", $_SERVER['REQUEST_TIME']);
			$receiptRequired = '';
			
			if(isset($this->request['data']['receipt_required'])){
				$receipt_required = $this->request['data']['receipt_required'];
				$this->request->Session()->write('receipt_required', $receipt_required);
				$receiptRequiredSession = $this->request->Session()->read('receipt_required');	
			}else{
				$receiptRequiredSession = $this->request->Session()->read('receipt_required');
			}
			
			//-----------------------------------------------------------------------------------------
			
			//die;
			$basketStr = "";
			$counter = 0;
			$totalBillingAmount = 0;
			$totalDiscountAmount = 0;
			$vatAmount = 0;
			//pr($sum_total);die;
			if(is_array($sum_total)){
				//pr($sum_total);
				$productCodeArr = array();
				foreach($sum_total as $key => $basketItem){
					if($key == 'error')continue;
					$productCodeArr_query = $ProductTable->find('all',array('conditions'=>array('id'=>$key),'fields'=>array('id','product_code')));
					$productCodeArr_query = $productCodeArr_query->hydrate(false);
					if(!empty($productCodeArr_query)){
						$productCodeArr[] = $productCodeArr_query->first();
					}
				}
				$productCode = array();
				if(!empty($productCodeArr)){
					foreach($productCodeArr as $k=>$productCodeData){
						$productCode[$productCodeData['id']]=$productCodeData['product_code'];
					}
				}
			//	pr($sum_total);die;
			$sub_total = 0;
				foreach($sum_total as $key => $basketItem){
					$counter++;
					$vat = $this->VAT;
					$vatItem = $vat/100;
					$discount = $basketItem['discount'];				
					$sellingPrice = $basketItem['selling_price'];
					$price_without_vat = $basketItem['price_without_vat'];
					$net_amount = $basketItem['net_amount'];
					$itemPrice = round($basketItem['selling_price']/(1+$vatItem),2);
					//$discountAmount = round($sellingPrice*$basketItem['discount']/100* $basketItem['quantity'],2);
					$discountAmount = $price_without_vat * $basketItem['discount']/100 * $basketItem['quantity'];
					$totalDiscountAmount+= $discountAmount;
					//$totalItemPrice = round($basketItem['selling_price'] * $basketItem['quantity'],2);
					$totalItemPrice = $price_without_vat * $basketItem['quantity'];
					$bulkDiscountPercentage = $bulkDiscountSession;
					$totalItemCost = round($totalItemPrice-$discountAmount,2);
					$totalBillingAmount+=$totalItemCost;
					$vatperitem = round($basketItem['quantity']*($sellingPrice-$itemPrice),2);
					$bulkDiscountValue = round($totalBillingAmount*$bulkDiscountPercentage/100,2);
					$netBillingAmount = round($totalBillingAmount-$bulkDiscountValue,2);
					//$netPrice = round($netBillingAmount/(1+$vatItem),2);
					$netPrice = $netBillingAmount;
					//$vatAmount = round($netBillingAmount-$netPrice,2);
					$vatAmount = $netBillingAmount*$vatItem;
					$finalAmount = $netBillingAmount;
					if($country=="OTH"){
						//$finalAmount = $netPrice;
					}else{
						//$finalAmount = $netBillingAmount+$vatAmount;
					}
					
					$sub_total = $sub_total + $totalItemCost;
					
					$basketStr.="<tr>
						<td>{$counter})</td>
						<td>{$productCode[$key]}</td>
						<td>".$basketItem['product']."</td>
						<td>".$basketItem['quantity']."</td>
						<td>".$CURRENCY_TYPE.number_format($price_without_vat,2)."</td>
						<td> ".$discount."</td>
						<td>".$CURRENCY_TYPE.number_format($net_amount,2)."</td>
						<td>".$CURRENCY_TYPE.number_format($totalItemCost,2)."</td></tr>";
				}
			}
			if(!empty($basketStr)){
				$basketStr = "<table><tr>
							<th style='width: 10px;'>Sr No</th>
							<th style='width: 132px;'>Product Code</th>
							<th style='width: 445px;'>Product</th>
							<th style='width: 30px;'>Qty</th>
							<th style='width: 99px;'>Sale Price</th>
							<th style='width: 40px;'>Disct %</th>
							<th style='width: 10px;'>Disct Value</th>
							<th style='width: 10px;'>Amount</th>
							</tr>".$basketStr."
							<tr><td colspan='7'>Sub Total</td><td>".$CURRENCY_TYPE.number_format($sub_total,2)."</td></tr>
							<tr><td colspan='7'>Bulk Discount ({$bulkDiscountPercentage}%)</td><td>".$CURRENCY_TYPE.number_format($bulkDiscountValue,2)."</td></tr>
							<tr><td colspan='7'>Sub Total(after bulk discount)</td><td>".$CURRENCY_TYPE.number_format($netBillingAmount,2)."</td></tr>
							<tr><td colspan='7'>Vat</td><td>".$CURRENCY_TYPE.number_format($vatAmount,2)."</td></tr>
							<tr><td colspan='7'>Net Amount</td><td>".$CURRENCY_TYPE.number_format($netPrice,2)."</td></tr>
							<tr><td colspan='7'>Total Amount</td><td>".$CURRENCY_TYPE.number_format($finalAmount,2)."</td></tr></table>";
					
			}
			$totalItems = count($this->request->Session()->read('Basket'));
			//-------------------------------------------------------------
			$this->request->Session()->write('finalAmount', $finalAmount);
			$flashMessage = "Please review the details and make payment<br/>$basketStr";
			$this->Flash->error($flashMessage,['escape'=>false]);
			if(empty($orderDetails['bulk_discount'])){
				$orderDetails['bulk_discount'] = 0;
			}
			if(empty($bulkDiscountPercentage)){
				$bulkDiscountPercentage = 0;
			}
            
            
			if($orderDetails['bulk_discount'] != $bulkDiscountPercentage){
				return $this->redirect(array('controller'=>'kiosk_product_sales','action' => "dr_adjust_payment", $orderId));
			}else{
				return $this->redirect(array('controller'=>'product_receipts','action' => "dr_make_payment", $orderId));
			}
			
		}
	}
	
	public function drSaveInvoiceEditDetail($saleId = ''){
		$CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
		$kisk_id = $this->request->Session()->read('kiosk_id');
		$kiosk_id = 0;
		if((int)$kisk_id){
			$kiosk_id = $this->request->Session()->read('kiosk_id');
		}
		
		if(!empty($kiosk_id)){
			$salesTable = "t_kiosk_product_sales";
			$productreceiptsTable = "t_product_receipts";
			$productsTable = "kiosk_{$kiosk_id}_products";
		}else{
			$productsTable = "products";
			$salesTable = "t_kiosk_product_sales";
			$productreceiptsTable = "t_product_receipts";
			
			$kiosk_id_to_set = $this->get_kiosk_for_dr_invoice();
			if(!empty($kiosk_id_to_set)){
				if($kiosk_id_to_set == 10000){
					$productsTable = "products";
					//$salesTable = "kiosk_product_sales";
					//$productreceiptsTable = "product_receipts";
					//$payment_source = "payment_details";
				}else{
					$kiosk_id = $kiosk_id_to_set;
					$productsTable = "kiosk_{$kiosk_id}_products";
					//$salesTable = "kiosk_{$kiosk_id}_product_sales";
					//$productreceiptsTable = "kiosk_{$kiosk_id}_product_receipts";
					//$payment_source = "kiosk_{$kiosk_id}_payment_details";
				}
				
				$ProductTable = TableRegistry::get($productsTable,[
																'table' => $productsTable,
															]);
				
				//$this->KioskProductSale->setSource($salesTable);
				//
				//$this->ProductReceipt->setSource($productreceiptsTable);
				//
				//$this->PaymentDetail->setSource($payment_source);
				
			}
		}
		
		$salesTable = "t_kiosk_product_sales";
		$productreceiptsTable = "t_product_receipts";
		$payment_source = "t_payment_details";
		
		
		$KioskProductSaleTable = TableRegistry::get($salesTable,[
																'table' => $salesTable,
															]);
		$ProductReceiptTable = TableRegistry::get($productreceiptsTable,[
																'table' => $productreceiptsTable,
															]);
		$PaymentDetailTable = TableRegistry::get($payment_source,[
																'table' => $payment_source,
															]);
		
		
		$vat = $this->VAT;
		$settingArr = $this->setting;
		$receiptRequiredSession = $this->request->Session()->read('receipt_required');
		$finalAmount = $this->request->Session()->read('finalAmount');
		$finalAmount = round($finalAmount,2);
		$newBasket = $this->request->Session()->read('Basket');
		$bulkDiscount = 0;
		$bulkDiscount = $this->request->Session()->read('BulkDiscount');
		$amount = 0;
		$amount = $finalAmount;
		
		
		
					
		$orderDetails_query = $ProductReceiptTable->find('all',array(
							'conditions'=>array('id'=>$saleId)
					)
				);
		$orderDetails_query = $orderDetails_query->hydrate(false);
		if(!empty($orderDetails_query)){
			$orderDetails = $orderDetails_query->first();
		}else{
			$orderDetails = array();
		}
		$originalSaleDate = $orderDetails['created'];
		$saleVat = $orderDetails['vat'];
		//pr($_SESSION);die;
		$sum_total = $this->request->Session()->read('Basket');
		$counter = 0;
		
		
		
		$saleData_query = $KioskProductSaleTable->find('all',array(
							'conditions'=>array('product_receipt_id'=>$saleId)
					)
				);
		$saleData_query = $saleData_query->hydrate(false);
		if(!empty($saleData_query)){
			$saleData = $saleData_query->toArray();
		}else{
			$saleData = array();
		}
		//pr($saleData);
		$orignal_price = 0;
		if(!empty($saleData)){
			foreach($saleData as $key => $value){
					 $sale_price = $value["sale_price"];
					 $discount = $value["discount"];
					 $quantity = $value["quantity"];
				 $orignal_price += $quantity * ($sale_price - ($sale_price*($discount/100)));
			}
		}
		$vat = $this->VAT;
		$discount = $orignal_price * ($bulkDiscount/100);
		$netprice = $orignal_price - $discount;
		if($saleVat > 0){
			//$netprice = $netprice + ($netprice * ($vat/100));
		}
		$amount = $amount+$netprice;
		
		
		$totalCost = 0; // added by sourabh
		//$this->Product->setSource("products");//because cost price of product can be fetched only from warehouse products table
		$source = "products";
		$ProductTable = TableRegistry::get($source,[
														'table' => $source,
													]);
		$product_code = $this->Products->find("list",[
									  "keyField" => "id",
									  "valueField" => "product_code"
									  ])->toArray();
		//pr($sum_total);die;
		foreach($sum_total as $productID => $productData){
			if($productID == 'error')continue;
			$quantity = $productData['quantity'];
			$discount = $productData['discount'];
			
			//$this->ProductReceipt->clear();
			$costPrice_query = $ProductTable->find('list',[
													 'conditions' => ['id' => $productID],
													 'keyField' => 'id',
													 'valueField'=>'cost_price'
													]
											);// added by sourabh
			$costPrice_query = $costPrice_query->hydrate(false);
			if(!empty($costPrice_query)){
				$costPrice = $costPrice_query->toArray();
			}else{
				$costPrice = array();
			}
			//------added by rajiv case: when price_without_vat is not received
			if(!array_key_exists('price_without_vat', $productData)){
				$sellingPrice_query = $ProductTable->find('list',[
															'conditions' => ['id' => $productID],
															'keyField' => 'id',
															'valueField'=>'selling_price'
														   ]
													);// added by sourabh
				$sellingPrice_query = $sellingPrice_query->hydrate(false);
				if(!empty($sellingPrice_query)){
					$sellingPrice = $sellingPrice_query->toArray();
				}else{
					$sellingPrice = array();
				}
				$numerator = $sellingPrice[$productID] * 100;
				$denominator = $vat+100;
				$priceWithoutVat = $numerator/$denominator;
				$priceWithoutVat = number_format($priceWithoutVat,2);
			}else{
				$priceWithoutVat = $productData['price_without_vat'];
			}
			//-----------------------------------------------------------------
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
					if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER || $this->request->session()->read('Auth.User.group_id') == MANAGERS //|| $this->request->session()->read('Auth.User.group_id') == SALESMAN
					   ){
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
					if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER && $this->request->session()->read('Auth.User.group_id') == MANAGERS //|| $this->request->session()->read('Auth.User.group_id') == SALESMAN
					   ){
						$sale_type = 1;
					}else{
						$sale_type = 0;
					}
					
				}
			
				$kioskProductSaleData = array(
												'kiosk_id' => $kiosk_id,
												'product_receipt_id' => $saleId,
												'sale_price' => $priceWithoutVat,
												'quantity' => $quantity,
												'product_id' => $productID,
												'discount' => $discount,
												'sale_type' => $sale_type,
												'created' => $originalSaleDate,
												'sold_by' => $this->request->Session()->read('Auth.User.id')
											);		
				$totalCost+=$costPrice[$productID] * $quantity; // added by sourabh
				$KioskProductSaleTable->behaviors()->load('Timestamp');
				$entity = $KioskProductSaleTable->newEntity();
				$entity = $KioskProductSaleTable->patchEntity($entity,$kioskProductSaleData);
				if($KioskProductSaleTable->save($entity)){
					$data = array(
								'quantity' => $quantity,
								'product_code' => $product_code[$productID],
								'selling_price_withot_vat' => $priceWithoutVat,
								'vat' => 0,
							);
					if($kiosk_id == 0){
						$kiosk_id_to_use = 10000;
					}else{
						$kiosk_id_to_use = $kiosk_id;
					}
					$this->insert_to_ProductSellStats($productID,$data,$kiosk_id_to_use,$operations = '+',1);
					$counter++;
					$productData = array('quantity' => "Product.quantity - $quantity");
					$updateQry = "UPDATE `$productsTable` SET `quantity` = `quantity` - $quantity WHERE `$productsTable`.`id` = '$productID'";
					$rand = rand(500,10000);
					//mail('kalyanrajiv@gmail.com', "Line #4231- $rand", $updateQry);
					//$this->Product->query($updateQry);
					$conn = ConnectionManager::get('default');
					$stmt = $conn->execute($updateQry); 
				}
				
		}

			if($counter > 0){
					//$this->ProductReceipt->id = $saleId;
				//$this->ProductReceipt->query("UPDATE `$productreceiptsTable` SET `bill_amount` =  $amount WHERE `$productreceiptsTable`.`id` = '$saleId'");
				$conn1 = ConnectionManager::get('default');
				$stmt1 = $conn1->execute("UPDATE `$productreceiptsTable` SET `bill_amount` =  $amount WHERE `$productreceiptsTable`.`id` = '$saleId'"); 
				//$this->ProductReceipt->query("UPDATE `$productreceiptsTable` SET `orig_bill_amount` =  $amount WHERE `$productreceiptsTable`.`id` = '$saleId'");
				$conn2 = ConnectionManager::get('default');
				$stmt2 = $conn2->execute("UPDATE `$productreceiptsTable` SET `orig_bill_amount` =  $amount WHERE `$productreceiptsTable`.`id` = '$saleId'"); 
				//$this->ProductReceipt->query("UPDATE `$productreceiptsTable` SET `bulk_discount` = $bulkDiscount WHERE `$productreceiptsTable`.`id` = '$saleId'");
				$conn3 = ConnectionManager::get('default');
				$stmt3 = $conn3->execute("UPDATE `$productreceiptsTable` SET `bulk_discount` = $bulkDiscount WHERE `$productreceiptsTable`.`id` = '$saleId'"); 
				$billCost_query = $KioskProductSaleTable->find('list',[
																'conditions' => ['product_receipt_id' => $saleId],
																'keyField' => 'product_id',
																'valueField'=>'quantity'
																]
														);
				$billCost_query = $billCost_query->hydrate(false);
				if(!empty($billCost_query)){
					$billCost = $billCost_query->toArray();
				}else{
					$billCost = array();
				}
				$totalBillCost = 0;
				if(count(array_keys($billCost)) > 0){
					$prodIds = array_keys($billCost);
					$source = "products";
					$ProductTable = TableRegistry::get($source,[
																	'table' => $source,
																]);
					if(empty($prodIds)){
						$prodIds = array(0=>null);
					}
					$prodCosts_query = $ProductTable->find('list',[
																'conditions' => ['id IN' => $prodIds],
																'keyField' => 'id',
																'valueField'=>'cost_price'
															]
													);
					$prodCosts_query = $prodCosts_query->hydrate(false);
					if(!empty($prodCosts_query)){
						$prodCosts = $prodCosts_query->toArray();
					}else{
						$prodCosts = array();
					}
					foreach($billCost as $prodId => $prodQty){
						$totalBillCost += $prodCosts[$prodId] * $prodQty;
					}
				}
			//$this->ProductReceipt->query("UPDATE `$productreceiptsTable` SET `bill_cost` = `bill_cost` + $totalCost WHERE `$productreceiptsTable`.`id` = '$saleId'");// added by sourabh
			$conn4 = ConnectionManager::get('default');
			$stmt4 = $conn4->execute("UPDATE `$productreceiptsTable` SET `bill_cost` = $totalBillCost WHERE `$productreceiptsTable`.`id` = '$saleId'"); 
			//$this->ProductReceipt->query("UPDATE `$productreceiptsTable` SET `bill_cost` = $totalBillCost WHERE `$productreceiptsTable`.`id` = '$saleId'");// modified by rajiv
			$productReceipt_query = $ProductReceiptTable->find('all',array(
																			'conditions' => array('id' => $saleId)
																			)
															);
			$productReceipt_query = $productReceipt_query->hydrate(false);
			if(!empty($productReceipt_query)){
				$productReceipt = $productReceipt_query->first();
			}else{
				$productReceipt = array();
			}
			
			//Start block: cron dashboard code
			if(array_key_exists('amount_changed',$_SESSION)){
				$amt = $amount - $_SESSION['amount_changed'];
				unset($_SESSION['amount_changed']);
			}else{
				$amt = 0;
			}
			if($amt >= 0){
				
				if($kiosk_id == 10000 || $kiosk_id == ""){
						$kioskid = 0;
					}else{
						$kioskid = $kiosk_id;
					}
				$this->loadModel('DashboardData');
				$conditionArr = array();
					$conditionArr[] = array(
								  "date >=" => date('Y-m-d', strtotime($originalSaleDate)),
								  "date <" => date('Y-m-d', strtotime($originalSaleDate. ' +1 Days')),			
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
							if($new_dash_data[$daash_key]['user_type'] == "normal"){
								continue;
							}
							if(array_key_exists("quotation",$dash_value)){
								$new_dash_data[$daash_key]['quotation'] = (float)$dash_value['quotation'] + (float)$amt;
							}
							if(array_key_exists("quotation_desc",$dash_value)){
								$product_sale_desc = unserialize($dash_value['quotation_desc']);
								$product_sale_desc['credit'] = (float)$product_sale_desc['credit'] + (float)$amt;
								$product_sale_desc_new = serialize($product_sale_desc);
								$new_dash_data[$daash_key]['quotation_desc'] = $product_sale_desc_new;
							}
							if(array_key_exists("net_credit_desc",$dash_value)){
								$credit_desc = unserialize($dash_value['net_credit_desc']);
								$credit_desc[0] = (float)$credit_desc[0] + (float)$amt;
								$credit_desc_new = serialize($credit_desc);
								$new_dash_data[$daash_key]['net_credit_desc'] = $credit_desc_new;
							}
							
							if(array_key_exists("total_sale",$dash_value)){
								$new_dash_data[$daash_key]['total_sale'] = (float)$dash_value['total_sale'] + (float)$amt;
							}
							if(array_key_exists("net_credit",$dash_value)){
								$new_dash_data[$daash_key]['net_credit'] = (float)$dash_value['net_credit'] + (float)$amt;
							}
							if(array_key_exists("net_sale",$dash_value)){
								$new_dash_data[$daash_key]['net_sale'] = (float)$dash_value['net_sale'] + (float)$amt;
							}
						}
					}
					
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
			
			if(!empty($productReceipt)){
				$id = $productReceipt['id'];
				$kioskProductSalesData_query = $KioskProductSaleTable->find('all',['conditions'=>['product_receipt_id'=>$id]]);
				$kioskProductSalesData_query = $kioskProductSalesData_query->hydrate(false);
				if(!empty($kioskProductSalesData_query)){
					$kioskProductSalesData = $kioskProductSalesData_query->toArray();
				}else{
					$kioskProductSalesData = array();
				}
			}else{
				$kioskProductSalesData = array();
			}
			$this->set(compact('kioskProductSalesData'));
			$processed_by = $productReceipt['processed_by'];
			$userName_query = $this->Users->find('all',array('conditions'=>array('Users.id'=>$processed_by),'fields'=>array('username')));
			$userName_query = $userName_query->hydrate(false);
			if(!empty($userName_query)){
				$userName = $userName_query->first();
			}else{
				$userName = array();
			}
			$user_name = $userName['username'];
			foreach($kioskProductSalesData as $key => $productDetail){
				$productIdArr[] = $productDetail['product_id'];
			}
			foreach($productIdArr as $product_id){
				$product_detail_query = $ProductTable->find('all', array('conditions'=>array('id'=>$product_id),'fields' => array('id','product','product_code')));
				$product_detail_query = $product_detail_query->hydrate(false);
				if(!empty($product_detail_query)){
					$product_detail = $product_detail_query->first();
				}else{
					$product_detail = array();
				}
			}
			//pr($product_detail);die;
			//foreach($product_detail as $productInfo){
				$productName[$product_detail['id']] = $product_detail['product'];
				$productCode[$product_detail['id']] = $product_detail['product_code'];
			//}
				
			$vat = $productReceipt['vat'];
			
			$paymentDetails_query = $PaymentDetailTable->find('all',array('conditions' => array('product_receipt_id' => $saleId)));
			$paymentDetails_query = $paymentDetails_query->hydrate(false);
			if(!empty($paymentDetails_query)){
				$paymentDetails = $paymentDetails_query->toArray();
			}else{
				$paymentDetails = array();
			}
			$payment_method = array();
			foreach($paymentDetails as $key=>$paymentDetail){
				$payment_method[] = $paymentDetail['payment_method']." ".$CURRENCY_TYPE.$paymentDetail['amount'];
			}
					
			//Configure::load('common-arrays');
			$countryOptions = Configure::read('uk_non_uk');
				
			$fullAddress = $kiosk_id = $kioskDetails = $kioskName = $kioskAddress1 = $kioskAddress2 = $kioskCity = $kioskState = $kioskZip  = $kioskZip = $kioskContact = $kioskCountry = $kioskTable = "";
			if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS &&
			    $this->request->session()->read('Auth.User.username')=='wholesale'){
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
			$send_by_email = Configure::read('send_by_email');
			$emailSender = Configure::read('EMAIL_SENDER');
			if($receiptRequiredSession == 1){
				$Email = new Email();
				$Email->config('default');
				$Email->viewVars(
								 array(
									   'productReceipt' => $productReceipt,
									   'payment_method' => $payment_method,
									   'vat' => $vat,
									   'settingArr' =>$settingArr,
									   'user_name'=>$user_name,
									   'productName'=>$productName,
									   'productCode'=>$productCode,
									   'kioskContact'=>$kioskContact,
									   'kioskTable'=>$kioskTable,
									   'countryOptions'=>$countryOptions
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
				//$Email->sender("sales@oceanstead.co.uk");
				$Email->subject('Order Receipt');
				$Email->send();
			}
					
			$this->Flash->success("Invoice has been updated");
			$this->request->Session()->delete('Basket');
			$this->request->Session()->delete('BulkDiscount');
			$this->request->Session()->delete('receipt_required');
			$this->request->Session()->delete('oldBasket');
			$this->request->Session()->delete('session_basket');
			if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
				$kiosk_id_to_set = $this->get_kiosk_for_dr_invoice();
				return $this->redirect(array('controller'=>'product_receipts','action'=>"dr_search?kiosk_id=$kiosk_id_to_set&date_type=invoice"));
			}else{
				return $this->redirect(array('controller'=>'product_receipts','action'=>'dr_index'));
			}
			
			
		}
	}
    
    public function deleteProductFromSession2($product_id="",$orderId = ""){
		if($product_id){
            unset($_SESSION['Basket'][$product_id]);
			$session_basket = $this->request->Session()->read('Basket');
			$kiosk_id = $this->request->Session()->read('kiosk_id');
			return $this->redirect(array('action'=>'edit_receipt_checkout',$orderId));
		}
	}
	
	public function drEditReceiptCheckout($orderId = ""){
		
		$kiosk_id_to_set = $this->get_kiosk_for_dr_invoice();
			if(!empty($kiosk_id_to_set)){
				if($kiosk_id_to_set == 10000){
					$productsTable = "products";
					//$salesTable = "kiosk_product_sales";
					//$productreceiptsTable = "product_receipts";
					//$payment_source = "payment_details";
				}else{
					$kiosk_id = $kiosk_id_to_set;
					$productsTable = "kiosk_{$kiosk_id}_products";
					//$salesTable = "kiosk_{$kiosk_id}_product_sales";
					//$productreceiptsTable = "kiosk_{$kiosk_id}_product_receipts";
					//$payment_source = "kiosk_{$kiosk_id}_payment_details";
				}
				//$this->KioskProductSale->setSource($salesTable);
				//
				//$this->ProductReceipt->setSource($productreceiptsTable);
				//
				//$this->PaymentDetail->setSource($payment_source);
				
			}else{
				$kiosk_id = $this->request->Session()->read('kiosk_id');
				$productsTable = "kiosk_{$kiosk_id}_products";
			}
			$ProductTable = TableRegistry::get($productsTable,[
																'table' => $productsTable,
															]);
			$salesTable = "t_kiosk_product_sales";
			$productreceiptsTable = "t_product_receipts";
			$payment_source = "t_payment_details";	
		
			$KioskProductSaleTable = TableRegistry::get($salesTable,[
																'table' => $salesTable,
															]);
			$ProductReceiptTable = TableRegistry::get($productreceiptsTable,[
																	'table' => $productreceiptsTable,
																]);
			$PaymentDetailTable = TableRegistry::get($payment_source,[
																	'table' => $payment_source,
															]);	
		//pr($_SESSION);die;
		$session_basket = $this->request->Session()->read('Basket');
		$productCodeArr = array();
		$productCode = array();
		if(!empty($session_basket)){
			$product_ids = array_keys($session_basket);
			if(empty($product_ids)){
				$product_ids = array(0=>null);
			}
			$productCodeArr_query = $ProductTable->find('all',array('conditions'=>array('id IN'=>$product_ids),'fields'=>array('id','product_code','quantity')));
			$productCodeArr_query = $productCodeArr_query->hydrate(false);
			if(!empty($productCodeArr_query)){
				$productCodeArr = $productCodeArr_query->toArray();
			}else{
				$productCodeArr = array();
			}
			foreach($session_basket as $key => $basketItem){
			if($key == 'error')continue;
			//$productCodeArr[] = $this->Product->find('first',array('conditions'=>array('Product.id'=>$key),'fields'=>array('id','product_code'),'recursive'=>-1));
			}
//			pr($productCodeArr);
			if(!empty($productCodeArr)){
			 	foreach($productCodeArr as $k=>$productCodeData){
					  $productIds[$productCodeData['id']] = $productCodeData['product_code'];
					  $productCodes[$productCodeData['product_code']] = $productCodeData['quantity'];
					}
			}
		}
		$orderDetails_query = $ProductReceiptTable->find('all',array(
							'conditions'=>array('id' => $orderId)
					)
				);
		$orderDetails_query = $orderDetails_query->hydrate(false);
		if(!empty($orderDetails_query)){
			$orderDetails = $orderDetails_query->first();
		}else{
			$orderDetails = array();
		}
		$customerId = $orderDetails['customer_id'];
		$customerAccountDetails_query = $this->Customers->find('all',array(
									'conditions'=>array('Customers.id' => $customerId)
									)
								);
		$customerAccountDetails_query = $customerAccountDetails_query->hydrate(false);
		if(!empty($customerAccountDetails_query)){
			$customerAccountDetails = $customerAccountDetails_query->first();
		}else{
			$customerAccountDetails = array();
		}
		$country = $customerAccountDetails['country'];
		$currencySymbol = $this->setting['currency_symbol'];
 		$vat = $this->VAT;
		
		if($this->request->is('post')){
			//pr($this->request);die;
			$error = array();
			if(array_key_exists('update_quantity',$this->request->data)){
				$lessProducts = array();
				$lowProducts = array();
				foreach($this->request->data['data']['CheckOut'] as $productCode => $quantity){
					$availableQty = $productCodes[$productCode];
					if($quantity == 0 || !(int)$quantity){
							$lowProducts[] = $productCode;
					}
					if($quantity > $availableQty){
						$lessProducts[] = $productCode;
					}	
				}
				
				if(count($lessProducts) >= 1){
					$this->Flash->error("Please choose ".implode(",",$lessProducts)." quantity less than or equal to available stock" );
					return $this->redirect(array('action'=>'new_order_checkout'));
				}
				
				if(count($lowProducts) > 0){
					$this->Flash->error("Please choose  more than 0 for product : ".implode(",",$lowProducts) );
					return $this->redirect(array('action'=>'dr_edit_receipt_checkout'));
				}else{
					$requestedQuantity = $this->request->data['data']['CheckOut'];
					$newArray = array();
					$counter = 0;
					$requestedQuantity = array_values($requestedQuantity);//die;
					//pr($session_basket);die;
					foreach($session_basket as $productCode => $productData){
						$qty = "";
						if(array_key_exists($counter,$requestedQuantity)){
							 $qty =  $requestedQuantity[$counter];
						}
						if(empty($productData['remarks'])){
							$productData['remarks'] = "";
						}
						$newArray[$productCode] = array(
							'quantity' =>  $qty   ,
							'selling_price' => $productData['selling_price'],
							//'remarks' => $productData['remarks'],
							'price_without_vat' => $productData['price_without_vat'],
							'net_amount' => $productData['net_amount'],
							'product'  => $productData['product'] ,
							'discount'  => $productData['discount'] ,
							'discount_status'  => $productData['discount_status'] ,
							'receipt_required'  => $productData['receipt_required'] ,
							'bulk_discount'  => $productData['bulk_discount'] 
													);
						$counter++;
					}
					$this->request->Session()->delete('Basket');
					$this->request->Session()->write('Basket',$newArray);
					$this->Flash->success("Quantity has been  successfully updated");
					return $this->redirect(array('action'=>'dr_edit_receipt_checkout',$orderId));
				}
			}elseif(array_key_exists('edit_basket',$this->request->data)){
				return $this->redirect(array('action'=>"dr_edit_receipt",$orderId));
			}
		}
		
		$this->set(compact('vat','country','currencySymbol','orderId','productCode','productCodeArr','productIds'));
	}
	
	public function drDeleteProductFromSession2($product_id="",$orderId = ""){
		if(true){
			unset($_SESSION['Basket'][$product_id]);
			$session_basket = $this->request->Session()->read('Basket');
			$kiosk_id = $this->request->Session()->read('kiosk_id');
			return $this->redirect(array('action'=>'dr_edit_receipt_checkout',$orderId));
		}
	}
	
	public function drAdjustPayment($orderId){
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
		
		
		$orderDetails_query = $ProductReceiptTable->find('all',array(
							'conditions'=>array('id' => $orderId)
					)
				);
		$orderDetails_query = $orderDetails_query->hydrate(false);
		if(!empty($orderDetails_query)){
			$orderDetails = $orderDetails_query->first();
		}else{
			$orderDetails = array();
		}
		if(!empty($orderDetails)){
			$id = $orderDetails['id'];
			$kioskProductSales_query = $KioskProductSaleTable->find('all',[
																			'conditions'=>['product_receipt_id'=>$id]
																			]
																	);
			$kioskProductSales_query = $kioskProductSales_query->hydrate(false);
			if(!empty($kioskProductSales_query)){
				$kioskProductSales = $kioskProductSales_query->toArray();
			}else{
				$kioskProductSales = array();
			}
			$paymentDetail_query = $PaymentDetailTable->find('all',[
																		'conditions'=>['product_receipt_id'=>$id]
																	]
															);
			$paymentDetail_query = $paymentDetail_query->hydrate(false);
			if(!empty($paymentDetail_query)){
				$paymentDetail = $paymentDetail_query->toArray();
			}else{
				$paymentDetail = array();
			}
		}else{
			$paymentDetail = array();
			$kioskProductSales = array();
		}
		$this->set(compact('paymentDetail','kioskProductSales'));
		//pr($paymentDetail);die;
		//pr($kioskProductSales);die;
		$bulkDiscount = $this->request->Session()->read('BulkDiscount');
		//pr($orderDetails);die;
		if(!empty($orderDetails)){
			$total_value = 0;
			foreach($kioskProductSales as $key => $value){
				if(!empty($value['discount'])){
					$each_item_value = $value['sale_price'] - $value['sale_price'] * ($value['discount']/100);
					$item_value = $each_item_value * $value['quantity'];
				}else{
					$each_item_value = $value['sale_price'];
					$item_value = $each_item_value * $value['quantity'];
				}
				$total_value += $item_value;
			}
			//echo $total_value;die;
		}
		$final_amout = $total_value - $total_value * ($bulkDiscount/100);
		if(!empty($orderDetails['vat'])){
			//$final_amout = $final_amout + ($final_amout*$orderDetails['ProductReceipt']['vat']/100);
		}
		$final_price = round($final_amout,2);
		if($this->request->is('post')){
			$amount = $this->request->data['sale_amount'];
			$amoubt_arr = $this->request->data['old_amt'];
			$check_amt = 0;
			foreach($amoubt_arr as $key => $value){
				$check_amt+= $value;
			}
			$check_amt = round($check_amt,2);
			$amount = round($amount,2);
			if($check_amt != $amount){
				$flashMessage = "amount is not matching";
				$this->Flash->error($flashMessage);
				return $this->redirect(array('controller'=>'kiosk_product_sales','action' => "dr_adjust_payment", $orderId));
			}
			$count = 0;
			foreach($paymentDetail as $key => $paymentData){
				$new_pay_data = array(
										'id' => $paymentData['id'],
										'amount' => $amoubt_arr[$key],
										'modified' => $paymentData['modified']
										);
				$PaymentDetailTable->behaviors()->load('Timestamp');
				$entity = $PaymentDetailTable->get($paymentData['id']);
				$entity = $PaymentDetailTable->patchEntity($entity,$new_pay_data,['validate'=>false]);
				if($PaymentDetailTable->save($entity)){
					$count++;	
				}	
			}
			if($count >0){
				$dataArr = array('bill_amount'=>$amount , 'orig_bill_amount'=>$amount);
				$ProductReceiptTable->behaviors()->load('Timestamp');
				$Entity = $ProductReceiptTable->get($orderId);
				$Entity = $ProductReceiptTable->patchEntity($Entity,$dataArr,['validate'=>false]);
				$ProductReceiptTable->save($Entity);
				return $this->redirect(array('controller'=>'product_receipts','action' => "dr_make_payment", $orderId));
			}
			
		}
		$this->set(compact('final_price','orderDetails'));
	}
    
    public function adminData($search = ""){
		//die('1125');
		if(array_key_exists('search',$this->request->query)){
			$search = strtolower($this->request->query['search']);
		}
		
		$catgoryArr = array();
		if(array_key_exists('category',$this->request->query)){
			$catgoryArr = explode(",",$this->request->query['category']);
		}
		
		
		//change product resource at run time
		//quantity should be more than 0
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		if($kiosk_id < 10000 && $kiosk_id != 0 && !empty($kiosk_id)){
			$productTable_source = "kiosk_{$kiosk_id}_products";
            
            
			//$this->Product->setSource("kiosk_{$kiosk_id}_products");
		}else{
			$productTable_source = "products";
		}
        
        $productTable = TableRegistry::get($productTable_source,[
                                                                            'table' => $productTable_source,
                                                                        ]);
		
		if(!empty($search)){
			/*$productList = $this->Product->find('all',array(
															'fields'=> array('product','product_code'),
															'recursive'=> -1,
															'conditions' => array(
																				  "LOWER(`Product`.`product`) like '%$search%'"
																				  )
												)
						    );*/
			ob_start();
			preg_match('/^(?>\S+\s*){1,5}/', $search, $match);
			$search = $match[0];
			$this->pc_permute(explode(' ',$search));  //split(' ', $search)
			$permutation = ob_get_clean();
			$wordArray = explode("\n", $permutation);
			$searchArray = array();
			$newCatArr = array();
			foreach($catgoryArr as $value){
				if($value == '0' || empty($value)){continue;}else{
					$newCatArr[] = $value;
				}
			}
			//if(($key = array_search('0', $catgoryArr)) !== false) {
			//	unset($catgoryArr[$key]);
			//}
			//print_r($newCatArr);
			
			foreach($wordArray as $value){
				if(empty($value))continue;
				$searchArray['AND']['OR'][] = "LOWER(`product`) like '%".str_replace(" ","%",$value)."%'";
				//removing 0 value from array which is for all
				
			}
			if(count($newCatArr) >= 1){
				$searchArray['AND']['category_id'] = $newCatArr;
			}
			//$searchArray['AND']['quantity >'] = '0';
			$productList_query = $productTable->find('all',array(
															'fields'=> array('product','product_code','quantity'),
															'recursive'=> -1,
															'conditions' => $searchArray
												)
						    );
			$productList_query->hydrate(false);
			$productList = $productList_query->toArray();
		}else{
			$productList_query = $productTable->find('all',array(
													'fields'=> array('product','product_code','quantity'),
													'conditions' => array(),
														'recursive'=>-1
											)
						    );
			$productList_query->hydrate(false);
			$productList = $productList_query->toArray();
		}
		$customProductList = array();
		foreach($productList as $productRow){
			$customProductList[] = array(
										 'product' => $productRow['product'],
										 'product_code'=> $productRow['product']."-".$productRow['product_code']." (Qty:".$productRow['quantity'].")",
										 'code' => $productRow['product_code'],

										 );
		}
		echo json_encode($customProductList);
		$this->viewBuilder()->layout(false);
		die;
	}
	
	public function has_refund($refundAmt = '',$upadtedAmt = '',$finalAmt = '',$updatedPayment = '',$saleAmt = '',$id = '',$kioskId = '',$userId = '',$recitId = '',$new_change_mode = '',$added_amount = ''){
		 $ProductReceipt_source = "kiosk_{$kioskId}_product_receipts";
        $ProductReceiptTable = TableRegistry::get($ProductReceipt_source,[
                                                                            'table' => $ProductReceipt_source,
                                                                        ]);
        $KioskProductSale_source = "kiosk_{$kioskId}_product_sales";
        $KioskProductSaleTable = TableRegistry::get($KioskProductSale_source,[
                                                                            'table' => $KioskProductSale_source,
                                                                        ]);
		
		$counter = 0;
		$checkAmt = 0;
		$checkAmt += $refundAmt;
		foreach($upadtedAmt as $key => $value){
			$checkAmt += $value;
		}
		$checkAmt = round($checkAmt,2);
		$finalAmt = round($finalAmt,2);
		if($checkAmt == $finalAmt){
			foreach($upadtedAmt as $key1 => $value1){
				$payment_entity = $this->ProductPayments->get($key1);
				$data_to_save = array('amount' => $value1);
				$payment_entity = $this->ProductPayments->patchEntity($payment_entity,$data_to_save,['validate' => false]);
				if($this->ProductPayments->save($payment_entity)){
					$paymentMethod = $updatedPayment[$key1];
					$data_to_save1 = array('payment_method' => $paymentMethod);
					$payment_entity1 = $this->ProductPayments->get($key1);
					$payment_entity1 = $this->ProductPayments->patchEntity($payment_entity1,$data_to_save1,['validate' => false]);
					$this->ProductPayments->save($payment_entity1);
					$counter++;
				}
			}
			if($counter >= 1){
					$KioskProductSale_entity = $KioskProductSaleTable->get($id);
					$data_to_save = array('sale_price' => $saleAmt);
					$KioskProductSale_entity = $KioskProductSaleTable->patchEntity($KioskProductSale_entity,$data_to_save,['validate' => false]);
					if($KioskProductSaleTable->save($KioskProductSale_entity)){
						$KioskProductSale_entity1 = $KioskProductSaleTable->get($id);
						$data_to_save1 = array('discount'=> 0);
						$KioskProductSale_entity1 = $KioskProductSaleTable->patchEntity($KioskProductSale_entity1,$data_to_save1,['validate' => false]);
						$KioskProductSaleTable->save($KioskProductSale_entity1);
						
						
						
						$ProductReceipt_entity = $ProductReceiptTable->get($recitId);
						$amount_to_add = $finalAmt + ($refundAmt*(-1));
						$date_to_save_recipt = array(
													 'bill_amount' => $amount_to_add,
													 'orig_bill_amount' => $amount_to_add);
						$ProductReceipt_entity = $ProductReceiptTable->patchEntity($ProductReceipt_entity,$date_to_save_recipt,['validate' => false]);
						if($ProductReceiptTable->save($ProductReceipt_entity)){
							$counter++;	
						}else{
							pr($ProductReceipt_entity->errors());die;
						}
						
					}else{
						pr($KioskProductSale_entity->errors());die;
					}
			}
		}else{
			if(isset($added_amount) && !empty($added_amount)){
				$checkAmt += $added_amount;
					if($checkAmt == $finalAmt){
						foreach($upadtedAmt as $key1 => $value1){
							$ProductPaymentsEntity = $this->ProductPayments->get($key1);
							$paymentMethod = $updatedPayment[$key1];
							$data_to_save = array('amount' => $value1,
												  'payment_method' => $paymentMethod,
												  );
							$ProductPaymentsEntity = $this->ProductPayments->patchEntity($ProductPaymentsEntity,$data_to_save,['validate' => false]);
							$this->ProductPayments->save($ProductPaymentsEntity);
							
						}
						$data = array(
										'kiosk_id' => $kioskId,
										'user_id' => $userId,
										'product_receipt_id' => $recitId,
										'payment_method' => $new_change_mode,
										'amount' => $added_amount,
										'payment_status' => 1,
										'status' => 1
									);
						$payEntity = $this->ProductPayments->newEntity($data,['validate' => false]);
						$payEntity = $this->ProductPayments->patchEntity($payEntity,$data,['validate' => false]);
						if($this->ProductPayments->save($payEntity)){
							$counter++;
						}
						if($counter >= 1){
							$KioskProduct_entity = $KioskProductSaleTable->get($id);
							$date_to_save_sale = array('sale_price' => $saleAmt,
													   'discount' => 0,
													   );
							$KioskProduct_entity = $KioskProductSaleTable->patchEntity($KioskProduct_entity,$date_to_save_sale,['validate' => false]);
							if($KioskProductSaleTable->save($KioskProduct_entity)){
								$amount_to_add = $finalAmt + ($refundAmt*(-1));
								
								
								$ProductReceipt_entity = $ProductReceiptTable->get($recitId);
								$data_to_save_recp = array(
														   'bill_amount' => $amount_to_add,
														   'orig_bill_amount' => $amount_to_add);
								$ProductReceipt_entity = $ProductReceiptTable->patchEntity($ProductReceipt_entity,$data_to_save_recp,['validate' => false]);
								if($ProductReceiptTable->save($ProductReceipt_entity)){
									$counter++;	
								}else{
									pr($ProductReceipt_entity->errors());die;
								}
								
							}else{
								pr($KioskProduct_entity->errors());die;
							}
						}
					}else{
						$this->Flash->error("failed to save payment.try again");
						$this->redirect(array('action' => 'payment_method',$changed_amount,$recitId,$kioskId,$diff,$id));
					}
					
			}
		}
		if($counter >= 2){
			return true;
		}else{
			return false;
		}
	}
	
	public function without_refund($upadtedAmt = '',$finalAmt = '', $updatedPayment = '', $saleAmt = '', $id = '', $kioskId = '', $userId = '', $recitId = '', $new_change_mode = '', $added_amount = ''){
		
		
		 $ProductReceipt_source = "kiosk_{$kioskId}_product_receipts";
        $ProductReceiptTable = TableRegistry::get($ProductReceipt_source,[
                                                                            'table' => $ProductReceipt_source,
                                                                        ]);
        $KioskProductSale_source = "kiosk_{$kioskId}_product_sales";
        $KioskProductSaleTable = TableRegistry::get($KioskProductSale_source,[
                                                                            'table' => $KioskProductSale_source,
                                                                        ]);
		
		$counter = 0;
		$checkAmt = 0;
		foreach($upadtedAmt as $key => $value){
			$checkAmt += $value;
		}
		//echo $checkAmt;
		if($checkAmt == $finalAmt){
			foreach($upadtedAmt as $key1 => $value1){
				$payEntity = $this->ProductPayments->get($key1);
				$data_to_save = array('amount' => $value1);
				$payEntity = $this->ProductPayments->patchEntity($payEntity,$data_to_save,['validate' => false]);
				if($this->ProductPayments->save($payEntity)){
					$paymentMethod = $updatedPayment[$key1];
					$data_to_save_2 = array('payment_method' => $paymentMethod);
					$payEntity1 = $this->ProductPayments->get($key1);
					$payEntity1 = $this->ProductPayments->patchEntity($payEntity1,$data_to_save_2,['validate' => false]);
					$this->ProductPayments->save($payEntity1);
					$counter++;
				}
				
				if($counter >= 1){
					$KioskProductEntity = $KioskProductSaleTable->get($id);
					$product_data = array('sale_price' => $saleAmt,
										  'discount' => 0,
										  );
					$KioskProductEntity = $KioskProductSaleTable->patchEntity($KioskProductEntity,$product_data,['validate' => false]);
					if($KioskProductSaleTable->save($KioskProductEntity)){
						$recipt_data = array(
											 'bill_amount' => $finalAmt,
											 'orig_bill_amount' => $finalAmt);
						$ProductReceiptEntity = $ProductReceiptTable->get($recitId);
						$ProductReceiptEntity = $ProductReceiptTable->patchEntity($ProductReceiptEntity,$recipt_data,['validate' => false]);
						$ProductReceiptTable->save($ProductReceiptEntity);
						$counter++;
					}
				}
			}
		}else{
			if(isset($added_amount) && !empty($added_amount)){
				echo "hi";
				$checkAmt += $added_amount;
					if($checkAmt == $finalAmt){
						foreach($upadtedAmt as $key1 => $value1){
							$pay_entity = $this->ProductPayments->get($key1);
							
							$data_to_save = array('amount' => $value1,
												  'payment_method' => $paymentMethod,
												  );
							$payEntity = $this->ProductPayments->patchEntity($payEntity,$data_to_save,['validate' => false]);
							
							if($this->ProductPayments->save($payEntity)){
								$counter++;
							}
						}
						$data = array(
											'kiosk_id' => $kioskId,
											'user_id' => $userId,
											'product_receipt_id' => $recitId,
											'payment_method' => $new_change_mode,
											'amount' => $added_amount,
											'payment_status' => 1,
											'status' => 1
										);
						$payEntity = $this->ProductPayments->newEntity($data,['validate' => false]);
						$payEntity = $this->ProductPayments->patchEntity($payEntity,$data,['validate' => false]);
						if($this->ProductPayments->save($payEntity)){
							$counter++;
						}
						if($counter >= 1){
							$sale_entity = $KioskProductSaleTable->get($id);
							$data_to_save_sale = array('sale_price' => $saleAmt,
													   'discount' => 0,
													   );
							$sale_entity = $KioskProductSaleTable->patchEntity($sale_entity,$data_to_save_sale,['validate' => false]);
							if($KioskProductSaleTable->save($sale_entity)){
								
								
								$recipt_entity = $ProductReceiptTable->get($recitId);
								$data = array(
											  'bill_amount' => $finalAmt,
											  'orig_bill_amount' => $finalAmt);
								$recipt_entity = $ProductReceiptTable->patchEntity($recipt_entity,$data,['validate' => false]);
								$ProductReceiptTable->save($recipt_entity);
								$counter++;
							}
						}
					}else{
						$this->Flash->error("failed to save payment.try again");
							$this->redirect(array('action' => 'payment_method',$changed_amount,$recitId,$kioskId,$diff,$id));
					}
			}
		}
		
		if($counter >= 2){
			return true;
		}else{
			return false;
		}
	}
	
	
	public function save_log($recitId = '',$kioskId = '',$userId = '',$saleAmt = '',$id = ''){
		
		$ProductReceipt_source = "kiosk_{$kioskId}_product_receipts";
        $ProductReceiptTable = TableRegistry::get($ProductReceipt_source,[
                                                                            'table' => $ProductReceipt_source,
                                                                        ]);
        $KioskProductSale_source = "kiosk_{$kioskId}_product_sales";
        $KioskProductSaleTable = TableRegistry::get($KioskProductSale_source,[
                                                                            'table' => $KioskProductSale_source,
                                                                        ]);
		
		
		
		$kioskProductSale_query = $KioskProductSaleTable->find('all' ,array(
																		'conditions'=> array('product_receipt_id'=> $recitId,
																						'id'=>$id												
																							),
																		'recursive' => -1
																		)
														);
		$kioskProductSale_query = $kioskProductSale_query->hydrate(false);
		if(!empty($kioskProductSale_query)){
			$kioskProductSale = $kioskProductSale_query->first();
		}else{
			$kioskProductSale = array();
		}
		
		
		$quantity = $kioskProductSale['quantity'];
		$saleDate = $kioskProductSale['created'];
		$productId = $kioskProductSale['product_id'];
		$productDetail_query = $this->Products->find('all' ,array(
														'conditions'=>array('id'=>$productId),
																		'recursive' => -1
																		)
														);
		$productDetail_query = $productDetail_query->hydrate(false);
		if(!empty($productDetail_query)){
			$productDetail = $productDetail_query->first();
		}else{
			$productDetail = array();
		}
		$selling_price = $productDetail['selling_price'];
		$title = $productDetail['product'];
		$product_code = $productDetail['product_code'];
		$discount = $productDetail['discount'];
		$lowest_price = $selling_price - ($selling_price*$discount/100);
		$dataArray = array(
							'receipt_id' => $recitId,
							'kiosk_id' => $kioskId,
							'user_id' => $userId,
							'orignal_amount' => $lowest_price,
							'modified_amount' => $saleAmt,
							'product_code' => $product_code,
							'product_title' => $title,
							'quantity' => $quantity,
							'sale_id' => $id,
							'sale_date' => $saleDate
							);
		$this->loadModel('SaleLogs');
		
		$sale_log_entity = $this->SaleLogs->newEntity($dataArray,['validate' => false]);
		$sale_log_entity = $this->SaleLogs->patchEntity($sale_log_entity,$dataArray,['validate' => false]);
		if($this->SaleLogs->save($sale_log_entity)){
			return true;
		}else{
			return false;
		}
		
	}
	
	    public function test()
        {
            $msg='Hellow User';
            $subject='CakePHP3 Mail1111';
            $to="sourabh.proavid@gmail.com";
			
			Email::configTransport('gmail', [
											'host' => 'ssl://smtp.gmail.com',
											'port' => 465,
											'username' => 'uk.mobilebooth@gmail.com',
											'password' => 'proavid2017',
											'className' => 'Smtp',
											'ssl' => [
														'verify_peer' => false,
														'verify_peer_name' => false,
														   'allow_self_signed' => true
															   ]
										]);
			
			
			 $email_obj = new Email();

			$email_obj->template('default')
				->emailFormat('html')
				->to($to)
				->from(["uk.mobilebooth@gmail.com" => "test"])
				->subject("test")
				->transport('gmail');
		
			if($email_obj->send($msg)){
				echo "hi";
			}else{
				echo "bye";
			}
			die;
     }
	
}
?>