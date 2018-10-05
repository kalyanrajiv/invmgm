<?php
namespace App\Controller;

use Cake\Controller\Controller;
use Cake\Event\Event;
use Cake\Network\Request;
use Cake\ORM\TableRegistry;
use Cake\Mailer\Email;
use Cake\Routing\Router;
use Cake\Utility\Text;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Datasource\ConnectionManager;


class OnDemandOrdersController extends AppController
{

    public $helpers = [
         'Paginator' => ['templates' => 'paginatortemplates']
         ];
    public function initialize()
    {
        parent::initialize();
        $siteBaseURL = Configure::read('SITE_BASE_URL'); //rasu
       	$this->set(compact('siteBaseURL'));
        $this->loadComponent('ScreenHint');
        $this->loadComponent('SessionRestore');
        $this->loadModel('KioskPlacedOrders');
        $this->loadModel('OnDemandOrders');
        $this->loadModel('Products');
        $this->loadModel('Kiosks');
        $this->loadModel('KioskOrders');
        $this->loadModel('StockTransfer');
        $this->loadModel('KioskOrderProducts'); 
        $this->loadModel('DefectiveKioskReferences');
        $this->loadModel('DefectiveKioskTransients');
        $this->loadModel('Users');
        $this->loadModel('RevertStocks');
        $this->loadModel('ReservedProducts');
        $this->loadModel('KioskCancelledOrderProducts');
        $this->loadModel('MobileBlkReSales');
        $this->loadModel('Brands');
        $this->loadModel('MobileModels');
        $this->loadModel('KioskProductSales');
       $this->loadModel('MobileRepairParts');
        $this->loadModel('OrderDisputes');
        $this->loadModel('FaultyProducts');
         $this->loadModel('MobilePlacedOrders');
		 $this->loadModel('Categories');
		 $this->loadModel('OnDemandProducts');
		 
		$this->fromemail = Configure::read('FROM_EMAIL');
		$yesNoOptions = $active = Configure::read('yes_no');
		$refundOptions = $active = Configure::read('refund_status');
		
	$discountArr = [];
	$discountArr = [];
    for($i = 0; $i <= 50; $i++){
		if($i==0){
			$discountArr[0] = "None";
			continue;
		}
            $discountArr[$i] = "$i %";
    }
    
    for($i=0; $i<=50; $i++){
        $newDiscountArr[$i] = "$i %";
    }
    $discountOptions = Configure::read('discount');
    	$this->set('discountOptions',$discountOptions);
		$this->set('newDiscountArr',$newDiscountArr);
		$this->set('refundOptions',$refundOptions);
    }
    public function newOrder(){
			$path = dirname(__FILE__);
			$isboloRam = strpos($path,"fonerevive");
			if($isboloRam != false){
				$connection = ConnectionManager::get('hpwaheguru');
				$productTable_source = 'products';
				$productTable = TableRegistry::get($productTable_source,[
																	'table' => $productTable_source,
																	'connection' => $connection
																	]);
			}else{
				//$connection = ConnectionManager::get('hpwaheguru');
				$productTable_source = 'products';
				$productTable = TableRegistry::get($productTable_source,[
																	'table' => $productTable_source,
																	//'connection' => $connection
																]);
			}
		//pr($_SESSION);die;
		//echo "hi";die;
        	$currencySymbol = Configure::read('CURRENCY_TYPE');
		//$currencySymbol = $this->setting['currency_symbol'];
        
		$vat = $this->setting['vat'];
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		$this->paginate = array(
						'limit' => 20,
						'order' => array('product' => 'ASC'),
						//'conditions' => array('NOT'=>array('Product.quantity' => 0))
				);
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
		$session_basket = $this->request->Session()->read('on_demand_basket');
		$quantityError = '';
		if(array_key_exists('quantityError',$this->request->Session()->read())){
			$quantityError = $this->Session->read('quantityError');
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
				$productCodeArr_query = $productTable->find('all',array('conditions'=>array('product_code'=>$key),
																		'fields'=>array('id','product_code'))
														  );
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
					unset($_SESSION['on_demand_basket'][$key]);
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
				//$net_amount = $basketItem['net_amount'];
				//$price_without_vat = $basketItem['price_without_vat'];
				//$itemPrice = $basketItem['selling_price']/(1+$vatItem);
				//$discountAmount = $price_without_vat * $basketItem['discount'] / 100 * $basketItem['quantity'];
				//$totalDiscountAmount+= $discountAmount;
				//$totalItemPrice = $price_without_vat * $basketItem['quantity'];
				////$pricebeforeVat = $itemPrice*$basketItem['quantity']-$discountAmount;
				//$totalItemCost = $totalItemPrice-$discountAmount;
				//$totalBillingAmount+=$totalItemCost;
				//$vatperitem = $basketItem['quantity']*($sellingPrice-$itemPrice);
				//$bulkDiscountValue = $totalBillingAmount*$bulkDiscountPercentage/100;
				//$netBillingAmount = $totalBillingAmount-$bulkDiscountValue;
				//$netPrice = $netBillingAmount;
				//$vatAmount = $netBillingAmount * $vatItem;
				//round($netBillingAmount-$netPrice,2);
				
				//if($country=="OTH" || $specialInvoice == 1){
					//$finalAmount = $netBillingAmount;
				//}else{
					//$finalAmount = $netBillingAmount+$vatAmount;
				//}
				
				$basketStr.="<tr>
						<td >{$counter})</td>
						<td >{$key}</td>
						<td >".$basketItem['product']."</td>
						<td >".$basketItem['quantity']."</td>
						<td >".$currencySymbol.number_format($sellingPrice,2)."</td>";
			}
			//<td> ".$discount."</td>
			//			<td>".$currencySymbol.number_format($discountAmount,2)."</td>
			//			<td>".$currencySymbol.number_format($totalItemCost,2)."</td></tr>"
			if(!empty($basketStr)){
				 $external_sites = Configure::read('external_sites');
				$external_site_status = 0;
				$path = dirname(__FILE__);
				foreach($external_sites as $key => $val){
				  if($external_site_status == 1){
					continue;
				  }
				  if(strpos($path,$val)){
					$external_site_status = 1;
				  }
				}
				if($external_site_status == 1){
					$basketStr = "<table><tr>
							<th style='width: 25px;'>Sr No</th>
							<th style='width: 163px;'>Product Code</th>
							<th style='width:974px;'>Product</th>
							<th style='width: 83px;'>Quanity</th>
							<th>Cost price</th>
							</tr>".$basketStr."</table>";	
				}else{
					$basketStr = "<table><tr>
							<th style='width: 25px;'>Sr No</th>
							<th style='width: 163px;'>Product Code</th>
							<th style='width:974px;'>Product</th>
							<th style='width: 83px;'>Quanity</th>
							<th>Selling price</th>
							</tr>".$basketStr."</table>";
				}
				
							//<th>Price/Item</th>
							//<th>Discount %</th>
							//<th>Discount Value</th>
							//<th>Gross</th>
							
				//<tr><td colspan='7'>Bulk Discount ({$bulkDiscountPercentage}%)</td><td>".$currencySymbol.number_format($bulkDiscountValue,2)."</td></tr>
				//			<tr><td colspan='7'>Sub Total</td><td>".$currencySymbol.number_format($netBillingAmount,2)."</td></tr>
				//			<tr><td colspan='7'>Vat</td><td>".$currencySymbol.number_format($vatAmount,2)."</td></tr>
				//			<tr><td colspan='7'>Net Amount</td><td>".$currencySymbol.number_format($netPrice,2)."</td></tr>
				//			<tr><td colspan='7'>Total Amount</td><td>".$currencySymbol.number_format($finalAmount,2)."</td></tr></table>"			
				$productCounts = count($this->request->Session()->read('on_demand_basket'));
			
				if($productCounts){
					//$productCounts product(s) added to the cart. //$basketStr
					$flashMessage = "$quantityError <br/> Total item Count:$productCounts.<br/>$basketStr";
				}else{
					$flashMessage = "$quantityError <br/> No item added to the cart. Item Count:$productCounts";
				}
				
				if(array_key_exists('error',$session_basket)){
					$flashMessage = $session_basket['error']."<br/>".$flashMessage;
				}
				$this->Flash->error($flashMessage,['escape' => false]);
			}elseif(!empty($quantityError)){
				$this->Flash->error($quantityError,['escape' => false]);
			}
			
			if(array_key_exists('quantityError',$this->request->Session()->read())){
				$this->request->Session()->delete('quantityError');
			}
		}
		//-----------------------------------------
		
		$products_query = $this->paginate($productTable);
		$products = $products_query->toArray();
		$categoryIdArr = array();
		foreach($products as $key=>$product){
			$categoryIdArr[] = $product->category_id;
		}
		$categoryName_query = $this->Categories->find('list',array('conditions'=>array('Categories.id IN'=>$categoryIdArr),
																   'keyField' => 'id',
																   'valueField' => 'category',
																	));
		$categoryName_query = $categoryName_query->hydrate(false);
		if(!empty($categoryName_query)){
			$categoryName = $categoryName_query->toArray();
		}else{
			$categoryName = array();
		}
		$this->set(compact('categories','categoryName','products','vat'));
	}
	
	public function search($keyword = ""){
		$path = dirname(__FILE__);
			$isboloRam = strpos($path,"fonerevive");
			if($isboloRam != false){
				$connection = ConnectionManager::get('hpwaheguru');
				$productTable_source = 'products';
				$productTable = TableRegistry::get($productTable_source,[
																	'table' => $productTable_source,
																	'connection' => $connection
																	]);
			}else{
				$productTable_source = 'products';
				$productTable = TableRegistry::get($productTable_source,[
																	'table' => $productTable_source,
																	//'connection' => $connection
																]);
			}
		
        unset($_SESSION['on_demand_basket']);
		$session_basket = $this->request->Session()->read('on_demand_basket');
		$this->session_basket($session_basket);
		$searchKW = '';
		//$this->initialize_customer();
		if(array_key_exists('search_kw', $this->request->query)){
			$searchKW = $this->request->query['search_kw'];
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
		$conditionArr = array();
		//----------------------
		if(!empty($searchKW)){
			$conditionArr['OR']['LOWER(product) like '] =  strtolower("%$searchKW%");
			$conditionArr['OR']['LOWER(product_code) like '] =  strtolower("%$searchKW%");
			$conditionArr['OR']['LOWER(description) like '] =  strtolower("%$searchKW%");
			//'NOT'=>array('Product.quantity'=>0)
		}
		//$conditionArr['NOT'] =  array('Product.quantity' => 0);
		
		//----------------------
		if(array_key_exists('category',$this->request->query)){
			$category = $this->request->query['category'];
			if(isset($category)){
				$conditionArr['category_id IN'] =  $category;
			}
		}
		
		$this->paginate = array(
						'conditions' => $conditionArr,
						'limit' => ROWS_PER_PAGE
					);
        if(array_key_exists('category_id IN',$conditionArr)){
            $categories = $this->CustomOptions->category_options($categories,true,$conditionArr['category_id IN']);    
        }else{
            $categories = $this->CustomOptions->category_options($categories,true,array());
        }
		$vat = $this->VAT;
		$products = $this->paginate($productTable);
		$this->set(compact('products','categories','vat'));
		//$this->layout = 'default'; 
		//$this->viewPath = 'Products';
		$this->render('new_order');
		
	}
	
	public function session_basket($session_basket){
		$vat = $this->VAT;
		$currencySymbol = $this->setting['currency_symbol'];
		if(is_array($session_basket)){
			$basketStr = "";
			$counter = 0;
			$totalBillingAmount = 0;
			$totalDiscountAmount = 0;
			$vatAmount = 0;
			//pr($session_basket);
			$productCodeArr = array();
			//pr($session_basket);
			foreach($session_basket as $key => $basketItem){
				if($key == 'error')continue;
				$productCodeArr_query = $this->Products->find('all',array(
																	   
																	   'conditions' => array('Products.product_code' => $key),
																	   'fields' => array('id','product_code')
																	   )
														 );
				$productCodeArr_query = $productCodeArr_query->hydrate(false);
				if(!empty($productCodeArr_query)){
					$productCodeArr[] =$productCodeArr_query->first();
				}else{
					$productCodeArr[] = array();
				}
			}
			$productCode = array();
			if(!empty($productCodeArr)){
				//pr($productCodeArr);
				foreach($productCodeArr as $k => $productCodeData){
					$productCode[$productCodeData['id']] = $productCodeData['product_code'];
				}
			}
			foreach($session_basket as $key => $basketItem){
				if($basketItem['quantity'] == 0){
					unset($_SESSION['on_demand_basket'][$key]);
					$this->Flash->error("quantity cannot be zero");
					return;
				}
			}
			foreach($session_basket as $key => $basketItem){
				if($key == 'error')continue;
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
				//{$productCode[$key]}- rasu
				$basketStr.="<tr>
						<td>{$counter})</td>
						<td>{$key}</td>
						<td>".$basketItem['product']."</td>
						<td>".$basketItem['quantity']."</td>
						<!--td>".$currencySymbol.number_format($sellingPrice,2)."</td-->";
			}
			
			//<td>".number_format($discount,2)."</td>
			//			<td>".$currencySymbol.number_format($discountAmount,2)."</td>
			//			<td>".$currencySymbol.number_format($totalItemCost,2)."</td></tr>
			if(!empty($basketStr)){
				$basketStr = "<table><tr>
							<th style='width: 25px;'>Sr No</th>
							<th style='width: 163px;'>Product Code</th>
							<th style='width:974px;'>Product</th>
							<th style='width: 83px;'>Quanity</th>
							<!--th>Price/Item</th-->
							</tr>".$basketStr."
							</table>";
							
							
							//<th>Discount %</th>
							//<th>Discount Value</th>
							//<th>Gross</th>
							//<tr><td colspan='7'>Sub Total</td><td>".$currencySymbol.number_format($totalBillingAmount,2)."</td></tr>
							//<tr><td colspan='7'>Vat (".$vat."%)</td><td>".$currencySymbol.number_format($vatAmount,2)."</td></tr>
							//<tr><td colspan='7'>Net Amount</td><td>".$currencySymbol.number_format($netPrice,2)."</td></tr>
							//<tr><td colspan='7'>Total Amount</td><td>".$currencySymbol.number_format($totalBillingAmount,2)."</td></tr>
							
				$productCounts = count($this->request->Session()->read('on_demand_basket'));
				$priceStr = $this->request->Session()->read('priceStr');
			
				if($productCounts){
					//$productCounts product(s) added to the cart.
					$flashMessage = "Total item Count:$productCounts.<br/>$basketStr";
				}else{
					$flashMessage = "No item added to the cart. Item Count:$productCounts";
				}
				
				if(array_key_exists('error',$session_basket)){
					$flashMessage = $session_basket['error']."<br/>".$flashMessage;
				}
				$this->Flash->error($priceStr."<br/>".$flashMessage);
				$this->request->Session()->delete('priceStr');
			}elseif(empty($basketStr)){
				//case:session empty and error basket not empty
				if(array_key_exists('error',$session_basket)){
					$flashMessage = $session_basket['error']."<br/>";
					$this->Flash->error($flashMessage);
				}
				//else{
					//$flashMessage = "Something goes wrong: either basket is  empty or you may not have entered quantity for any of the products";
				//}
				//$this->Session->setFlash($flashMessage);
			}
			unset($_SESSION['Basket']['error']);
			$this->request->Session()->delete('Basket.error');
		}
	}
	
	public function sellProducts(){
		//pr($this->request);die;
		$updateQueries = array();
		//pr($this->request->data);die;
		$currencySymbol = $this->setting['currency_symbol'];
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		$user_id = $this->request->session()->read('Auth.User.id');	//rasa
		//$this->initialize_tables($kiosk_id);
		//creating missing tables
		
		$current_page = "";
		if(array_key_exists('current_page',$this->request['data'])){
			$current_page = $this->request['data']['current_page'];		
		}
		
		$productCounts = 0;
		$session_basket = $this->request->Session()->read('on_demand_basket');
		//--------------------------
		//$this->initialize_customer();
		if(array_key_exists('basket',$this->request['data'])){
			$productArr = array();
			$productID = 0;
			$error_str = "Not sufficient quantity for product code";
			//pr($this->request);die;
			//pr($this->request['data']['KioskProductSale']);die;//rasu
			foreach($this->request['data']['OnDemandOrders']['item'] as $key => $item){				
				if((int)$item){
					$discount = $this->request['data']['OnDemandOrders']['discount'][$key];
					$price = $this->request['data']['OnDemandOrders']['selling_price'][$key];
					$discountStatus = $this->request['data']['OnDemandOrders']['discount_status'][$key];
					$currentQuantity = $this->request['data']['OnDemandOrders']['p_quantity'][$key];
					$productID = $this->request['data']['OnDemandOrders']['product_id'][$key];
					$productCode = $this->request['data']['OnDemandOrders']['product_code'][$key]; //rasu
					$remarks = $this->request['data']['OnDemandOrders']['remarks'][$key];
					$productTitle = $this->request['data']['OnDemandOrders']['product'][$key];
					$quantity = $this->request['data']['OnDemandOrders']['quantity'][$key];
					$netAmount = $this->request['data']['OnDemandOrders']['net_amount'][$key];
					//custom price filled by kiosk user
					if(empty($netAmount)){$netAmount = $price;}
					if($quantity<=0){
						continue;
					}
					//if($currentQuantity <= 0 || $currentQuantity < $quantity){
					//	$error[] = $productCode;
					//	continue;
					//}
					
					if($productID){
						$priceCheck_query = $this->Products->find('all',array(
																		'conditions' => array('Products.product_code' => $productCode),
																		'fields' => array('selling_price','product')
																	)
										);
						$priceCheck_query = $priceCheck_query->hydrate(false);
						if(!empty($priceCheck_query)){
							$priceCheck = $priceCheck_query->first();
						}else{
							$priceCheck = array();
						}
					
						$originalPrice = $priceCheck['selling_price'];
						$product_name = $priceCheck['product'];
						$discountValue = $originalPrice * $discount/100;
						$minPrice = round($originalPrice-$discountValue,2);
						//echo "$price > $originalPrice";
						
						//if($price < $originalPrice){
						//	$flashMessage = "Selling price cannot be less than the base price";
						//	$this->Flash->error($flashMessage);
						//	return $this->redirect(array('action' => "new_order/page:$current_page"));
						//	die;
						//}elseif($netAmount != $price && $netAmount < $minPrice){
						//	$flashMessage = "Selling price cannot be less than the minimum allowed price";
						//	$this->Flash->error($flashMessage);
						//	return $this->redirect(array('action' => "new_order/page:$current_page"));
						//	die;
						//}elseif($price > $originalPrice){
						//	$priceFlash[] = "Price of $product_name has been updated to {$currencySymbol}$price";
						//	if(count($priceFlash)){
						//		$priceStr = implode("<br/>",$priceFlash);
						//		$this->request->Session()->write('priceStr',$priceStr);
						//	}
						//}elseif($netAmount > $price){
						//	$price = $netAmount;
						//}
					}
				
					if((int)$item ){ //&& $quantity <= $currentQuantity
						//$productArr[$productID] = array(
						$productArr[$productCode] = array(
										'quantity' => $quantity,
										'selling_price' => $price,
										'remarks' => $remarks,
										'product' => $productTitle,
										'discount' => $discount,
										'discount_status' => $discountStatus,
										'id' => $productID,
										);
						$productCounts++;
					}				
				}
			}
			$session_basket = $this->request->Session()->read('on_demand_basket');
			if(count($session_basket) >= 1){
				//adding item to the the existing session
				$sum_total = $this->add_arrays(array($productArr,$session_basket));
				$this->request->Session()->write('on_demand_basket', $sum_total);
				$session_basket = $this->request->Session()->read('on_demand_basket');				
			}else{
				//adding item first time to session
				//pr($productArr);die;
				if(count($productCounts))$this->request->Session()->write('on_demand_basket', $productArr);
			}
			
			$basketStr = "";
			$counter = $totalBillingAmount = $totalDiscountAmount = $vatAmount = 0;
			
			$session_basket = $this->request->Session()->read('on_demand_basket');
			if(is_array($session_basket)){
				$this->SessionRestore->append_2_backup_table($this->request->params['controller'], 'new_order', 'on_demand_basket', $session_basket, $kiosk_id);
				
				//$session_basket = array();
				unset($_SESSION['on_demand_basket']);
				//$productCounts = "";
				
				$productCodeArr = array();
				//pr($session_basket['error']);die;
				if(!empty($session_basket)){
					foreach($session_basket as $key => $basketItem){
						$productCodeArr_query = $this->Products->find('all',array(
																			   'conditions' => array('Products.product_code' => $key),
																			   'fields' => array('id','product_code'))
																 );
						$productCodeArr_query = $productCodeArr_query->hydrate(false);
						if(!empty($productCodeArr_query)){
							$productCodeArr[] = $productCodeArr_query->first();
						}else{
							$productCodeArr[] = array();
						}
					}
					//pr($productCodeArr);die;
					$productCode = array();
					foreach($productCodeArr as $k=>$productCodeData){
						$productCode[$productCodeData['id']] = $productCodeData['product_code'];
					}
					
					foreach($session_basket as $key => $basketItem){
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
							<td>".$basketItem['remarks']."</td>
							<!--td>".$currencySymbol.number_format($sellingPrice,2)."</td-->";
					}
				}
			}
			//<td>".number_format($discount,2)."</td>
			//			<td>".$currencySymbol.number_format($discountAmount,2)."</td>
			//			<td>".$currencySymbol.number_format($totalItemCost,2)."</td></tr>"
			if(!empty($basketStr)){
				$basketStr = "<table><tr>
							<th style='width: 20px;'>Sr No</th>
							<th style='width: 163px;'>Product Code</th>
							<th style='width:974px;'>Product</th>
							<th style='width: 83px;'>Quanity</th>
							<th style='width: 83px;'>Remarks</th>
							<!--th>Price/Item</th-->		
							</tr>".$basketStr."
							
							</table>";
			}
			//<th>Discount %</th>
			//				<th>Discount Value</th>
			//				<th>Gross</th>
		// <tr><td colspan='7'>Sub Total</td><td>".$currencySymbol.number_format($totalBillingAmount,2)."</td></tr>
		//					<tr><td colspan='7'>Vat (".$vat."%)</td><td>".$currencySymbol.number_format($vatAmount,2)."</td></tr>
		//					<tr><td colspan='7'>Net Amount</td><td>".$currencySymbol.number_format($netPrice,2)."</td></tr>
		//					<tr><td colspan='7'>Total Amount</td><td>".$currencySymbol.number_format($totalBillingAmount,2)."</td></tr>
			$totalItems = count($this->request->Session()->read('on_demand_basket'));
			
			if($productCounts){
				//$productCounts product(s) added to the cart.//$basketStr
				$flashMessage = "Total item Count:$totalItems.<br/>$basketStr";
			}else{
				$flashMessage = "";//"No item added to the cart. Item Count:$productCounts";
			}
			if(!empty($error)){
				$error_str .= implode(",",$error);
				$this->Flash->error($error_str,['escape' => false]);
			}
			//$this->Session->setFlash($flashMessage);
			return $this->redirect(array('controller' => 'OnDemandOrders','action' => "new_order/page:$current_page"));
		}elseif(array_key_exists('empty_basket',$this->request['data'])){
			unset($_SESSION['on_demand_basket']);
			if(true){
				//echo "hi";
				$this->SessionRestore->delete_from_session_backup_table($this->request->params['controller'], 'new_order', 'on_demand_basket', $kiosk_id);
			}
			$flashMessage = "Basket is empty; Add new items to cart!";
			$this->Flash->error($flashMessage);
			return $this->redirect(array('action' => "new_order/page:$current_page"));			
		}elseif(array_key_exists('checkout',$this->request['data'])){
			$session_basket = $this->request->Session()->read('on_demand_basket');
			if(!empty($session_basket)){
				return $this->redirect(array('action'=>"new_order_checkout"));
			}else{
				$this->Flash->error('Please add items to the basket');
				return $this->redirect(array('action' => "new_order/page:$current_page"));			
			}
		}elseif(array_key_exists('calculate',$this->request['data'])){
			return $this->redirect(array('action' => "new_order/page:$current_page"));
		}else{
			$counter1 = 0;
			$cat_res_query = $this->Products->find('list',array(
														  'keyField' => 'id',
														  'valueField' => 'category_id',
														  ));
			$cat_res_query = $cat_res_query->hydrate(false);
			if(!empty($cat_res_query)){
				$cat_res = $cat_res_query->toArray();
			}else{
				$cat_res = array();
			}
			$qantity_res_query = $this->Products->find('list',array(
															  'keyField' =>'id',
															  'valueField' => 'quantity',
															  ));
			if(!empty($qantity_res_query)){
				$qantity_res = $qantity_res_query->toArray();
			}else{
				$qantity_res = array();
			}
			$kiosk_id = $this->request->Session()->read('kiosk_id');
			if(empty($kiosk_id)){
				$flashMessage = "This Function Works Only On Kiosk";
				$this->request->Session()->delete('on_demand_basket');
				$this->Flash->error($flashMessage);
				$this->redirect(array('action' => "new_order/page:$current_page"));
				die;
			}
			$on_demand_products_detail = array();
			if(array_key_exists('on_demand_basket',$_SESSION)){
				$session_basket = $this->request->Session()->read('on_demand_basket');
				foreach($session_basket as $s_key => $s_value){
					$product_id = $s_value['id'];
					$remarks = $s_value['remarks'];
					$quantity = $s_value['quantity'];
					$cat_id = $cat_res[$product_id];
					$current_qty = $qantity_res[$product_id];
					if($quantity > $current_qty){
						//continue;
					}
					$on_demand_products_detail[$s_key] = array(
														'product_id' => $product_id,
														'category_id' => $cat_id,
														'quantity' => $quantity,
														'remarks' => $remarks,
													   );
				}
			}
			$product_table_data = array();
			if(array_key_exists('OnDemandOrders',$this->request->data)){
				foreach($this->request->data['OnDemandOrders']['item'] as $p_key => $p_value){
					if($p_value > 0){
						$p_qantity = $this->request->data['OnDemandOrders']['quantity'][$p_key];
						$p_product_id = $this->request->data['OnDemandOrders']['product_id'][$p_key];
						$p_product_code = $this->request->data['OnDemandOrders']['product_code'][$p_key];
						$p_remarks = $this->request->data['OnDemandOrders']['remarks'][$p_key];
						$p_cat_id = $cat_res[$p_product_id];
						$current_qty = $qantity_res[$p_product_id];
						if($p_qantity > $current_qty){
							//continue;
						}
						$product_table_data[$p_product_code] = array(
															'product_id' => $p_product_id,
															'category_id' => $p_cat_id,
															'quantity' => $p_qantity,
															'remarks' => $p_remarks,
													);
					}
				}
			}
			$sum_data = array();
			if(!empty($on_demand_products_detail) || !empty($product_table_data)){
				$sum_data = $this->add_arrays(array($on_demand_products_detail,$product_table_data));
			}
			// pr($sum_data);die;
			if(!empty($sum_data)){
				$user_id = $this->request->session()->read('Auth.User.id');
				$order_data = array(
									'kiosk_id' => $kiosk_id,
									'user_id' => $user_id
								);
				//pr($order_data);die;
				$OnDemandOrdersEntity = $this->OnDemandOrders->newEntity($order_data,['validate' => false]);
				$OnDemandOrdersEntity = $this->OnDemandOrders->patchEntity($OnDemandOrdersEntity,$order_data,['validate' => false]);
				if($this->OnDemandOrders->save($OnDemandOrdersEntity,['validate' => false])){
					$id = $OnDemandOrdersEntity->id;
					foreach($sum_data as $n_key => $n_value){
						$on_demand_products_details = array(
														'kiosk_id' => $kiosk_id,//1,
														'kiosk_placed_order_id' => $id,
														'product_id' => $n_value['product_id'],
														'category_id' => $n_value['category_id'],
														'quantity' => $n_value['quantity'],
														'org_qty' => $n_value['quantity'],
														'remarks' => $n_value['remarks'],
														);
					// pr($on_demand_products_details);die;
						$OnDemandProductsEntity = $this->OnDemandProducts->newEntity($on_demand_products_details,['validate' => false]);
						$OnDemandProductsEntity = $this->OnDemandProducts->patchEntity($OnDemandProductsEntity,$on_demand_products_details,['validate' => false]);
						if($this->OnDemandProducts->save($OnDemandProductsEntity)){
							$counter1 ++;
						}else{
							 $errors = array();
							  debug($OnDemandProductsEntity->errors());die;
							if(!empty($OnDemandProductsEntity->errors())){
								 foreach($OnDemandProductsEntity->errors() as $key){
									  foreach($key as $value){
										 $errors[] = $value;  
									  }
								 }
								 $this->Flash->error(implode("</br>",$errors),['escape' => false]);
							}
						}
					}
				}
			}else{
				//echo "ll";die;
				$flashMessage = "Failed to create order. <br />Please select quantity atleast for one product!";
				$this->Flash->error($flashMessage);
				$redirectTo = array('action' => "new_order/page:$current_page");	
			}
			if($counter1 > 0){
				//echo "lasasl";die;
                unset($_SESSION['on_demand_basket']);
				$this->request->Session()->delete('on_demand_basket');
                $kiosk_id = $this->request->Session()->read('kiosk_id');
                $this->SessionRestore->delete_from_session_backup_table("OnDemandOrders","new_order","on_demand_basket",$kiosk_id);
				$flashMessage = "Order created";
				$this->Flash->error($flashMessage);					
				return $this->redirect(array('action' => "new_order/page:$current_page"));
			}else{
				//echo "lassdsadasdasl";die;
				$flashMessage = "Failed to create order.";
				$this->Flash->error($flashMessage);					
				return $this->redirect(array('action' => "new_order/page:$current_page"));
			}
		}
	}
	
	private function add_arrays($arrays = array()){
		$allValues = array();
		$arrays = array_reverse($arrays,true);
			foreach($arrays as $sngArr){
				if(is_array($sngArr)){
					foreach($sngArr as $key => $value){
						if(!array_key_exists($key,$allValues))
							$allValues[$key] = $value;
					}
				}
			}
				//sort($allValues,SORT_STRING);
		return $allValues;
    }
	public function restoreSession($currentController = '', $currentAction = '', $session_key = '', $kioskId = '', $redirectAction = ''){
		if(!$redirectAction){
		    $redirectAction = $currentAction;
		}
		$status = $this->SessionRestore->restore_from_session_backup_table($currentController, $currentAction, $session_key, $kioskId);
		if($currentAction == 'new_sale'){
		    //$redirectAction = "new_sale/$redirectAction"; //we are passing customer id in redirect action from view, since passing full action "new_sale/$customer_id" was changing in url to % sign
		}
		
		if($status == 'Success'){
		    $msg = "Session succesfully retreived!";
		}else{
		    $msg = "Session could not be retreived!";
		}
		$this->Flash->success($msg);
		return $this->redirect(array('action' => $redirectAction));
	}
	
	public function newOrderCheckout(){
		$session_basket = $this->request->Session()->read('on_demand_basket');
		$productCodeArr = array();
		$productCodes = array();
		if(!empty($session_basket)){
			//$product_ids = array_keys($session_basket);//rasu
			$product_codes = array_keys($session_basket);
			$productCodeArr_query = $this->Products->find('all',array(
															  'conditions' => array(
																					//'Product.id' => $product_ids,
																					'Products.product_code IN' => $product_codes
																					),
															  'fields' => array('id','product_code','quantity'),
															  )
												  );
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
			//pr($productCodeArr);
			if(!empty($productCodeArr)){
				foreach($productCodeArr as $k => $productCodeData){
					$productIds[$productCodeData['id']] = $productCodeData['product_code'];
					$productCodes[$productCodeData['product_code']] = $productCodeData['quantity'];
				}
			}
		}
		
		$currencySymbol = $this->setting['currency_symbol'];
		//$customerAccountDetails = $this->Customer->find('first',array(
		//							'conditions'=>array('Customer.id'=>$customerId)
		//							)
		//						);
		
		//$country = $customerAccountDetails['Customer']['country'];
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
					return $this->redirect(array('action'=>'new_order_checkout'));
				}
				
				if(count($lowProducts) > 0){
					$this->Flash->error("Please choose  more than 0 for product : ".implode(",",$lowProducts) );
					return $this->redirect(array('action'=>'new_order_checkout'));
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
							'remarks' => $productData['remarks'],
							'product'  => $productData['product'] ,
							'discount'  => $productData['discount'] ,
							'discount_status'  => $productData['discount_status'] ,
							'receipt_required'  => $productData['receipt_required'] ,
							'bulk_discount'  => $productData['bulk_discount'] 
													);
						$counter++;
					}
					$this->request->Session()->delete('on_demand_basket');
					$this->request->Session()->write('on_demand_basket',$newArray);
					$this->Flash->success("Quantity has been  successfully updated");
					return $this->redirect(array('action'=>'new_order_checkout'));
				}
			}elseif(array_key_exists('edit_basket',$this->request->data)){
				return $this->redirect(array('action'=>"new_order"));
			}
		}
			 
		$this->set(compact('vat','country','currencySymbol','customerId','productCodes','productCodeArr','productIds'));
		// $this->set(compact('kiosks','products','productCodeArr','costArr','sellingArr','productNameArr'));
	}
	
	public function deleteProductFromSession1($product_id=""){
		//pr($_SESSION);die;
		if(array_key_exists('on_demand_basket',$_SESSION)){
			$session_basket =   $this->request->Session()->read('on_demand_basket');
			unset($_SESSION['on_demand_basket'][$product_id]);
			$this->SessionRestore->update_session_backup_table($this->request->params['controller'], 'new_order', 'on_demand_basket', $session_basket);
			if(!empty($session_basket)){
				return $this->redirect(array('action'=>'new_order_checkout'));die;
			}else{
				return $this->redirect(array('action'=>'new_order'));die;
			}
		}else{
			//pr($_SESSION);
			//die;
			if(!empty($session_basket)){
				return $this->redirect(array('action'=>'edit_receipt_checkout'));die;
			}else{
				return $this->redirect(array('action'=>'new_order'));die;
			}
		}
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
	
}
