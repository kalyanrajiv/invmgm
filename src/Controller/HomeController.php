<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Event\Event;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Network\Request;
use Cake\ORM\TableRegistry;
use Cake\Mailer\Email;
use Cake\I18n\Time;
use Cake\ORM\Behavior;
use Cake\Datasource\ConnectionManager;

class HomeController extends AppController{
    public $helpers = [
         'Paginator' => ['templates' => 'paginatortemplates']
         ];
    public function initialize()
    {
		//echo'hi';die;
        parent::initialize();
        $this->loadComponent('CustomOptions');
        $this->loadModel('Customers');
        $this->loadModel('Categories');
        $this->loadModel('Products');
        $this->loadModel('SessionBackups');
		$this->loadModel('PaymentDetails');
		$this->loadModel('Users');
        $this->loadModel('MobileBlkReSales');
        $this->loadModel('MobileBlkReSalePayments');
        $this->loadModel('MobileRepairSales');
        $this->loadModel('RepairPayments');
        $this->loadModel('MobileUnlockSales');
        $this->loadModel('UnlockPayments');
        $this->loadModel('KioskProductSales');
        $this->loadModel('ProductReceipts');
        $this->loadModel('ProductPayments');
        $this->loadModel('MobileReSales');
        $this->loadModel('MobileReSalePayments');
		$this->loadModel('Brands');
		$this->loadModel('MobileModels');
		$this->loadModel('WarehouseStock');
        $this->loadModel('CommentMobileUnlock');
        $this->loadModel('StockTransfer');
        $this->loadModel('MobileUnlockPrices');
        $this->loadModel('Networks');
        $this->loadModel('MobileUnlocks');
        $this->loadModel('MobileRepairs');
		$this->loadModel('MobileRepairLogs');
		$this->loadModel('Agents');
		$this->loadModel('OnDemandOrders');
		$this->loadModel('OnDemandProducts');
		$this->loadComponent('SessionRestore');
        $siteBaseURL = Configure::read('SITE_BASE_URL'); //rasu
        $CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
		$this->set(compact('CURRENCY_TYPE','siteBaseURL'));
		$this->managerLogin();
		
    }    
    public function bulkSale($customerID = 0){
		
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
		$this->set(compact('agents'));
        $vat = isset($this->setting['vat']) && !empty($this->setting['vat']) ? $this->setting['vat'] : 0;
		//$customer = $this->Customers->find('all', array(
		//												 'conditions' => array('id' => (int)$customerID),
		//												 'recursive' => -1,
		//												 )
		//								  );
        $query = $this->Customers->find('all', [
                                                'conditions' => array('id' => (int)$customerID),
												'recursive' => -1,
                                            ]);
		$query->hydrate(false);
        $customer = $query->first();
		if(count($customer) < 1){
			$flashMessage = "Invalid customer id";
			$this->Flash->error($flashMessage);
			$this->redirect(array('controller' => 'customers','action' => "index"));
		}
		if(!empty($customer)){
			$this->request->Session()->write('customer',$customer);
		}
       
		//-----------------------------------------
        
		$categories_query = $this->Categories->find('all',array(
								'fields' => array('id', 'category','id_name_path'),
                                                                'conditions' => array('Categories.status' => 1),
								'order' => 'Categories.category asc',
								'recursive' => -1
								));
        $categories_query->hydrate(false);
        $categories = $categories_query->toArray();
		$categories = $this->CustomOptions->category_options($categories,true);
		$this->set(compact('categories','customer','vat'));
    }
    
    public function getProductsJsonByTitle(){
		//$siteBaseURL = Configure::read('SITE_BASE_URL');
		$search = "";
		if(array_key_exists('search_kw',$this->request->query)){
			$search = strtolower($this->request->query['search_kw']);
		}
		$vat = $this->VAT;
		$categories = $categoryIDs = $prodArr = array();
		$fieldArr = array(
						  'id',
						  'product_code',
						  'product',
						  'color',
						  'category_id',
						  'image_dir',
						  'image',
						  'quantity',
						  'cost_price',
						  'selling_price',
						  'discount',
						  'discount_status',
						  );
		/*$this->Product->bindModel(array('hasOne' => array('Category' => array('className' => 'Category'))));*/
		if(!empty($search)){
			$kiosk_id = $this->request->Session()->read('kiosk_id');
			if($kiosk_id < 10000 && $kiosk_id != 0 && !empty($kiosk_id)){
				$Product_Source = "kiosk_{$kiosk_id}_products";
			}else{
				$Product_Source = "products";
			}
			$productTable = TableRegistry::get($Product_Source,[
																		'table' => $Product_Source,
																	]);
			
			$prodRecs_query = $productTable->find('all',array(
										 'conditions' => array("LOWER(product) like '%$search%' OR LOWER(product_code) like '%$search%'"),
										 'fields' => $fieldArr,
										));
             $prodRecs_query->hydrate(false);
             $prodRecs = $prodRecs_query->toArray();
			$categoryIDs = array();
			foreach($prodRecs as $prodRec){
				$categoryIDs[$prodRec['category_id']] = $prodRec['category_id'];
			}
		}
        
		if(count($categoryIDs)){
            $unique_cats = array_unique($categoryIDs);
			if(empty($unique_cats)){
				$unique_cats = array(0=>null);
			}
			$categories_query = $this->Categories->find('list',[
																'keyField' => 'id',
																'valueField' => 'category',
																'conditions' => ['id IN' => $unique_cats]
															   ]
                                                      );
			$categories_query = $categories_query->hydrate(false);
            if(!empty($categories_query)){
				$categories = $categories_query->toArray();
			}else{
				$categories = array();
			}
		}
		
		
		foreach($prodRecs as $prodRec){
			if($prodRec['quantity'] == 0){
				continue;
			}
			$imageDir = WWW_ROOT."files".DS.'Products'.DS.'image'.DS.$prodRec['id'].DS;
			$catID = $prodRec['category_id'];
			$categoryTitle = $categories[$catID];
			$prodRec['category_title'] =  $categoryTitle;
			//----------Image code------------------
			$imageName = 'thumb_'.$prodRec['image'];
			$largeImageName = 'vga_'.$prodRec['image'];
			$imageURL = "/thumb_no-image.png";
			$largeImageURL = $imageURL;
			$absoluteImagePath = $imageDir.$imageName;
			//echo $absoluteImagePath;die;
			if(file_exists($absoluteImagePath)){
				$siteBaseURL = "";
				$imageURL = "{$siteBaseURL}/files/Products/image/".$prodRec['id']."/$imageName"; //rasu
				$largeImageURL = "{$siteBaseURL}/files/Products/image/".$prodRec['id']."/$largeImageName"; //rasu
			}
			$prodRec['image_url'] = $imageURL;
			//----------Image code------------------
			
			//--------price without vat and discounted value---------
			$numerator = $prodRec['selling_price']*100;
			$denominator = $vat+100;
			$priceWithoutVat = $numerator/$denominator;
			if($prodRec['discount_status'] == 1){
				$disValue = $priceWithoutVat*$prodRec['discount']/100;
				$netVal = $priceWithoutVat - $disValue;
			}else{
				$netVal = 0;
			}
			$prodRec['discounted_value'] = round($netVal,2);
			$prodRec['price_without_vat'] = round($priceWithoutVat,2);
			//--------price without vat and discounted value---------
			
			$id = $prodRec['id'];
			$prodArr[$id] = $prodRec;
		}
		echo json_encode($prodArr);
		//$this->layout = false;
        $this->viewBuilder()->layout(false);
		die;
	}
    
    public function crGetProductsJsonByTitle(){
		//$siteBaseURL = Configure::read('SITE_BASE_URL');
		$search = "";
		if(array_key_exists('search_kw',$this->request->query)){
			$search = strtolower($this->request->query['search_kw']);
		}
		$vat = $this->VAT;
		$categories = $categoryIDs = $prodArr = array();
		$fieldArr = array(
						  'id',
						  'product_code',
						  'product',
						  'color',
						  'category_id',
						  'image_dir',
						  'image',
						  'quantity',
						  'cost_price',
						  'selling_price',
						  'discount',
						  'discount_status',
						  );
		/*$this->Product->bindModel(array('hasOne' => array('Category' => array('className' => 'Category'))));*/
		if(!empty($search)){
			$kiosk_id = $this->request->Session()->read('kiosk_id');
			if($kiosk_id < 10000 && $kiosk_id != 0 && !empty($kiosk_id)){
				$Product_Source = "kiosk_{$kiosk_id}_products";
			}else{
				$Product_Source = "products";
			}
			$productTable = TableRegistry::get($Product_Source,[
																		'table' => $Product_Source,
																	]);
			
			$prodRecs_query = $productTable->find('all',array(
										 'conditions' => array("LOWER(product) like '%$search%' OR LOWER(product_code) like '%$search%'"),
										 'fields' => $fieldArr,
										));
             $prodRecs_query->hydrate(false);
             $prodRecs = $prodRecs_query->toArray();
			$categoryIDs = array();
			foreach($prodRecs as $prodRec){
				$categoryIDs[$prodRec['category_id']] = $prodRec['category_id'];
			}
		}
        
		if(count($categoryIDs)){
            $unique_cats = array_unique($categoryIDs);
			if(empty($unique_cats)){
				$unique_cats = array(0=>null);
			}
			$categories_query = $this->Categories->find('list',[
																'keyField' => 'id',
																'valueField' => 'category',
																'conditions' => ['id IN' => $unique_cats]
															   ]
                                                      );
			$categories_query = $categories_query->hydrate(false);
            if(!empty($categories_query)){
				$categories = $categories_query->toArray();
			}else{
				$categories = array();
			}
		}
		
		
		foreach($prodRecs as $prodRec){
			if($prodRec['quantity'] == 0){
				//continue;
			}
			$imageDir = WWW_ROOT."files".DS.'Products'.DS.'image'.DS.$prodRec['id'].DS;
			$catID = $prodRec['category_id'];
			$categoryTitle = $categories[$catID];
			$prodRec['category_title'] =  $categoryTitle;
			//----------Image code------------------
			$imageName = 'thumb_'.$prodRec['image'];
			$largeImageName = 'vga_'.$prodRec['image'];
			$imageURL = "/thumb_no-image.png";
			$largeImageURL = $imageURL;
			$absoluteImagePath = $imageDir.$imageName;
			if(file_exists($absoluteImagePath)){
				$siteBaseURL = "";
				$imageURL = "{$siteBaseURL}/files/Products/image/".$prodRec['id']."/$imageName"; //rasu
				$largeImageURL = "{$siteBaseURL}/files/Products/image/".$prodRec['id']."/$largeImageName"; //rasu
			}
			$prodRec['image_url'] = $imageURL;
			//----------Image code------------------
			
			//--------price without vat and discounted value---------
			$numerator = $prodRec['selling_price']*100;
			$denominator = $vat+100;
			$priceWithoutVat = $numerator/$denominator;
			if($prodRec['discount_status'] == 1){
				$disValue = $priceWithoutVat*$prodRec['discount']/100;
				$netVal = $priceWithoutVat - $disValue;
			}else{
				$netVal = 0;
			}
			$prodRec['discounted_value'] = round($netVal,2);
			$prodRec['price_without_vat'] = round($priceWithoutVat,2);
			//--------price without vat and discounted value---------
			
			$id = $prodRec['id'];
			$prodArr[$id] = $prodRec;
		}
		echo json_encode($prodArr);
		//$this->layout = false;
        $this->viewBuilder()->layout(false);
		die;
	}
    
   function crAdd2CartShort(){
		extract($this->request->query);
		$quick_cart = $this->request->Session()->read('cr_quick_cart'); 
		$prodErrArr = $itemArr = array();
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		if($kiosk_id < 10000 && $kiosk_id != 0 && !empty($kiosk_id)){
			$productsTable = TableRegistry::get("kiosk_{$kiosk_id}_products",[
																		'table' => "kiosk_{$kiosk_id}_products",
																	]);
			//$this->Product->setSource("kiosk_{$kiosk_id}_products");
		}else{
			$productsTable = TableRegistry::get("products",[
																		'table' => "products",
																	]);
		}
        $prodRow_query = $productsTable->find('all', array('conditions' => array('product_code' => $product_code),'recursive' => -1));
        $prodRow_query->hydrate(false);
        if(!empty($prodRow_query)){
             $prodRow = $prodRow_query->first();
        }else{
            $prodRow = array();
        }
       
		//--------price without vat and discounted value---------
		$vat = $this->VAT;
		if(count($prodRow) >= 1){
			$numerator = $prodRow['selling_price']*100;
			$denominator = $vat+100;
			$priceWithoutVat = $numerator/$denominator;
			$priceWithoutVat = round($priceWithoutVat,2);
			if($prodRow['discount_status'] == 1){
				$disValue = $priceWithoutVat * $prodRow['discount']/100;
				$netVal = $priceWithoutVat - $disValue;
			}else{
				$netVal = 0;
			}
		}else{
			$prodErrArr['error'] = "Product either out of stock or invalid product code!";
		}
		
		if(is_array($quick_cart) && count($quick_cart) > 0){
			$items = $quick_cart['new_sale_basket'];
			if(count($prodRow) >= 1 ){
				//quanity should not be 0
				$prod_id = $prodRow['id'];
				//pr($quick_cart);echo "prod id : $prod_id";pr($items);
				if(array_key_exists($prod_id, $items)){
					//echo "updating quantity for added item";
					//updating only quantity
					
					//echo $items[$prod_id]['quantity'];echo "</br>";
					//	echo $quantity;die;
					$totalQty = $items[$prod_id]['quantity'] + $quantity;
					if($totalQty > $prodRow['quantity']){
						$availQty = $prodRow['quantity'];
						$items[$prod_id]['quantity'] = $totalQty; //updating qty
						$qantity_short = 0;
						$prodErrArr['error'] = "";
					}else{
						$items[$prod_id]['quantity'] = $totalQty; //updating qty
						$qantity_short = 0;
					}
					$quick_cart['position'] += 1; //updating position
					$items[$prod_id]['position'] = $quick_cart['position']; //updating position
					$items[$prod_id]['qantity_short'] = $qantity_short;
					$quick_cart['new_sale_basket'] = $items;
					///pr($quick_cart);die;
				}else{
					//add new item to existing cart
					//echo "adding item to existing cart";
					$qantity_short = 0;
					if(array_key_exists('position',$quick_cart)){
						$quick_cart['position'] += 1; //updating position
					}else{
						$quick_cart['position'] = 1; //updating position
					}
					
					if($quantity > $prodRow['quantity']){
						$availQty = $quantity;
						$quantity = $availQty; //updating qty
						$qantity_short = 0;
						$prodErrArr['error'] = "";
					}
					$items[$prod_id] = array(
											'product' => $prodRow['product'],
											'quantity' => $quantity, //coming from box adjacent to google suggest
											'discount_status' => $prodRow['discount_status'],
											'net_amount' => $prodRow['selling_price'],
											'selling_price' => $priceWithoutVat,//$prodRow['Product']['selling_price'],
											'discount' => '',
											'price_without_vat' => $priceWithoutVat,
											'product_code' => $prodRow['product_code'],
											'position' => $quick_cart['position'],
											'available_qantity' => $prodRow['quantity'],
											'discounted_value' => $netVal, //check
											'minimum_selling_price' => $netVal, //check
											//'price_without_vat' => $priceWithoutVat, //check
											'cost_price' => $prodRow['cost_price'],
											'type' => 'normal'
										);
					$items[$prod_id]['qantity_short'] = $qantity_short;
					$quick_cart['new_sale_basket'] = $items;
				}
			}else{
				$prodErrArr['error'] = "Product either out of stock or invalid product code!";
			}
		}else{
			//create new cart
			//echo "create new cart";
			if(count($prodRow) >= 1){
				//quanity should not be 0
				$quick_cart['position'] = 1;
				$prod_id = $prodRow['id'];
				$qantity_short = 0;
				if($quantity > $prodRow['quantity']){
					$availQty = $quantity;
					$quantity = $availQty; //updating qty
					$qantity_short = 0;
					$prodErrArr['error'] = "";
				}
				$itemArr[$prod_id] = array(
											'product' => $prodRow['product'],
											'quantity' => $quantity,
											'discount_status' => $prodRow['discount_status'],
											'net_amount' => $prodRow['selling_price'],
											'selling_price' => $priceWithoutVat,//$prodRow['Product']['selling_price'],
											'discount' => '',
											'price_without_vat' => $priceWithoutVat,
											'product_code' => $prodRow['product_code'],
											'position' => $quick_cart['position'],
											'available_qantity' => $prodRow['quantity'],
											'discounted_value' => $netVal, //check
											'minimum_selling_price' => $netVal, //check
											'price_without_vat' => $priceWithoutVat, //check
											'cost_price' => $prodRow['cost_price'],
											'type' => 'normal'
										);
				$items[$prod_id]['qantity_short'] = $qantity_short;
				$quick_cart['new_sale_basket'] = $itemArr;
			}else{
				$prodErrArr['error'] = "Product either out of stock or invalid product code!";
				$quick_cart = array('new_sale_basket' => array());
				$this->cr_reorder_cart($quick_cart, $prodErrArr);
				//echo json_encode(array('msg' => 'Nothing to restore'));
				//$this->layout = false;
				//die;
			}
		}
		$quick_cart['new_sale_bulk_discount'] = $bulk_discount;
		$quick_cart['receipt_required'] = $recept_req;
		$quick_cart['special_invoice'] = $special_invoice;
		$this->cr_reorder_cart($quick_cart, $prodErrArr);
	} 
    function add2CartShort(){
		extract($this->request->query);
		$quick_cart = $this->request->Session()->read('quick_cart');
		$prodErrArr = $itemArr = array();
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		if($kiosk_id < 10000 && $kiosk_id != 0 && !empty($kiosk_id)){
			$productsTable = TableRegistry::get("kiosk_{$kiosk_id}_products",[
																		'table' => "kiosk_{$kiosk_id}_products",
																	]);
			//$this->Product->setSource("kiosk_{$kiosk_id}_products");
		}else{
			$productsTable = TableRegistry::get("products",[
																		'table' => "products",
																	]);
		}
           
        $prodRow_query = $productsTable->find('all', array('conditions' => array('product_code' => $product_code),'recursive' => -1));
         $prodRow_query->hydrate(false);
        $prodRow = $prodRow_query->first();
       
		//--------price without vat and discounted value---------
		$vat = $this->VAT;
		if(count($prodRow) >= 1){
			$numerator = $prodRow['selling_price']*100;
			$denominator = $vat+100;
			$priceWithoutVat = $numerator/$denominator;
			if($prodRow['discount_status'] == 1){
				$disValue = $priceWithoutVat * $prodRow['discount']/100;
				$netVal = $priceWithoutVat - $disValue;
			}else{
				$netVal = 0;
			}
		}else{
			$prodErrArr['error'] = "Product either out of stock or invalid product code!";
		}
		
		if(is_array($quick_cart) && count($quick_cart) > 0){
			// pr($quick_cart);die;
			$items = $quick_cart['new_sale_basket'];
			if(count($prodRow) >= 1 && $prodRow['quantity']){
				//quanity should not be 0
				$prod_id = $prodRow['id'];
				//pr($quick_cart);echo "prod id : $prod_id";pr($items);
				if(array_key_exists($prod_id, $items)){
					//echo "updating quantity for added item";
					//updating only quantity
					$totalQty = $items[$prod_id]['quantity'] + $quantity;
					if($totalQty > $prodRow['quantity']){
						$availQty = $prodRow['quantity'];
						$items[$prod_id]['quantity'] = $availQty; //updating qty
						$qantity_short = 1;
						$prodErrArr['error'] = "Product Quantity adjusted to $availQty due to limited stock";
					}else{
						$items[$prod_id]['quantity'] = $totalQty; //updating qty
						$qantity_short = 0;
					}
					$quick_cart['position'] += 1; //updating position
					$items[$prod_id]['position'] = $quick_cart['position']; //updating position
					$items[$prod_id]['qantity_short'] = $qantity_short;
                   // pr($items);die;
					$quick_cart['new_sale_basket'] = $items;
				}else{
					//add new item to existing cart
					//echo "adding item to existing cart";
					$qantity_short = 0;
					if(array_key_exists('position',$quick_cart)){
						$quick_cart['position'] += 1; //updating position
					}else{
						$quick_cart['position'] = 1; //updating position
					}
					
					if($quantity > $prodRow['quantity']){
						$availQty = $prodRow['quantity'];
						$quantity = $availQty; //updating qty
						$qantity_short = 1;
						$prodErrArr['error'] = "Product Quantity adjusted to $availQty due to limited stock!";
					}
					$items[$prod_id] = array(
											'product' => $prodRow['product'],
											'quantity' => $quantity, //coming from box adjacent to google suggest
											'discount_status' => $prodRow['discount_status'],
											'net_amount' => $prodRow['selling_price'],
											'selling_price' => round($priceWithoutVat,2),//$prodRow['Product']['selling_price'],
											'discount' => '',
											'price_without_vat' => round($priceWithoutVat,2),
											'product_code' => $prodRow['product_code'],
											'position' => $quick_cart['position'],
											'available_qantity' => $prodRow['quantity'],
											'discounted_value' => round($netVal,2), //check
											'minimum_selling_price' => round($netVal,2), //check
											//'price_without_vat' => $priceWithoutVat, //check
											'cost_price' => $prodRow['cost_price'],
										);
					$items[$prod_id]['qantity_short'] = $qantity_short;
					$quick_cart['new_sale_basket'] = $items;
				}
			}else{
				$prodErrArr['error'] = "Product either out of stock or invalid product code!";
			}
		}else{
			//create new cart
			// echo "create new cart";die;
			if(count($prodRow) >= 1 && $prodRow['quantity']){
				//quanity should not be 0
				$quick_cart['position'] = 1;
				$prod_id = $prodRow['id'];
				$qantity_short = 0;
				if($quantity > $prodRow['quantity']){
					$availQty = $prodRow['quantity'];
					$quantity = $availQty; //updating qty
					$qantity_short = 1;
					$prodErrArr['error'] = "Product Quantity adjusted to $availQty due to limited stock!";
				}
				$itemArr[$prod_id] = array(
											'product' => $prodRow['product'],
											'quantity' => $quantity,
											'discount_status' => $prodRow['discount_status'],
											'net_amount' => $prodRow['selling_price'],
											'selling_price' => round($priceWithoutVat,2),//$prodRow['Product']['selling_price'],
											'discount' => '',
											'price_without_vat' => round($priceWithoutVat,2),
											'product_code' => $prodRow['product_code'],
											'position' => $quick_cart['position'],
											'available_qantity' => $prodRow['quantity'],
											'discounted_value' => round($netVal,2), //check
											'minimum_selling_price' => round($netVal,2), //check
											'price_without_vat' => round($priceWithoutVat,2), //check
											'cost_price' => round($prodRow['cost_price'],2),
										);
				$items[$prod_id]['qantity_short'] = $qantity_short;
				$quick_cart['new_sale_basket'] = $itemArr;
			}else{
				$prodErrArr['error'] = "Product either out of stock or invalid product code!";
				$quick_cart = array('new_sale_basket' => array());
				$this->reorder_cart($quick_cart, $prodErrArr);
				//echo json_encode(array('msg' => 'Nothing to restore'));
				//$this->layout = false;
				//die;
			}
		}
		$quick_cart['new_sale_bulk_discount'] = $bulk_discount;
		$quick_cart['receipt_required'] = $recept_req;
		$quick_cart['special_invoice'] = $special_invoice;
		$this->reorder_cart($quick_cart, $prodErrArr);die;
	}
    
    
    private function reorder_cart($quick_cart, $prodErrArr = array()){
		$items = $sortedItems = $posArr = array();
        if(array_key_exists("new_sale_basket",$quick_cart)){
            $items = $quick_cart['new_sale_basket'];    
        }
		
		if(!is_array($items)){
			$items = array();
		}else{
			foreach($items as $item){ $posArr[] = $item['position']; }
			rsort($posArr);
		
			foreach($posArr as $pos){
				foreach($items as $key => $item){
					if($item['position'] == $pos){
						$sortedItems[$key] = $item;
						unset($items[$key]);
						break;
					}
				}
			}
			$quick_cart['new_sale_basket'] = $sortedItems;
			$this->request->Session()->write('quick_cart',$quick_cart);
		}
		
		//pr($_SESSION);die;
		//$quick_cart['new_sale_basket'] = array($sortedItems);
		$cart = $quick_cart;
		if(count($prodErrArr)){
			$quick_cart['prodError'] = $prodErrArr['error'];
			echo $quickCart = json_encode($quick_cart);
		}else{
			echo $quickCart = json_encode($quick_cart);
		}
		$serilized_data = serialize($cart);
		//--------------------------------
		if(count($sortedItems) >= 1){
			$kiosk_id = $this->request->Session()->read('kiosk_id');
				if($kiosk_id == ""){
					$kiosk_id = 10000;	
				}
			$sessionRS_query = $this->SessionBackups->find('all', array('conditions' => array(
																				'controller' => 'Home',
																				'action' => 'reorder_cart',
																				'kiosk_id' => $kiosk_id,
																				'user_id' => $this->request->session()->read('Auth.User.id'),
																			)));
            $sessionRS = $sessionRS_query->first();
			if(count($sessionRS) >= 1){
				//update record
				$this->SessionBackups->id = $sessionRS->id;
				$data = array('session_detail' => $serilized_data);
				$data = $this->SessionBackups->patchEntity($sessionRS, $data);
				$this->SessionBackups->save($data);
			}else{
				//add record
				$kiosk_id = $this->request->Session()->read('kiosk_id');
				if($kiosk_id == ""){
					$kiosk_id = 10000;	
				}
				$sessionBackupData = array(
										'controller' => 'Home',
										'action' => 'reorder_cart',
										'session_key' => 'any_key',
										'session_detail' => $serilized_data,
										'user_id' => $this->request->session()->read('Auth.User.id'),
										'kiosk_id' => $kiosk_id
										);
				$SessionBackups = $this->SessionBackups->newEntity();
				$SessionBackups = $this->SessionBackups->patchEntity($SessionBackups, $sessionBackupData);
				$this->SessionBackups->save($SessionBackups);
			}
		}
		//--------------------------------
		//$this->layout = false;
		$this->viewBuilder()->layout(false);
		die;
	}
	
	public function clearCart(){
		$this->request->Session()->delete('quick_cart');
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		if($kiosk_id == ""){
			$kiosk_id = 10000;
		}
		$this->SessionRestore->delete_from_session_backup_table('Home', 'reorder_cart', 'any_key', $kiosk_id);
		echo json_encode(array('msg' => 'Cart Cleared'));
		$this->viewBuilder()->layout(false);
		die;
	}
	
	function deleteFromCart(){
		extract($this->request->query);
		$quick_cart = $this->request->Session()->read('quick_cart');
		if(array_key_exists($prod_id,$quick_cart['new_sale_basket'])){
			unset($_SESSION['quick_cart']['new_sale_basket'][$prod_id]);
		}
		$quick_cart['new_sale_basket'] = $_SESSION['quick_cart']['new_sale_basket'];
		$this->reorder_cart($quick_cart);
		$this->viewBuilder()->layout(false);
		die;
	}
	
	public function updateCart(){
		extract($this->request->query);
		if(empty($bulk)){$bulk = "";}
		$quick_cart = $this->request->Session()->read('quick_cart');
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		if($kiosk_id < 10000 && $kiosk_id != 0 && !empty($kiosk_id)){
            $product_source = "kiosk_{$kiosk_id}_products";
            
			//$this->Product->setSource("kiosk_{$kiosk_id}_products");
		}else{
            $product_source  = 'products';
        }
         $productTable = TableRegistry::get($product_source,[
                                                                    'table' => $product_source,
                                                                ]);
		$prodRow_query = $productTable->find('all', array('conditions' => array('id' => $prod_id),'recursive' => -1));
		$prodRow_first = $prodRow_query->first();
		$prodRow = $prodRow_first->toArray();
		
		$databse_qantity = $prodRow['quantity'];
		if(is_array($quick_cart) && count($quick_cart) > 0){
			if($qty > $databse_qantity){
				$allowed_qtity = $databse_qantity;
				$qantity_short = 1;
			}else{
				$allowed_qtity = $qty;
				$qantity_short = 0;
			}
			if(!array_key_exists('position', $quick_cart)){
				$quick_cart['position'] = 1;
			}else{
				$quick_cart['position'] += 1; //updating position
			}
			$quick_cart['new_sale_bulk_discount'] = $bulk;
			$items = $quick_cart['new_sale_basket'];
			$items[$prod_id]['quantity'] = $allowed_qtity;
			$items[$prod_id]['position'] = $quick_cart['position'];
			$items[$prod_id]['selling_price'] = $sp;
			$items[$prod_id]['qantity_short'] = $qantity_short;
			$quick_cart['new_sale_basket'] = $items;
			$quick_cart['receipt_required'] = $recit_req;
			$quick_cart['special_invoice'] = $special_invoice;
		}
		//pr($quick_cart);die;
		$this->reorder_cart($quick_cart);
		die;
	}
	
	public function updateBulk(){
		extract($this->request->query);
		$quick_cart = $this->request->Session()->read('quick_cart');
		if(!empty($quick_cart)){
			$quick_cart['new_sale_bulk_discount'] = $bulk;
			$quick_cart['special_invoice'] = $special_invoice;
			$this->request->Session()->write('quick_cart',$quick_cart);
		}else{
			$quick_cart['new_sale_bulk_discount'] = $bulk;
			$quick_cart['special_invoice'] = $special_invoice;
			$this->request->Session()->write('quick_cart',$quick_cart);
		}
		$this->reorder_cart($quick_cart);
		$this->viewBuilder()->layout(false);
		die;
	}
	
	
	function add2CartFull(){
		//$this->Session->delete('quick_cart');
		extract($this->request->query);
		$quick_cart = $this->request->Session()->read('quick_cart');
		$itemArr = array();
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		if($kiosk_id < 10000 && $kiosk_id != 0 && !empty($kiosk_id)){
			$Product_Source = "kiosk_{$kiosk_id}_products";
		}else{
			$Product_Source = "products";
		}
		$productTable = TableRegistry::get($Product_Source,[
																		'table' => $Product_Source,
																	]);
		$prodRow_query = $productTable->find('all', array('conditions' => array('id' => $prod_id)));
		$prodRow_first = $prodRow_query->first();
		$prodRow = $prodRow_first->toArray();
		//pr($prodRow);die;
		//Case: validation needs to be added for quantity check
		if(is_array($quick_cart) && count($quick_cart) > 0){
			$items = $quick_cart['new_sale_basket'];
			if(count($prodRow) >= 1){
				if(array_key_exists($prod_id, $items)){
					$cart_qty = $items[$prod_id]['quantity'];
					$orig_qty = $cart_qty + $qty;
					$databse_qantity = $prodRow['quantity'];
					if($orig_qty > $databse_qantity){
						$allowed_qtity = $databse_qantity;
						$qantity_short = 1;
					}else{
						$qantity_short = 0;
						$allowed_qtity = $orig_qty;
					}
					//updating only quantity
					$quick_cart['position'] += 1; //updating position
					$items[$prod_id]['quantity'] = $allowed_qtity;
					$items[$prod_id]['position'] = $quick_cart['position']; //updating position
					$items[$prod_id]['qantity_short'] = $qantity_short; // qantites are short
					$quick_cart['new_sale_basket'] = $items;
				}else{
					$databse_qantity = $prodRow['quantity'];
					if($qty > $databse_qantity){
						$allowed_qtity = $databse_qantity;
						$qantity_short = 1;
					}else{
						$allowed_qtity = $qty;
						$qantity_short = 0;
					}
					//add new item to existing cart
					$quick_cart['position'] += 1; //updating position
					$items[$prod_id] = array(
											'product' => $prodRow['product'],
											'quantity' => $allowed_qtity,
											'discount_status' => $prodRow['discount_status'],
											'net_amount' => $prodRow['selling_price'],
											'selling_price' => $sp,//$prodRow['Product']['selling_price'],
											'discount' => '',
											'price_without_vat' => '',
											'product_code' => $prodRow['product_code'],
											'position' => $quick_cart['position'],
											'minimum_selling_price' => $min_dis,
											'available_qantity' => $prodRow['quantity'],
											'cost_price' => $prodRow['cost_price'],
										);
					$items[$prod_id]['qantity_short'] = $qantity_short;
					$quick_cart['new_sale_basket'] = $items;
					$quick_cart['position'] = $quick_cart['position'];
				}
			}
		}else{
			//create new cart
			if(count($prodRow) >= 1){
				$databse_qantity = $prodRow['quantity'];
				if($qty > $databse_qantity){
					$allowed_qtity = $databse_qantity;
					$qantity_short = 1;
				}else{
					$allowed_qtity = $qty;
					$qantity_short = 0;
				}
				$quick_cart['position'] = 1;
				$itemArr[$prod_id] = array(
											'product' => $prodRow['product'],
											'quantity' => $allowed_qtity,
											'discount_status' => $prodRow['discount_status'],
											'net_amount' => $prodRow['selling_price'],
											'selling_price' => $sp,//$prodRow['Product']['selling_price'],
											'discount' => '',
											'price_without_vat' => '',
											'product_code' => $prodRow['product_code'],
											'position' => $quick_cart['position'],
											'minimum_selling_price' => $min_dis,
											'available_qantity' => $prodRow['quantity'],
											'cost_price' => $prodRow['cost_price'],
										);
				$items[$prod_id]['qantity_short'] = $qantity_short;
				$quick_cart['new_sale_basket'] = $itemArr;
			}else{
				echo json_encode(array('msg' => 'Nothing to restore'));
			}
		}
		if(isset($bulk_discount))$quick_cart['new_sale_bulk_discount'] = $bulk_discount;
		if(isset($recept_req))$quick_cart['receipt_required'] = $recept_req;
		if(isset($special_invoice))$quick_cart['special_invoice'] = $special_invoice;
		$this->reorder_cart($quick_cart);
	}
	
	
	public function restoreCart(){
		$quick_cart = $this->request->Session()->read('quick_cart');
		if(is_array($quick_cart) && count($quick_cart) > 0){
			//all good
			$this->reorder_cart($quick_cart);
		}else{
			echo json_encode(array('msg' => 'Nothing to restore'));
		}
		$this->viewBuilder()->layout(false);
		die;
	}
	
	public function restoreSessionDb(){
		$user_id = $this->request->session()->read('Auth.User.id');
		$kiosk_id = $this->request->Session()->read('kiosk_id');
				if($kiosk_id == ""){
					$kiosk_id = 10000;	
				}
		$sessionRS_query = $this->SessionBackups->find('all', array('conditions' => array(
																				'controller' => 'Home',
																				'action' => 'reorder_cart',
																				'kiosk_id' => $kiosk_id,
																				'user_id' => $user_id,
																			)));
		$sessionRS = $sessionRS_query->first();
		if(!empty($sessionRS)){
			$data = $sessionRS['session_detail'];
			$unserilizesd_data = unserialize($data);
			//echo json_encode($unserilizesd_data);
			$this->reorder_cart($unserilizesd_data);
		}else{
			$msg = array('msg' => "No Cart Found");
			echo json_encode($msg);
		}
		$this->viewBuilder()->layout(false);
		die;
	}
    
    
    
	
	public function saveInvoice(){
		//pr($_SESSION);die;
		$special_invoice = 0;
		if(array_key_exists("quick_cart",$_SESSION)){
			$quick_cart = $this->request->Session()->read('quick_cart');
			$special_invoice = $quick_cart['special_invoice'];
		}
		
		$product_id_array = $error = array();
		if(array_key_exists('quick_cart',$_SESSION)){
			if(array_key_exists('new_sale_basket',$_SESSION['quick_cart'])){
				$session_basket = $_SESSION['quick_cart']['new_sale_basket'];
				foreach($session_basket as $s_key => $s_value){
					$product_id_array[$s_key] = $s_key;
					$product_id_qan_array[$s_key] = $s_value['quantity'];
					if(!array_key_exists('net_amount',$session_basket[$s_key])){
						$error[] = "error";
					}
					if(!array_key_exists('selling_price',$session_basket[$s_key])){
						$error[] = "error";
					}
					if(!array_key_exists('quantity',$session_basket[$s_key])){
						$error[] = "error";
					}
					if(!array_key_exists('cost_price',$session_basket[$s_key])){
						$error[] = "error";
					}
					
				}
			}
		}
		
		if(!empty($error)){
			$this->Flash->success("invoice could not be saved(some keys are missing)");
			return $this->redirect(array('controller'=>'customers','action'=>'index'));die;
		}
		
		
		$kioskId = $this->request->Session()->read('kiosk_id');
		if(empty($kioskId)){
			$product = 'products';
			$paymentSource = 'payment_details';
		}else{
			$product = "kiosk_{$kioskId}_products";
			$paymentSource = "kiosk_{$kioskId}_payment_details";
		}
		
		$productTable = TableRegistry::get($product,[
                                                                    'table' => $product,
                                                                ]);
		if(empty($product_id_array)){
			$product_id_array  = array(0 => null);
		}
		$res_query = $productTable->find('all',array(
												'fields' => array('id','quantity','product'),
												'conditions' => array(
															   'id IN' => $product_id_array
															   )
												));
		$res_query = $res_query->hydrate(false);
		if(!empty($res_query)){
			$res = $res_query->toArray();
		}else{
			$res = array();
		}
		$error = $qtyArr = array();
		//$error = "";
		foreach($res as $key => $value){
			if(array_key_exists($value['id'],$product_id_qan_array)){
				if($product_id_qan_array[$value['id']] > $value['quantity']){
					$pr_code = $value['product'];
					$qty = $value['quantity'];
					$error[] = "Product code:{$pr_code}. have not sufficient quantity.You can add maximum {$qty} for this code";
					//$error[] = "Qantity not available for product code {$pr_code}";
				}
			}
		}
		if(!empty($error)){
			$s = implode("<br/>",$error);
			$this->Flash->error("$s");
			return $this->redirect(array('controller'=>'customers','action'=>'index'));
		}
		
		
		if($special_invoice == 1){
			//$this->PaymentDetail->setSource('t_payment_details');
			$paymentSource = "t_payment_details";
		}
		//$this->PaymentDetails->setSource($paymentSource);
		
		$paymentTable = TableRegistry::get($paymentSource,[
                                                            'table' => $paymentSource,
										   ]);
		//die;
		//Step 1: after payment submission from ajax screen
		//In this function we saved payment details without generating receipt according to payment method choosen.
		if(array_key_exists("quick_cart",$_SESSION)){
			$quick_cart = $this->request->Session()->read('quick_cart');
			$quick_cart['new_sale_bulk_discount'] = $this->request->data['bulk_discount_input'];
			$this->request->Session()->write('quick_cart',$quick_cart);
		}
		//pr($_SESSION);die;
		$counter = $amountToPay = 0;
		$totalPaymentAmount = 0;
		if(array_key_exists('final_amount',$this->request->data)){
			$amountToPay = $this->request->data['final_amount'];
		}
		
		//echo $amountToPay;die;
		foreach($this->request['data']['data']['Payment']['Amount'] as $key => $paymentAmount){
			$totalPaymentAmount += floatval($paymentAmount);
		}
		$amountToPay = round($amountToPay,2);
		$totalPaymentAmount = round($totalPaymentAmount,2);
		
		foreach($this->request['data']['data']['Payment']['Payment_Method'] as $key => $paymentMethod){
			if(
				$totalPaymentAmount < $amountToPay &&
				($paymentMethod == "Cheque" || $paymentMethod == "Cash" || $paymentMethod == "Bank Transfer" || $paymentMethod == "Card")){
				$error[] = "Amount must be equivalent to &#163; {$amountToPay}. Please try again";
				break;
			}elseif($totalPaymentAmount > $amountToPay &&
				($paymentMethod == "Cheque" ||
				$paymentMethod == "Cash" ||
				$paymentMethod == "Bank Transfer" ||
				$paymentMethod == "Card")){
				$error[] = "Amount must be equivalent to &#163; {$amountToPay}. Please try again";
				break;
			}elseif($totalPaymentAmount < $amountToPay && $paymentMethod == "On Credit"){
				$error[] = "Amount must be equivalent to &#163; {$amountToPay}. Please try again";
				break;
			}
		}
		
		if(!empty($error)){
				$errorStr = implode("<br/>",$error);
				$this->Flash->success("$errorStr");
				return $this->redirect(array('controller'=>'customers','action'=>'index'));
		}
		
		$id = array();
		$agentId = (int)$_SESSION['customer']['agent_id'];//added by yamini
		if($this->request['data']['data']['Payment']['Payment_Method'][0] == "On Credit"){
			$paymentDetailData = array(
										//'product_receipt_id' => $id,//by rajiv
										'payment_method' => $this->request['data']['data']['Payment']['Payment_Method'][0],
										'description' => 'On Credit',//$this->request['data']['Payment']['Description'][0],
										'amount' => $amountToPay,
										'payment_status' => 0,
										'status' => 1,
										'agent_id'=>$agentId//added by yamini
									);
			if($special_invoice == 1){
				//$this->PaymentDetail->setSource('t_payment_details');
				if(!empty($kioskId)){
					$paymentDetailData['kiosk_id'] = $kioskId;
				}else{
					$paymentDetailData['kiosk_id'] = 0;
				}
				
			}
			//pr($paymentDetailData);die;
			$paymentTable->behaviors()->load('Timestamp');
			$PaymentDetails = $paymentTable->newEntity();
				$PaymentDetails = $paymentTable->patchEntity($PaymentDetails, $paymentDetailData);
				if($paymentTable->save($PaymentDetails)){
					$id[] = $PaymentDetails->id;
					$counter++;
				}
			//This needs to be refined. If first method is on credit others can be by cheque, by cash or any other = pending
		}else{
			foreach($this->request['data']['data']['Payment']['Amount'] as $key => $paymentAmount){
				$paymentMethod = $this->request['data']['data']['Payment']['Payment_Method'][$key];
				if($paymentMethod == "On Credit"){
					$payment_status = 0;
				}else{
					$payment_status = 1;
				}
				
				if(!empty($paymentAmount)){
					$paymentDetailData = array(
												'payment_method' => $paymentMethod,
												'amount' => $paymentAmount,
												'payment_status' => $payment_status,
												'status' => 1,
												'agent_id'=>$agentId//added by yamini
											);
					if($special_invoice == 1){
						//$this->PaymentDetail->setSource('t_payment_details');
						if(!empty($kioskId)){
							$paymentDetailData['kiosk_id'] = $kioskId;
						}else{
							$paymentDetailData['kiosk_id'] = 0;
						}
						
					}
					$paymentTable->behaviors()->load('Timestamp');
					$PaymentDetails = $paymentTable->newEntity();
					$PaymentDetails = $paymentTable->patchEntity($PaymentDetails, $paymentDetailData);
					if($paymentTable->save($PaymentDetails)){
						$id[] = $PaymentDetails->id;
						$counter++;
					}
				}
			}
		}
		
		//Call for step 2
		$this->save_receipt($id,$amountToPay);
	}
	
	public function save_receipt($id = array(),$amountToPay = ""){
		$conn = ConnectionManager::get('default');
		//Step 2: here we are checking if vat is applicable to customer and saving customer information in product_receipts table
		//here we are grabbing cost of each product from session and multiplying that with quanity and calculating total cost and we are saving this cost in product_receipts table.
		//Here we are also fetching bulk_discount from session array and we saved that  bulk discount simply in product_receipts table.
		//Here we are saving amountToPay (which is including vat if applicable to customer)
		//Case: if we are missing customer information in session we are redirecting to customer screen.
		//We need to delete $id from payment table for above case
		
		$special_invoice = 0;
		if(array_key_exists("quick_cart",$_SESSION)){
			$quick_cart = $this->request->Session()->read('quick_cart');
			$special_invoice = $quick_cart['special_invoice'];
		}
		
		$kioskId = $this->request->Session()->read('kiosk_id');
		if(empty($kioskId)){
			$receiptSource = 'product_receipts';
		}else{
			$receiptSource = "kiosk_{$kioskId}_product_receipts";
		}
		
		if($special_invoice == 1){
			$receiptSource = "t_product_receipts";
		}
		
		//echo $receiptSource;die;
		$recitTable = TableRegistry::get($receiptSource,[
                                                            'table' => $receiptSource,
										   ]);
		//$this->Receipt->setSource($receiptSource);
		
		 //pr($_SESSION);die;
		if(array_key_exists('customer',$_SESSION) && (float)$amountToPay > 0){
			$cutomerInfo = $_SESSION['customer'];
            // pr($cutomerInfo);die;
			if(!empty($cutomerInfo)){
				
				$total_cost = $bulk_discount = 0;
				$vat = $this->VAT;
				if($cutomerInfo['country'] == "OTH"){
					$vat = 0;
				}
				
				if(array_key_exists('quick_cart',$_SESSION)){
					if(array_key_exists('new_sale_bulk_discount',$_SESSION['quick_cart'])){
						$bulk_discount = $_SESSION['quick_cart']['new_sale_bulk_discount'];
					}
					$session_basket = $_SESSION['quick_cart']['new_sale_basket'];
					foreach($session_basket as $pId => $value){
						$total_cost += $value['cost_price'] * $value['quantity'];
					}
				}
				$receiptData = array(
										'customer_id' => $cutomerInfo['id'],
										'address_1' => $cutomerInfo['address_1'],
										'address_2' => $cutomerInfo['address_2'],
										'city' => $cutomerInfo['city'],
										'state' => $cutomerInfo['state'],
										'zip' => $cutomerInfo['zip'],
										'vat' =>$vat,
										'bill_cost' => $total_cost,
										'bill_amount' => $amountToPay,
										'orig_bill_amount' => $amountToPay,
										'bulk_discount' => $bulk_discount,
										'processed_by' =>$this->Auth->user('id'),
										'fname' => $cutomerInfo['fname'],
										'lname' => $cutomerInfo['lname'],
										'mobile' => $cutomerInfo['mobile'],
										'email' => $cutomerInfo['email'],
										'status'=> 0,
										'sale_type' => 1,
										'agent_id' => $cutomerInfo['agent_id']//added by yamini
									);
				
				if($special_invoice == 1){
					if(empty($kioskId)){
						$receiptData['kiosk_id'] = 0;
					}else{
						$receiptData['kiosk_id'] = $kioskId;
					}
					
				}
				$recitTable->behaviors()->load('Timestamp');
				$recitDetails = $recitTable->newEntity();
				$recitDetails = $recitTable->patchEntity($recitDetails, $receiptData);
				if($recitTable->save($recitDetails)){
					$receipt_id = $recitDetails->id;
					//Step 3: here we are saving product entries which we are grabbing from session and saving reference of receipt id in payment details table.
					$this->save_kiosk_sale($receipt_id,$id);
				}
                
			}
		}else{
			//Delete from payment table for variable $id. - Pending
			if(!empty($id)){
				$kioskId = $this->request->Session()->read('kiosk_id');
				if(empty($kioskId)){
					$paymentTable = "payment_details";
				}else{
					$paymentTable = "kiosk_{$kioskId}_payment_details";
				}
				foreach($id as $key => $value1){
					$paymentTable = "payment_details";
					$updateQry = "DELETE FROM `$paymentTable`  WHERE `$paymentTable`.`id` = '$value1'";
					$stmt = $conn->execute($updateQry);
                   // $this->PaymentDetail->query($updateQry);
				}
			}
			$this->Flash->success("Failed to generate invoice for the case either amount is 0 or customer information is missing");
			return $this->redirect(array('controller'=>'customers','action'=>'index'));
		}
	}
	
	
	public function save_kiosk_sale($receipt_id,$id = array()){
		$conn = ConnectionManager::get('default');
		$special_invoice = 0;
		if(array_key_exists("quick_cart",$_SESSION)){
			$quick_cart = $this->request->Session()->read('quick_cart');
			$special_invoice = $quick_cart['special_invoice'];
		}
		$kioskId = $this->request->Session()->read('kiosk_id');
		if(empty($kioskId)){
			$kioskId = 0;
			$paymentSource = 'payment_details';
			$receiptSource = 'product_receipts';
			$productSaleSource = 'kiosk_product_sales';
			$product = 'products';
		}else{
			$paymentSource = "kiosk_{$kioskId}_payment_details";
			$receiptSource = "kiosk_{$kioskId}_product_receipts";
			$productSaleSource = "kiosk_{$kioskId}_product_sales";
			$product = "kiosk_{$kioskId}_products";
		}
		if($special_invoice == 1){
			$paymentSource = "t_payment_details";
			$receiptSource = "t_product_receipts";
			$productSaleSource = "t_kiosk_product_sales";
		}
		//$this->KioskProductSale->setSource($productSaleSource);
		//$this->PaymentDetail->setSource($paymentSource);
		//$this->Receipt->setSource($receiptSource);
		//$this->Product->setSource($product);
		
		$kiosk_saleTable = TableRegistry::get($productSaleSource,[
                                                                    'table' => $productSaleSource,
                                                                ]);
		$paymentTable = TableRegistry::get($paymentSource,[
                                                                    'table' => $paymentSource,
                                                                ]);
		$recitTable = TableRegistry::get($receiptSource,[
                                                                    'table' => $receiptSource,
                                                                ]);
		$productTable = TableRegistry::get($product,[
                                                                    'table' => $product,
                                                                ]);
		
		
		$new_kiosk_id = $kioskId;
		if(empty($new_kiosk_id)){
			$new_kiosk_id = 10000;
		}
		
		$new_kiosk_data = $this->Kiosks->find("all",['conditions' => ['id' => $new_kiosk_id]])->toArray();
		//Here after processing invoice we are deleting all related sessions
		$vat = $this->VAT;$counter = 0;
		if(array_key_exists('quick_cart',$_SESSION)){
			if(array_key_exists('new_sale_basket',$_SESSION['quick_cart'])){
				$session_basket = $_SESSION['quick_cart']['new_sale_basket'];
			}
		}
       // pr($_SESSION);die;
		if(array_key_exists('customer',$_SESSION)){
			$cutomerInfo = $_SESSION['customer'];
			if(!empty($cutomerInfo)){
				$customer_id = $cutomerInfo['id'];
			}
		}
		$bulk_dis = 0;
		if(array_key_exists('quick_cart',$_SESSION)){
			if(array_key_exists('new_sale_bulk_discount',$_SESSION['quick_cart'])){
				$bulk_dis = $_SESSION['quick_cart']['new_sale_bulk_discount'];
			}
		}
		$country = "";
		$vat = $this->VAT;
		if(array_key_exists('customer',$_SESSION)){
			$cutomerInfo = $_SESSION['customer'];
			if(!empty($cutomerInfo)){
				$country = $cutomerInfo['country'];
			}
		}
		//pr($session_basket);die;
		$p_kiosk_id = $this->request->Session()->read('kiosk_id');
			if($p_kiosk_id == 0 || $p_kiosk_id == ''){
				$p_kiosk_id = 10000;
			}
		if(!empty($session_basket)){
           // pr($session_basket);die;
			foreach($session_basket as $key => $value){
				//In this loop we are saving each product purchase with quanity in kiosk_product_sales table for the data we grabbed from session and saving receipt id generated in step 2 and updating quantity as well.
				$quantity = $withVatValue = $numerator = $denominator = $priceWithoutVat = $sold_price = $firstVal = $discountPercentage = 0;
				$withVatValue = $value['net_amount'];
				$numerator = $withVatValue*100;
				$denominator = $vat+100;
				$priceWithoutVat = $numerator/$denominator;
				$sold_price = $value['selling_price'];
				$firstVal = $priceWithoutVat - $sold_price;
				//echo "$firstVal/$priceWithoutVat*100";echo "</br>";
				$discountPercentage = $firstVal/$priceWithoutVat*100;
				if($discountPercentage > 0){
					$dis_status = 1;
				}else{
					$dis_status = 0;
				}
				$quantity = $value['quantity'];
				$group = $this->request->session()->read('Auth.User.group_id');
				$user_type = $this->request->session()->read('Auth.User.user_type');
				
				if($group == ADMINISTRATORS || $group == MANAGERS || $group == SALESMAN){
					$sale_type = 1;
				}else{
					if($group == KIOSK_USERS && $user_type =='wholesale'){
						$sale_type = 1;
					}else{
						$sale_type = 0;
					}
				}
				
				
				$kiosk_data = array(
										'kiosk_id' => $kioskId,
										'product_id' => $key,
										'customer_id' => $customer_id,
										'quantity' => $value['quantity'],
										'cost_price' => $value['cost_price'],//Undefined index: cost_price
										'sale_price' =>$priceWithoutVat,
										'discount' => $discountPercentage,
										'sold_by' =>$this->Auth->user('id'),
										'discount_status' => $dis_status,
										'product_receipt_id' =>$receipt_id,
										'sale_type' => $sale_type,
									);
				//pr($kiosk_data);die;
				//Start: newly added by rajiv on Oct 10, 2016
				$kiosk_id = $this->request->Session()->read('kiosk_id');
				if($kiosk_id < 10000 && $kiosk_id != 0 && !empty($kiosk_id)){
					if($special_invoice == 1){
						$kiosk_saleTable = TableRegistry::get("t_kiosk_product_sales",[
																					   'table' => 't_kiosk_product_sales'
																					   ]);
					}else{
						$kiosk_saleTable = TableRegistry::get("kiosk_{$kiosk_id}_product_sales",[
																					   'table' => "kiosk_{$kiosk_id}_product_sales"
																					   ]);
					}
				}
				//end: newly added by rajiv on Oct 10, 2016
				$kiosk_saleTable->behaviors()->load('Timestamp');
				$kiosk_saleDetails = $kiosk_saleTable->newEntity();
				$kiosk_saleDetails = $kiosk_saleTable->patchEntity($kiosk_saleDetails, $kiosk_data);
				if($kiosk_saleTable->save($kiosk_saleDetails)){
                     $productTable = TableRegistry::get($product,[
																		'table' => $product,
																	]);
					$product_code_query =  $productTable->find('list',[
																		'conditions' => ['id' => $key],
																		'keyField' => 'id',
																		'valueField' => 'product_code'
																		]
															   );
					$product_code_query = $product_code_query->hydrate(false);
					if(!empty($product_code_query)){
						$product_code = $product_code_query->toArray();
					}else{
						$product_code = array();
					}
					if($bulk_dis > 0){
						$bulk_value =  $value['selling_price'] * ($bulk_dis/100);
						$after_bulk_value = $value['selling_price'] - $bulk_value;
						$selling_price_without_vat = $after_bulk_value * $value['quantity'];
					}else{
						$after_bulk_value =  $value['selling_price'];
						$selling_price_without_vat = $value['selling_price'] * $value['quantity'];
					}
					if($country != 'OTH'){
						$vat_value = $after_bulk_value * ($vat/100);
						$total_vat = $vat_value * $value['quantity'];
					}else{
						$total_vat = 0;
					}
					
					 $data = array(
									'quantity' => $value['quantity'],
									'product_code' => $product_code[$key],
									'selling_price_withot_vat' => $selling_price_without_vat,
									'vat' => $total_vat
					   );
					  if($special_invoice == 1){
						$is_special = 1;
					 }else{
						$is_special = 0;
					 }
					$this->insert_to_ProductSellStats($key,$data,$p_kiosk_id,$operations = '+',$is_special);
					//Start: newly added by rajiv on Oct 10, 2016
					if($kiosk_id < 10000 && $kiosk_id != 0 && !empty($kiosk_id)){
						$productTable = "kiosk_{$kiosk_id}_products";
						
					}else{
						$productTable = "products";
						
					}
					
					//end: newly added by rajiv on Oct 10, 2016
					$updateQry = "UPDATE `$productTable` SET `quantity` = `quantity` - $quantity WHERE `$productTable`.`id` = '$key'";
					$stmt = $conn->execute($updateQry);
					//$this->Product->query($updateQry);
					$counter++;
				}
			}
			
			$count = 0;
			if($counter > 0){
				
				//newly added by rajiv on Oct 10, 2016
				$kiosk_id = $this->request->Session()->read('kiosk_id');
				if($kiosk_id < 10000 && $kiosk_id != 0 && !empty($kiosk_id)){
					$productTable = TableRegistry::get("kiosk_{$kiosk_id}_products",[
																					 'table' => "kiosk_{$kiosk_id}_products"
																					 ]);
					//$this->Product->setSource("kiosk_{$kiosk_id}_products");
					if($special_invoice == 1){
						$recitTable = TableRegistry::get("t_product_receipts",[
																			   'table' => "t_product_receipts"
																			   ]);
						$paymentTable = TableRegistry::get("t_payment_details",[
																			   'table' => "t_payment_details"
																			   ]);
					}else{
						$recitTable = TableRegistry::get("kiosk_{$kiosk_id}_product_receipts",[
																							   'table' => "kiosk_{$kiosk_id}_product_receipts"
																							   ]);
						$paymentTable = TableRegistry::get("kiosk_{$kiosk_id}_payment_details",[
																								'table' => "kiosk_{$kiosk_id}_payment_details"
																								]);
						//$this->ProductReceipt->setSource("kiosk_{$kiosk_id}_product_receipts");
						//$this->PaymentDetail->setSource("kiosk_{$kiosk_id}_payment_details");
					}
					
				}
				//newly added by rajiv
				
				if(!empty($id)){
					foreach($id as $key => $value1){
						//here we are updating receipt_id generated in step 2 for payment detail table.
						//$paymentTable->id = $value1;
						$query = $paymentTable->query();
						$query->update()
							->set(['product_receipt_id' => $receipt_id])
							->where(['id' => $value1])
							->execute();
						//$paymentTable->saveField('product_receipt_id',$receipt_id);
						$count++;
					}
					
					if($count > 0){
						$options = array(
								'conditions' => array('id' => $receipt_id),
								'recursive' => 1
							);
						//$productReceipt_query = $recitTable->find('all', $options)->contain(['Customers']);    //KioskProductSale
						
						$productReceipt_query =  $recitTable->find('all',$options);
                                    //->contain(['KioskProductSales' => function($q) {   //'KioskProductSales'
                                     //   $q->limit(10);  
                                     //   return $q;
                                    //},'Customers'    
                                //]
								//);
						$productReceipt_query = $productReceipt_query->hydrate(false);
						if(!empty($productReceipt_query)){
                            $productReceipt = $productReceipt_query->first();
						}else{
                            $productReceipt =  array();
                        }
						if(!empty($productReceipt)){
							$customer_id = $productReceipt['customer_id'];
						}
						//pr($productReceipt);die;
						$cust_data_query = $this->Customers->get($customer_id);
						$cust_data = $cust_data_query->toArray();
						$sale_table_query = $kiosk_saleTable->find('all',[
													  'conditions' => ['product_receipt_id' => $receipt_id]
													  ]);
						//pr($sale_table_query);die;
						$sale_table_query = $sale_table_query->hydrate(false);
						if(!empty($sale_table_query)){
							$sale_table = $sale_table_query->toArray();
						}else{
							$sale_table = array();
						}
						//if(!empty($productReceipt)){
							$processed_by = $productReceipt['processed_by'];
						//}
						$userName_query = $this->Users->find('all',array(
																	'conditions' => array('Users.id' => $processed_by),
																	'fields' => array('username'),
																	'recursive' => -1)
													  );
						$userName = $userName_query->hydrate(false);
						if(!empty($userName)){
							$userName = $userName->first();
						}
						$user_name = $userName['username'];
						//pr($productReceipt);die;
						foreach($sale_table as $key => $productDetail){
							$productIdArr[] = $productDetail['product_id'];
						}
						foreach($productIdArr as $product_id){
							$product_query = $this->Products->find('all', array(
																					'conditions' => array('Products.id' => $product_id),
																					'fields' => array('id','product','product_code'),
																					'recursive' => -1
																					)
																	);
							$product = $product_query->hydrate(false);
							if(!empty($product)){
								$product = $product->first();
							}
							$product_detail[]  = $product;
						}
						foreach($product_detail as $productInfo){
							$productName[$productInfo['id']] = $productInfo['product'];
							$productCode[$productInfo['id']] = $productInfo['product_code'];
						}
						$customerData_query = $this->Customers->find('all',array(
														'conditions' => array('Customers.id'=>$customer_id)
														)
											);
						
						$customerData = $customerData_query->hydrate(false);
						if(!empty($customerData)){
							$customerData = $customerData->first();
						}
						$paymentDetails_query = $paymentTable->find('all',array('conditions' => array('product_receipt_id' => $receipt_id),'recursive' => -1));
						$paymentDetails_query->hydrate(false);
						$paymentDetails = $paymentDetails_query->toArray();
						$payment_method = array();
						$settingArr = $this->setting;
						$CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
						foreach($paymentDetails as $key=>$paymentDetail){
							//pr($paymentDetail);
							$payment_method[] = $paymentDetail['payment_method']." ".$CURRENCY_TYPE.$paymentDetail['amount'];
						}
						
						$fullAddress = $countryOptions = $kioskContact = $kioskTable = "";
						$countryOptions = Configure::read('uk_non_uk');
						if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS &&
							$this->request->session()->read('Auth.User.user_type')=='wholesale'){
							$kiosk_id = $this->request->Session()->read('kiosk_id');
							$kioskDetails_query = $this->Kiosks->find('all',array('conditions'=>array('Kiosks.id'=>$kiosk_id),'recursive'=>-1,'fields'=>array('id','name','address_1','address_2','city','state','zip','contact','country')));
							$kioskDetails_result = $kioskDetails_query->hydrate(false);
							if(!empty($kioskDetails_result)){
								$kioskDetails = $kioskDetails_result->first();
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
						
						
						$receiptRequired = $_SESSION['quick_cart']['receipt_required'];
						if($receiptRequired == 1 && $special_invoice != 1){
							$send_by_email = Configure::read('send_by_email');
							$emailSender = Configure::read('EMAIL_SENDER');
							$Email = new Email();
							$Email->config('default');
							$Email->viewVars(array('productReceipt' => $productReceipt,'payment_method' => $payment_method,'vat' => $vat,'settingArr' =>$settingArr,'user_name'=>$user_name,'productName'=>$productName,'productCode'=>$productCode,'kioskTable'=>$kioskTable,'kioskContact'=>$kioskContact,'countryOptions'=>$countryOptions,'sale_table' => $sale_table,'cust_data'=>$cust_data,'new_kiosk_data' => $new_kiosk_data));
							//$Email->config(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
							//$Email = new CakeEmail(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
							$emailTo = $customerData['email']; 
							$Email->template('receipt_new_sale');
							$Email->emailFormat('both');
							$Email->to($emailTo);
							$Email->transport(TRANSPORT);
							$Email->from([$send_by_email => $emailSender]);
							//$Email->sender("sales@oceanstead.co.uk");  //$this->fromemail
							$Email->subject('Order Receipt');
							$Email->send();
						}
						unset($_SESSION['quick_cart']);
						unset($_SESSION['customer']);
						$kiosk_id = $this->request->Session()->read('kiosk_id');
						if($kiosk_id == ""){
								$kiosk_id = 10000;	
						}
						$this->SessionRestore->delete_from_session_backup_table('Home', 'reorder_cart', 'any_key', $kiosk_id);
						//$this->Session->setFlash("invoice created");
						$this->Flash->success(__('invoice created'));
						if($special_invoice == 1){
							return $this->redirect(array('controller'=>'product-receipts','action'=>'dr-generate-receipt',$receipt_id));
						}else{
							return $this->redirect(array('controller'=>'product-receipts','action'=>'generate-receipt',$receipt_id));
						}
					}
				}
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
			$searchArray['AND']['quantity >'] = '0';
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
	
	
	public function adminDataWQty($search = ""){
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
	
    public function monthlyKioskSaleDetail(){
        if($this->request->is('post')){
            //echo'hi';die;
            //pr($this->request);die;
                if(count($this->request->data)){
                        $kioskId = $this->request->data['kiosk'];
                        $dateData = $this->request->data['month'];
                        if(empty($dateData)){
                                $dateData = date("Y-m-d");
                        }
                        $lastDay = date('t',strtotime($dateData));
                        $month = date('m',strtotime($dateData));
                        $year = date('Y',strtotime($dateData));
                        $firstDay = date("{$year}-{$month}-01");
                        
                        $repairArr = array();
                        $unlockArr = array();
                        $productArr = array();
                        $mobileArr = array();
                        $dateWiseSaleArr = array();
                        
                        for($day = 0; $day < $lastDay; $day++){
                                if($day == 0){
                                        $dte = $firstDay;
                                }else{
                                        $add_days = "+ ".$day."days";
                                        $dte = date('Y-m-d',strtotime($add_days,strtotime($firstDay)));
                                }
                                
                                $bulkMobileArr[$dte] = $this->todayMobileSale($kioskId, $dte);
                                $bulkMobileSale[$dte] = (is_numeric($bulkMobileArr[$dte]['todayBlkMobileSale'])) ? $bulkMobileArr[$dte]['todayBlkMobileSale'] : "0";
                                $bulkMobileCard[$dte] = (is_numeric($bulkMobileArr[$dte]['todaysBlkMcardPayment'])) ? $bulkMobileArr[$dte]['todaysBlkMcardPayment'] : "0";
                                $bulkRefundArr[$dte] = $this->today_refund($kioskId, $dte);
                                $bulkMobileRefund[$dte] = (is_numeric($bulkRefundArr[$dte]['todayBlkMobileRefund'])) ? $bulkRefundArr[$dte]['todayBlkMobileRefund'] : "0";
                                
                                $repairArr[$dte] = $this->get_repair_sale($kioskId, $dte);
                                $repairSale[$dte] = (is_numeric($repairArr[$dte]['repairData']['repair_sale'])) ? $repairArr[$dte]['repairData']['repair_sale'] : "0";
                                $repairCard[$dte] = $repairArr[$dte]['repairData']['card_payment'];
                                $repairRefund[$dte] = (is_numeric($repairArr[$dte]['repairData']['repair_refund'])) ? $repairArr[$dte]['repairData']['repair_refund'] : "0";
                                
                                $unlockArr[$dte] = $this->get_unlock_sale($kioskId, $dte);
                                $unlockSale[$dte] = (is_numeric($unlockArr[$dte]['unlockData']['unlock_sale'])) ? $unlockArr[$dte]['unlockData']['unlock_sale'] : "0";
                                $unlockCard[$dte] = $unlockArr[$dte]['unlockData']['card_payment'];
                                $unlockRefund[$dte] = (is_numeric($unlockArr[$dte]['unlockData']['unlock_refund'])) ? $unlockArr[$dte]['unlockData']['unlock_refund'] : "0";
                                
                                $productArr[$dte] = $this->get_product_sale($kioskId, $dte);
                                //pr($productArr);die;
                                //if($day == 10){
                                //	pr($productArr);die;		
                                //}
                                $productSale[$dte] = (is_numeric($productArr[$dte]['productData']['product_sale'])) ? $productArr[$dte]['productData']['product_sale'] : "0";
                                $productCard[$dte] = $productArr[$dte]['productData']['card_payment'];
                                $productRefund[$dte] = (is_numeric($productArr[$dte]['productData']['product_refund'])) ? $productArr[$dte]['productData']['product_refund'] : "0";
                                
                                $mobileArr[$dte] = $this->get_mobile_data($kioskId, $dte);
                                $mobileSale[$dte] = (is_numeric($mobileArr[$dte]['mobileData']['mobile_sale'])) ? $mobileArr[$dte]['mobileData']['mobile_sale'] : "0";
                                $mobileCard[$dte] = $mobileArr[$dte]['mobileData']['card_payment'];
                                $mobileRefund[$dte] = (is_numeric($mobileArr[$dte]['mobileData']['mobile_refund'])) ? $mobileArr[$dte]['mobileData']['mobile_refund'] : "0";
                                $mobilePurchase[$dte] = $mobileArr[$dte]['mobileData']['mobile_purchase'];
                                                                        
                                $totalSaleArr[$dte] = $repairSale[$dte] + $unlockSale[$dte] + $productSale[$dte] + $mobileSale[$dte] + $bulkMobileSale[$dte];
                                $totalCardPaymentArr[$dte] = $repairCard[$dte] + $unlockCard[$dte] + $productCard[$dte] + $mobileCard[$dte] + $bulkMobileCard[$dte];
                                if($repairRefund[$dte] <0){
                                    $repairRefund[$dte] = -1 * $repairRefund[$dte];
                                }
                                
                                if($unlockRefund[$dte] < 0){
                                    $unlockRefund[$dte] = -1 * $unlockRefund[$dte];
                                }
                                if($productRefund[$dte] < 0){
                                    $productRefund[$dte] = -1 * $productRefund[$dte];
                                }
                                if($mobileRefund[$dte] < 0 ){
                                    $mobileRefund[$dte] = -1 * $mobileRefund[$dte];
                                }
                                if($bulkMobileRefund[$dte] < 0){
                                    $bulkMobileRefund[$dte] = -1 * $bulkMobileRefund[$dte];
                                }
                                
                                $totalRefundArr[$dte] = $repairRefund[$dte] + $unlockRefund[$dte] + $productRefund[$dte] + $mobileRefund[$dte] + $bulkMobileRefund[$dte];
                                $totalMobPurchaseArr[$dte] = $mobilePurchase[$dte];
                                if($totalRefundArr[$dte] < 0){
                                    $refund = -1 * $totalRefundArr[$dte];
                                }else{
                                    $refund = $totalRefundArr[$dte];
                                }
                                $netSale[$dte] = $totalSaleArr[$dte]-$refund;   //$totalRefundArr[$dte]  sourabh
                                $cashInHand[$dte] = $netSale[$dte]-$totalMobPurchaseArr[$dte]-$totalCardPaymentArr[$dte];
                                
                                $dateWiseSaleArr[$dte] = array(
                                                                'day' => date("D", strtotime($dte)),
                                                                'sale' => $totalSaleArr[$dte],
                                                                'card_payment' => $totalCardPaymentArr[$dte],
                                                                'refund' => $totalRefundArr[$dte],
                                                                'mobile_purchase' => $totalMobPurchaseArr[$dte],
                                                                'net_sale' => $netSale[$dte],
                                                                'cash_in_hand' => $cashInHand[$dte]
                                                                                     );
                        }
                }
        }
        
        $kiosks_query = $this->Kiosks->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'name',
                                                'conditions' => ['kiosk_type' => 1],
                                                'order' => ['Kiosks.name asc']
                                             ]);
		//pr($kiosks_query);die;
        $kiosks_query = $kiosks_query->hydrate(false);
		if(!empty($kiosks_query)){
            $kiosks = $kiosks_query->toArray();
        }else{
            $kiosks = array();
        }
		//pr($kiosks);die;
		
        $this->set(compact('kiosks','dateWiseSaleArr'));
    }
    
    private function todayMobileSale($kioskId = 0,$date = ""){
		if(empty($date)){
			$date = date('Y-m-d');
		}
		if($kioskId == 0){
			$kioskId = 10000;
		}
		$mobileSale_query = $this->MobileBlkReSales->find('all', [
                                                                       	'conditions' => [
                                                                                              "Date(MobileBlkReSales.created)" => $date,
                                                                                              'MobileBlkReSales.refund_status Is NULL',
                                                                                              'MobileBlkReSales.kiosk_id'=>$kioskId
                                                                                        ]
									]);
        $mobileSale_query 
               ->select(['todayMobileSale'=>'CASE WHEN MobileBlkReSales.discounted_price is NULL THEN MobileBlkReSales.selling_price ELSE MobileBlkReSales.discounted_price END'])
               ->select('MobileBlkReSales.discounted_price')
               ->select( 'MobileBlkReSales.selling_price');
              // pr($mobileSale_query);die;
        $mobileSale_query = $mobileSale_query->hydrate(false);
        if(!empty($mobileSale_query)){
            $mobileSale = $mobileSale_query->toArray();
        }else{
            $mobileSale = array();
        }
        //pr($mobileSale);die;
				$todayBlkMobileSale = 0;
				if(!empty($mobileSale)){
						foreach($mobileSale as $key=>$data){
								$todayBlkMobileSale+= $data['todayMobileSale'];
						}  
				}
				if($kioskId == 0){
					$kioskId = 10000;
				}
                
                $todaysMcardPaymentDetail_query = $this->MobileBlkReSalePayments->find('all',[
                                                                                              'conditions' => [
                                                                                                               'Date(MobileBlkReSalePayments.created)' => $date,
                                                                                                               'MobileBlkReSalePayments.kiosk_id'=>$kioskId,
                                                                                                               'MobileBlkReSalePayments.payment_method' => 'Card'
                                                                                                               ]
                                                                                              ]);
                $todaysMcardPaymentDetail_query
                          ->select(['today_sale' => $todaysMcardPaymentDetail_query->func()->sum('MobileBlkReSalePayments.amount')]);
                $todaysMcardPaymentDetail_result = $todaysMcardPaymentDetail_query->hydrate(false);
                if(!empty($todaysMcardPaymentDetail_result)){
                    $todaysMcardPaymentDetail = $todaysMcardPaymentDetail_result->first();
                }else{
                    $todaysMcardPaymentDetail = array();
                }
				
				$todaysBlkMcardPayment = $todaysMcardPaymentDetail['today_sale'];
				if(empty($todaysBlkMcardPayment)){
						$todaysBlkMcardPayment = 0;
				}
				
				if($kioskId == 0){
					$kiosk_Id = 10000;
				}else{
					$kiosk_Id = $kioskId;
				}
				$todaysMcashPaymentDetail_query = $this->MobileBlkReSalePayments->find('all',[
                                                                                              'conditions' => [
                                                                                                               'Date(MobileBlkReSalePayments.created)' => $date,
                                                                                                               'MobileBlkReSalePayments.kiosk_id'=>$kiosk_Id,
                                                                                                               'MobileBlkReSalePayments.payment_method' => 'Cash',
                                                                                                               'MobileBlkReSalePayments.amount >=' => 0
                                                                                                               ]]);
                $todaysMcashPaymentDetail_query
                          ->select(['today_sale' => $todaysMcashPaymentDetail_query->func()->sum('MobileBlkReSalePayments.amount')]);
                $todaysMcashPaymentDetail_result = $todaysMcashPaymentDetail_query->hydrate(false);
                if(!empty($todaysMcashPaymentDetail_result)){
                    $todaysMcashPaymentDetail = $todaysMcashPaymentDetail_result->first();
                }else{
                    $todaysMcashPaymentDetail = array();
                }
				
				
				$todaysBlkMcashPayment = $todaysMcashPaymentDetail['today_sale'];
				if(empty($todaysBlkMcashPayment)){
						$todaysBlkMcashPayment = 0;
				}
				
			$this->set(compact('todayBlkMobileSale','todaysBlkMcardPayment','todaysBlkMcashPayment'));
			return array(
				'todayBlkMobileSale' => $todayBlkMobileSale,
				'todaysBlkMcardPayment' => $todaysBlkMcardPayment,
				'todaysBlkMcashPayment' => $todaysBlkMcashPayment,
			);
	}
    
    private function today_refund($kioskId = 0,$date = ""){
		if(empty($date)){
			$date = date('Y-m-d');
		}
        if($kioskId == 0){
			$kioskId = 10000;
		}
        $mobile_refund_query = $this->MobileBlkReSales->find('all',[
                                                                    'conditions' => [
                                                                                     'Date(MobileBlkReSales.created)' => $date,
                                                                                     'MobileBlkReSales.refund_status'=>1,
                                                                                     'MobileBlkReSales.kiosk_id'=>$kioskId
                                                                                     ]
                                                                    ]);
                $mobile_refund_query
						  ->select('MobileBlkReSales.refund_price','MobileBlkReSales.refund_gain')
                          ->select(['todayTotalRefund' => $mobile_refund_query->func()->sum('MobileBlkReSales.refund_price')])
                          ->select(['todayTotalRefundGain' => $mobile_refund_query->func()->sum('MobileBlkReSales.refund_gain')]);
						  
						  
						  
        $mobile_refund_result = $mobile_refund_query->hydrate(false);
        if(!empty($mobile_refund_result)){
            $mobileRefund = $mobile_refund_result->first();
        }else{
            $mobileRefund = array();
        }
        
       
        if(!empty($mobileRefund['todayTotalRefund'])){
               $todayBlkMobileRefund = $mobileRefund['todayTotalRefund'];
        }else{
            $todayBlkMobileRefund = '';
        }
        $this->set(compact('todayBlkMobileRefund'));
        return array(
                     'todayBlkMobileRefund' => $todayBlkMobileRefund,
                     );
	}
    
    private function get_repair_sale($kskId = '', $dte = ''){
		if($kskId){
			$kioskId = $kskId;
		}else{
			$kiosk_id = $this->request->Session()->read('kiosk_id');
			if(!empty($this->request->params['pass']) && array_key_exists(0,$this->request->params['pass'])){//for admin kiosk_total_sale
				$kioskId = $this->request->params['pass'][0];
			}elseif((int)$kiosk_id){
				$kioskId = $kiosk_id;
			}else{
				$kioskId = 0;
			}
		}
				
		if($dte){
			//for monthly_kiosk_sale page
			$date = $dte;
		}else{
			$date = date('Y-m-d');
		}
		//get today mobile repair sale
		
        $todaysRepairSale_query = $this->MobileRepairSales->find('all',[
                                                                        'conditions' => [
                                                                                         'Date(MobileRepairSales.created)' => $date,
                                                                                         'MobileRepairSales.kiosk_id'=>$kioskId
                                                                                         ]
                                                                        ]
                                                                 );
            $todaysRepairSale_query
                      ->select(['today_sale' => $todaysRepairSale_query->func()->sum('MobileRepairSales.amount')]);
                      //pr($todaysRepairSale_query);die;
        $todaysRepairSale_result = $todaysRepairSale_query->hydrate(false);
        if(!empty($todaysRepairSale_result)){
            $todaysRepairSale = $todaysRepairSale_result->first();
        }else{
            $todaysRepairSale = array();
        }
       // pr($todaysRepairSale);
		$todaysSale = $todaysRepairSale['today_sale'];
        
        $todaysRcashPaymentDetail_query = $this->RepairPayments->find('all',[
                                                                             'conditions' => [
                                                                                              'Date(RepairPayments.created)' => $date,
                                                                                              'RepairPayments.kiosk_id'=>$kioskId,
                                                                                              'RepairPayments.payment_method' => 'Cash',
                                                                                              'RepairPayments.amount >=' => 0
                                                                                              ]
                                                                             ]);
            $todaysRcashPaymentDetail_query
                      ->select(['today_sale' => $todaysRcashPaymentDetail_query->func()->sum('RepairPayments.amount')]);
        $todaysRcashPaymentDetail_result = $todaysRcashPaymentDetail_query->hydrate(false);
        if(!empty($todaysRcashPaymentDetail_result)){
            $todaysRcashPaymentDetail = $todaysRcashPaymentDetail_result->first();
        }else{
            $todaysRcashPaymentDetail = array();
        }
		$todaysRcashPayment = $todaysRcashPaymentDetail['today_sale'];
		if(empty($todaysRcashPayment)){
			$todaysRcashPayment = 0;
		}
        $todaysRcardPaymentDetail_query = $this->RepairPayments->find('all',[
                                                                             'conditions' => [
                                                                                              'Date(RepairPayments.created)' => $date,
                                                                                              'RepairPayments.kiosk_id'=>$kioskId,
                                                                                              'RepairPayments.payment_method' => 'Card',
                                                                                              'RepairPayments.amount >=' => 0
                                                                                              ]
                                                                             ]);
            $todaysRcardPaymentDetail_query
                      ->select(['today_sale' => $todaysRcardPaymentDetail_query->func()->sum('RepairPayments.amount')]);
        $todaysRcardPaymentDetail_result = $todaysRcardPaymentDetail_query->hydrate(false);
        if(!empty($todaysRcardPaymentDetail_result)){
            $todaysRcardPaymentDetail = $todaysRcardPaymentDetail_result->first();
        }else{
            $todaysRcardPaymentDetail = array();
        }
        
		$todaysRcardPayment = $todaysRcardPaymentDetail['today_sale'];
		if(empty($todaysRcardPayment)){
			$todaysRcardPayment = 0;
		}
		
        $yesterdaysRepairSale_query = $this->MobileRepairSales->find('all',[
                                                                            'conditions' => [
                                                                                             'Date(MobileRepairSales.created)' => date('Y-m-d', strtotime(' -1 day')),
                                                                                             'MobileRepairSales.kiosk_id' => $kioskId
                                                                                             ]
                                                                            ]);
            $yesterdaysRepairSale_query
                      ->select(['yesterday_sale' => $yesterdaysRepairSale_query->func()->sum('MobileRepairSales.amount')]);
        $yesterdaysRepairSale_result = $yesterdaysRepairSale_query->hydrate(false);
        if(!empty($yesterdaysRepairSale_result)){
            $yesterdaysRepairSale = $yesterdaysRepairSale_result->first();
        }else{
            $yesterdaysRepairSale = array();
        }
       
		$yesterdaySale = $yesterdaysRepairSale['yesterday_sale'];
		
        $yesterdaysRcardPaymentDetail_query = $this->RepairPayments->find('all',[
                                                                                 'conditions' => [
                                                                                                  'Date(RepairPayments.created)' => date('Y-m-d',strtotime(' -1 day')),
                                                                                                  'RepairPayments.kiosk_id' => $kioskId,
                                                                                                  'RepairPayments.payment_method' => 'Card'
                                                                                                  ]
                                                                                 ]);
            $yesterdaysRcardPaymentDetail_query
                      ->select(['yesterday_sale' => $yesterdaysRcardPaymentDetail_query->func()->sum('RepairPayments.amount')]);
        $yesterdaysRcardPaymentDetail_result = $yesterdaysRcardPaymentDetail_query->hydrate(false);
        if(!empty($yesterdaysRcardPaymentDetail_result)){
            $yesterdaysRcardPaymentDetail = $yesterdaysRcardPaymentDetail_result->first();
        }else{
            $yesterdaysRcardPaymentDetail = array();
        }
        
		$yesterdaysRcardPayment = $yesterdaysRcardPaymentDetail['yesterday_sale'];
				
		//------------sourabh-------------------------
        $yesterdaysRcashPaymentDetail_query = $this->RepairPayments->find('all',[
                                                                                 'conditions' => [
                                                                                                  'Date(RepairPayments.created)' => date('Y-m-d',strtotime(' -1 day')),
                                                                                                  'RepairPayments.kiosk_id' => $kioskId,
                                                                                                  'RepairPayments.payment_method' => 'Cash',
                                                                                                  'RepairPayments.amount >=' => 0
                                                                                                  ]
                                                                                 ]);
            $yesterdaysRcashPaymentDetail_query
                      ->select(['yesterday_sale' => $yesterdaysRcashPaymentDetail_query->func()->sum('RepairPayments.amount')]);
        $yesterdaysRcashPaymentDetail_result = $yesterdaysRcashPaymentDetail_query->hydrate(false);
        if(!empty($yesterdaysRcashPaymentDetail_result)){
            $yesterdaysRcashPaymentDetail = $yesterdaysRcashPaymentDetail_result->first();
        }else{
            $yesterdaysRcashPaymentDetail = array();
        }
        
		$yesterdaysRcashPayment = $yesterdaysRcashPaymentDetail['yesterday_sale'];
				
		if(empty($yesterdaysRcardPayment)){
				$yesterdaysRcardPayment = 0;
		}
				
        $todaysRepairRefund_query = $this->MobileRepairSales->find('all',[
                                                                          'conditions' => [
                                                                                           'Date(MobileRepairSales.refund_on)' => $date,
                                                                                           'MobileRepairSales.kiosk_id' => $kioskId,
                                                                                           'MobileRepairSales.refund_status' => 1
                                                                                           ]
                                                                          ]);
            $todaysRepairRefund_query
                      ->select(['todays_refund' => $todaysRepairRefund_query->func()->sum('MobileRepairSales.refund_amount')]);
					  //pr($todaysRepairRefund_query);die;
        $todaysRepairRefund_result = $todaysRepairRefund_query->hydrate(false);
        if(!empty($todaysRepairRefund_result)){
            $todaysRepairRefund = $todaysRepairRefund_result->first();
        }else{
            $todaysRepairRefund = array();
        }
        //pr($todaysRepairRefund);die;
		$todaysRefund = $todaysRepairRefund['todays_refund'];
		//pr($todaysRefund);die;
		if($kskId && $dte){
			$monthlyKioskSaleArr = array('repairData' => array(
																	'date' => $dte,
																	'repair_sale' => $todaysSale,
																	'card_payment' => $todaysRcardPayment,
																	'repair_refund' => $todaysRefund
																));
			return $monthlyKioskSaleArr;
		}
		
        $yesterdaysRepairRefund_query = $this->MobileRepairSales->find('all',[
                                                                              'conditions' => [
                                                                                               'Date(MobileRepairSales.refund_on)' => date('Y-m-d',strtotime(' -1 day')),'MobileRepairSales.kiosk_id' => $kioskId,
'MobileRepairSales.refund_status' => 1]]);
            $yesterdaysRepairRefund_query
                      ->select(['yesterday_refund' => $yesterdaysRepairRefund_query->func()->sum('MobileRepairSales.refund_amount')]);
        $yesterdaysRepairRefund_result = $yesterdaysRepairRefund_query->hydrate(false);
        if(!empty($yesterdaysRepairRefund_result)){
            $yesterdaysRepairRefund = $yesterdaysRepairRefund_result->first();
        }else{
            $yesterdaysRepairRefund = array();
        }
		$yesterdaysRefund = $yesterdaysRepairRefund['yesterday_refund'];
		$this->set(compact('todaysSale','yesterdaySale','todaysRefund', 'yesterdaysRefund','todaysRcardPayment','yesterdaysRcardPayment','todaysRcashPayment','yesterdaysRcashPayment'));	
    }
    
    private function get_unlock_sale($kskId = '', $dte = ''){
		if($kskId){
			//for monthly_kiosk_sale page
			$kioskId = $kskId;
		}else{
			$kiosk_id = $this->request->Session()->read('kiosk_id');
			if(!empty($this->request->params['pass']) && array_key_exists(0,$this->request->params['pass'])){//for admin kiosk_total_sale
				$kioskId = $this->request->params['pass'][0];
			}elseif((int)$kiosk_id){
				$kioskId = $kiosk_id;
			}else{
				$kioskId = 0;
			}
		}
		
		if($dte){
			$date = $dte;
		}else{
			$date = date('Y-m-d');
		}
			
		$todaysUnlockSale_query = $this->MobileUnlockSales->find('all',[
                                                                        'conditions' => [
                                                                                         'Date(MobileUnlockSales.created)' => $date,
                                                                                         'MobileUnlockSales.kiosk_id' => $kioskId
                                                                                         ]
                                                                        ]);
        $todaysUnlockSale_query
                  ->select(['today_sale' => $todaysUnlockSale_query->func()->sum('MobileUnlockSales.amount')]);
                 // pr($todaysUnlockSale_query);die;
        $todaysUnlockSale_result = $todaysUnlockSale_query->hydrate(false);
        if(!empty($todaysUnlockSale_result)){
            $todaysUnlockSale = $todaysUnlockSale_result->first();
        }else{
            $todaysUnlockSale = array();
        }
		$todaysUsale = $todaysUnlockSale['today_sale'];
        
        $todaysUcardPaymentDetail_query = $this->UnlockPayments->find('all',['conditions' => [
                                                                                              'Date(UnlockPayments.created)' => $date,
                                                                                              'UnlockPayments.kiosk_id' => $kioskId,
                                                                                              'UnlockPayments.payment_method' => 'Card'
                                                                                              ]
                                                                             ]);
        $todaysUcardPaymentDetail_query
                  ->select(['today_sale' => $todaysUcardPaymentDetail_query->func()->sum('UnlockPayments.amount')]);
        $todaysUcardPaymentDetail_result = $todaysUcardPaymentDetail_query->hydrate(false);
        if(!empty($todaysUcardPaymentDetail_result)){
            $todaysUcardPaymentDetail = $todaysUcardPaymentDetail_result->first();
        }else{
            $todaysUcardPaymentDetail = array();
        }
        
		$todaysUcardPayment = $todaysUcardPaymentDetail['today_sale'];
		if(empty($todaysUcardPayment)){
				$todaysUcardPayment = 0;
		}
	
        $todaysUcashPaymentDetail_query = $this->UnlockPayments->find('all',[
                                                                             'conditions' => [
                                                                                              'Date(UnlockPayments.created)' => $date,
                                                                                              'UnlockPayments.kiosk_id' => $kioskId,
                                                                                              'UnlockPayments.payment_method' => 'Cash',
                                                                                              'UnlockPayments.amount >=' => 0
                                                                                              ]
                                                                             ]);
        $todaysUcashPaymentDetail_query
                  ->select(['today_sale' => $todaysUcashPaymentDetail_query->func()->sum('UnlockPayments.amount')]);
        $todaysUcashPaymentDetail_result = $todaysUcashPaymentDetail_query->hydrate(false);
        if(!empty($todaysUcashPaymentDetail_result)){
            $todaysUcashPaymentDetail = $todaysUcashPaymentDetail_result->first();
        }else{
            $todaysUcashPaymentDetail = array();
        }
        
		$todaysUcashPayment = $todaysUcashPaymentDetail['today_sale'];
		if(empty($todaysUcashPayment)){
				$todaysUcashPayment = 0;
		}
	
        $yesterdaysUnlockSale_query = $this->MobileUnlockSales->find('all',[
                                                                            'conditions' => [
                                                                                             'Date(MobileUnlockSales.created)' => date('Y-m-d', strtotime(' -1 day')),
                                                                                             'MobileUnlockSales.kiosk_id' => $kioskId
                                                                                             ]
                                                                            ]);
        $yesterdaysUnlockSale_query
                  ->select(['yesterday_sale' => $yesterdaysUnlockSale_query->func()->sum('MobileUnlockSales.amount')]);
        $yesterdaysUnlockSale_result = $yesterdaysUnlockSale_query->hydrate(false);
        if(!empty($yesterdaysUnlockSale_result)){
            $yesterdaysUnlockSale = $yesterdaysUnlockSale_result->first();
        }else{
            $yesterdaysUnlockSale = array();
        }
        
		$yesterdayUsale = $yesterdaysUnlockSale['yesterday_sale'];
	
        $yesterdaysUcardPaymentDetail_query = $this->UnlockPayments->find('all',[
                                                                                 'conditions' => [
                                                                                                  'Date(UnlockPayments.created)' => date('Y-m-d', strtotime(' -1 day')),
                                                                                                  'UnlockPayments.kiosk_id' => $kioskId,
                                                                                                  'UnlockPayments.payment_method' => 'Card'
                                                                                                  ]
                                                                                 ]);
        $yesterdaysUcardPaymentDetail_query
                  ->select(['yesterday_sale' => $yesterdaysUcardPaymentDetail_query->func()->sum('UnlockPayments.amount')]);
        $yesterdaysUcardPaymentDetail_result = $yesterdaysUcardPaymentDetail_query->hydrate(false);
        if(!empty($yesterdaysUcardPaymentDetail_result)){
            $yesterdaysUcardPaymentDetail = $yesterdaysUcardPaymentDetail_result->first();
        }else{
            $yesterdaysUcardPaymentDetail = array();
        }
        
		$yesterdaysUcardPayment = $yesterdaysUcardPaymentDetail['yesterday_sale'];
		if(empty($yesterdaysUcardPayment)){
			$yesterdaysUcardPayment = 0;
		}
		
        $yesterdaysUcashPaymentDetail_query = $this->UnlockPayments->find('all',[
                                                                                 'conditions' => [
                                                                                                  'Date(UnlockPayments.created)' => date('Y-m-d', strtotime(' -1 day')),
                                                                                                  'UnlockPayments.kiosk_id' => $kioskId,
                                                                                                  'UnlockPayments.payment_method' => 'Cash',
                                                                                                  'UnlockPayments.amount >=' => 0
                                                                                                  ]
                                                                                 ]);
        $yesterdaysUcashPaymentDetail_query
                  ->select(['yesterday_sale' => $yesterdaysUcashPaymentDetail_query->func()->sum('UnlockPayments.amount')]);
        $yesterdaysUcashPaymentDetail_result = $yesterdaysUcashPaymentDetail_query->hydrate(false);
        if(!empty($yesterdaysUcashPaymentDetail_result)){
            $yesterdaysUcashPaymentDetail = $yesterdaysUcashPaymentDetail_result->first();
        }else{
            $yesterdaysUcashPaymentDetail = array();
        }
        
		$yesterdaysUcashPayment = $yesterdaysUcashPaymentDetail['yesterday_sale'];
		if(empty($yesterdaysUcashPayment)){
				$yesterdaysUcashPayment = 0;
		}
		
        $todaysUnlockRefund_query = $this->MobileUnlockSales->find('all',[
                                                                          'conditions' => [
                                                                                           'Date(MobileUnlockSales.refund_on)' => $date,
                                                                                           'MobileUnlockSales.kiosk_id'=>$kioskId,
                                                                                           'MobileUnlockSales.refund_status' => 1
                                                                                           ]
                                                                          ]);
        $todaysUnlockRefund_query
                  ->select(['todays_refund' => $todaysUnlockRefund_query->func()->sum('MobileUnlockSales.refund_amount')]);
				  //pr($todaysUnlockRefund_query);die;
        $todaysUnlockRefund_result = $todaysUnlockRefund_query->hydrate(false);
        if(!empty($todaysUnlockRefund_result)){
            $todaysUnlockRefund = $todaysUnlockRefund_result->first();
        }else{
            $todaysUnlockRefund = array();
        }
		
		$todaysUrefund = 0;
		if(!empty($todaysUnlockRefund['todays_refund'])){
			$todaysUrefund = -$todaysUnlockRefund['todays_refund'];
		}
		
		if($kskId && $dte){
				$monthlyKioskSaleArr = array('unlockData' => array(
																		'date' => $dte,
																		'unlock_sale' => $todaysUsale,
																		'card_payment' => $todaysUcardPayment,
																		'unlock_refund' => $todaysUrefund
											));
			return $monthlyKioskSaleArr;
		}
		
        $yesterdaysUnlockRefund_query = $this->MobileUnlockSales->find('all',[
                                                                              'conditions' => ['Date(MobileUnlockSales.refund_on)' => date('Y-m-d', strtotime(' -1 day')),
                                                                                               'MobileUnlockSales.kiosk_id'=>$kioskId,
                                                                                               'MobileUnlockSales.refund_status' => 1
                                                                                               ]
                                                                              ]);
        $yesterdaysUnlockRefund_query
                  ->select(['yesterday_refund' => $yesterdaysUnlockRefund_query->func()->sum('MobileUnlockSales.refund_amount')]);
        $yesterdaysUnlockRefund_result = $yesterdaysUnlockRefund_query->hydrate(false);
        if(!empty($yesterdaysUnlockRefund_result)){
            $yesterdaysUnlockRefund = $yesterdaysUnlockRefund_result->first();
        }else{
            $yesterdaysUnlockRefund = array();
        }
		
		$yesterdaysUrefund = 0;
		if(!empty($yesterdaysUnlockRefund['yesterday_refund'])){
			$yesterdaysUrefund = -$yesterdaysUnlockRefund['yesterday_refund'];
		}
		
		$this->set(compact('todaysUsale','yesterdayUsale','todaysUrefund', 'yesterdaysUrefund','todaysUcardPayment','yesterdaysUcardPayment','todaysUcashPayment','yesterdaysUcashPayment'));	
	}
    
    private function get_product_sale($kskId = '', $dte = ''){
        //pr($this->request);die;
		if($kskId){
			$kioskId = $kskId;
              //echo $kioskId;die;
		}else{
			$kiosk_id = $this->request->Session()->read('kiosk_id');
			if(!empty($this->request->params['pass']) && array_key_exists(0,$this->request->params['pass'])){
				//for admin kiosk_total_sale
				$kioskId = $this->request->params['pass'][0];
			}elseif((int)$kiosk_id){
				$kioskId = $kiosk_id;
			}else{
				$kioskId = 0;
			}
		}
		 //echo "sds".$dte; 
		  $date = date('Y-m-d');		
		if($dte){$date = $dte;}
		//echo $date; echo'</br>';die;
		if((int)$kioskId == 0){
            //echo 'hi';die;
			$saleSource = "kiosk_product_sales";
			$productReceiptSource = "product_receipts";
			$paymentDetailSource = "payment_details";
			$invoiceOrderSource = "invoice_orders";
			$todSale = 0;
		}else{
            //echo 'bye';die;
			$saleSource = "kiosk_{$kioskId}_product_sales";
			$productReceiptSource = "kiosk_{$kioskId}_product_receipts";
			$paymentDetailSource = "kiosk_{$kioskId}_payment_details";
			$invoiceOrderSource = "kiosk_{$kioskId}_invoice_orders";
		}
		//pr($saleSource);die;
		//$receiptTable_source = "kiosk_{$kioskId}_product_receipts";
       
        //$this->ProductReceipt->setSource($productReceiptSource);
        $productReceiptTable = TableRegistry::get($productReceiptSource,[
                                                                'table' => $productReceiptSource,
                                                                ]);
		//$this->KioskProductSale->setSource($saleSource);
		 $saleSourceTable = TableRegistry::get($saleSource,[
                                                                'table' => $saleSource,
                                                                ]);
		$receiptIdArr_query = $saleSourceTable->find('list',[
																'keyField' => 'id',
                                                                'valueField' => 'product_receipt_id',
																'conditions' => ['Date(created)' => $date,
																						  'refund_status' => 0,
                                                                                ],
																//'recursive' => -1
                                                              ]);
        // pr($receiptIdArr_query); echo'</br>';
        if(!empty($receiptIdArr_query)){
            $receiptIdArr = $receiptIdArr_query->toArray();
        }else{
            $receiptIdArr = array();
        }
        //$receiptIdArrpr($receiptIdArr);die;
        $paymentDetailTable = TableRegistry::get($paymentDetailSource,[
                                                                        'table' => $paymentDetailSource,
                                                                    ]);
		$paymentDetailQuery = "";
		$todayProductPmtDetails = array(
										'credit' => 0,
										'cash' => 0,
										'bank_transfer' => 0,
										'card' => 0,
										'cheque' => 0,
										'misc' => 0,
										);
		$todayCash = 0;
		$yesCash = 0;
		//pr($receiptIdArr);
		if(count($receiptIdArr)){
            if(empty($receiptIdArr)){
                $receiptIdArr = array(0 => null);
            }
			$paymentDetails_query = $paymentDetailTable->find('all', array(
														'conditions' => array('product_receipt_id IN' => $receiptIdArr),
														//'recursive' => -1,
													));
            $paymentDetails_query = $paymentDetails_query->hydrate(false);
            if(!empty($paymentDetails_query)){
                $paymentDetails = $paymentDetails_query->toArray();
            }else{
                $paymentDetails = array();
            }
			$todayProductPmtDetails = $this->getPaymentDetailSale($paymentDetails,$productReceiptTable);
			
            $cashDetails_query = $this->ProductPayments->find('all',[
                                                                     'conditions' => [
                                                                                      '`ProductPayments`.`payment_method`' => 'Cash',
                                                                                      'Date(`ProductPayments`.`created`)' => $date,
                                                                                      '`ProductPayments`.`amount` >= ' => 0,
                                                                                      '`ProductPayments`.`kiosk_id`' => $kioskId
                                                                                      ]
                                                                     ]);
            $cashDetails_query
                      ->select(['today_sale' => $cashDetails_query->func()->sum('ProductPayments.amount')]);
            $cashDetails_result = $cashDetails_query->first();
            if(!empty($cashDetails_result)){
                $cashDetails = $cashDetails_result->toArray();
            }else{
                $cashDetails = array();
            }
            //pr($cashDetails);die;
			 $todayCash = $cashDetails['today_sale'];
			
		}
        if(empty($receiptIdArr)){
            $receiptIdArr = array(0 => null);
        }
        //pr($_SESSION);die;
		$bulkDiscountArr_query = $productReceiptTable->find('list',[
																	'keyField' => 'id',
                                                                    'valueField' => 'bulk_discount',
																	'conditions' => ['id IN' => $receiptIdArr],
																	//'recursive' => -1
                                                               ]);
        //pr($bulkDiscountArr_query);die;
        if(!empty($bulkDiscountArr_query)){
            $bulkDiscountArr = $bulkDiscountArr_query->toArray();
        }else{
            $bulkDiscountArr = array();
        }
        //pr($bulkDiscountArr);die;
        if(empty($receiptIdArr)){
            $receiptIdArr = array(0 => null);
        }
		$vatArr_query = $productReceiptTable->find('list',[
                                                        'keyField' => 'id',
                                                        'valueField' => 'vat',
                                                        'conditions' => ['id IN' => $receiptIdArr],
                                                        //'recursive' => -1
                                                      ]);
        if(!empty($vatArr_query)){
            $vatArr = $vatArr_query->toArray();
        }else{
            $vatArr = array();
        }
		$saleIds = array_keys($receiptIdArr);
		
		$recitIds = array_values($receiptIdArr);
		
		$productSaleArr = $productRefundArr = array();
		
		if(!empty($saleIds)){
			//sale array
			$productSaleArr_query = $saleSourceTable->find('all',array(
																			'fields' => array(
																								'product_id',
																								'sale_price',
																								'discount',
																								'product_receipt_id',
																								'quantity'
																								),
																			'conditions' => array('id IN' => $saleIds,
																								'refund_status' => 0),
																			//'recursive' => -1
																		)
															);
			$productSaleArr_query = $productSaleArr_query->hydrate(false);
            if(!empty($productSaleArr_query)){
                $productSaleArr = $productSaleArr_query->toArray();
            }else{
                $productSaleArr = array();
            }
			//refund array
            if(empty($recitIds)){
                $recitIds = array(0 => null);
            }
			$productRefundArr_query = $saleSourceTable->find('all',array(
																			'fields' => array(
																							  'product_id',
																							  'sale_price',
																							  'discount',
																							  'product_receipt_id',
																							  'quantity'
																							  ),
																			'conditions' => array('product_receipt_id IN' => $recitIds,
																								'refund_status IN' => array(1,2)),
																			//'recursive' => -1
																		)
															  );
            $productRefundArr_query = $productRefundArr_query->hydrate(false);
            if(!empty($productRefundArr_query)){
                $productRefundArr = $productRefundArr_query->toArray();
            }else{
                $productRefundArr = array();
            }
		}
				
		$newProductRefundArr = array();
		if(!empty($productRefundArr)){
			foreach($productRefundArr as $ki => $productRefundInfo){
				$productId = $productRefundInfo['product_id'];
				$receiptId = $productRefundInfo['product_receipt_id'];
				$refkey = "$productId|$receiptId";
				//$newProductRefundArr[$refkey] = $productRefundInfo['KioskProductSale']['quantity'];
				if(array_key_exists($refkey, $newProductRefundArr)){
					$newProductRefundArr[$refkey] += $productRefundInfo['quantity'];
				}else{
					$newProductRefundArr[$refkey] = $productRefundInfo['quantity'];
				}
			}
		}
		$finalQuantity = $totalSaleArr = $newProductSaleArr = array();
			
		if(!empty($productSaleArr)){
			foreach($productSaleArr as $k => $productSaleInfo){
				$productId = $productSaleInfo['product_id'];
				$receiptId = $productSaleInfo['product_receipt_id'];
				$key = "$productId|$receiptId";
				$newProductSaleArr[$key] = $productSaleInfo;
							
				if(array_key_exists($key,$newProductRefundArr)){
					$newProductRefundArr[$key] += $productSaleInfo['quantity'];
				}else{
					$newProductRefundArr[$key] = $productSaleInfo['quantity'];
				}
				//if($dte == "2016-09-07"){
				//	pr($newProductRefundArr);die;
				//}
                //pr($bulkDiscountArr);die;
				$salePrice = $productSaleInfo['sale_price'];
				$discount = $productSaleInfo['discount'];
				
				$prodRecptID = $productSaleInfo['product_receipt_id'];
				if(array_key_exists($prodRecptID,$bulkDiscountArr) && $bulkDiscountArr[$prodRecptID] > 0){
					$bulkDiscount = $bulkDiscountArr[$prodRecptID];
				}else{
					$bulkDiscount = 0;
				}
				$totalSaleArr[] = ($salePrice-($salePrice*$discount/100+$salePrice*$bulkDiscount/100)) * $newProductRefundArr[$key];
				
			}
		}
				
		$todayProductSale = 0;
		foreach($totalSaleArr as $key => $todaySale){
			$todayProductSale+=$todaySale;
		}
        if(empty($receiptIdArr)){
            $receiptIdArr = array(0 => null);
        }
        //pr($receiptIdArr);die;
        //echo $date; echo'</br>';
        $res_query = $productReceiptTable->find('all',['conditions' => ['id IN' => $receiptIdArr]]);
        $res_query
                  ->select(['total_sale' => $res_query->func()->sum('orig_bill_amount')]);
                  if($date == '2016-12-10'){
                 // pr($res_query);die;
                  }
				  //echo $date;die;
        $res_result = $res_query->hydrate(false);
        if(!empty($res_result)){
            $res = $res_result->first();
        }else{
            $res = array();
        }
        if($date == '2016-12-10'){
           //  pr($res);die('error');
        }
		
		$todayProductSale = $res['total_sale'];
        //pr($todayProductSale);die;
		$credit_to_other_changed  = array(
											'credit' => 0,
											'cash' => 0,
											'bank_transfer' => 0,
											'cheque' => 0,
											'card' => 0,
											'misc' => 0								  
										  );
		if(true){  //$kioskId == 0
			$result_query =  $paymentDetailTable->find('all',array('conditions' => array('date(created)' => $date),
												   ));
            $result_query = $result_query->hydrate(false);
            if(!empty($result_query)){
                $result = $result_query->toArray();
            }else{
                $result = array();
            }
            //product_receipt_id
            $recipt_arr = array();
			foreach($result as $paykey => $payvalue){
                $recipt_arr[$payvalue['product_receipt_id']] = $payvalue['product_receipt_id'];
            }
            if(empty($recipt_arr)){
                $recipt_arr = array(0 => null);
            }
            
            $recipt_res_query  = $productReceiptTable->find('list',array('conditions' => array('id IN' => $recipt_arr),
                                       'keyField' => 'id',
                                       'valueField' => 'created',
                                       ));
            
            $recipt_res_query = $recipt_res_query->hydrate(false);
            if(!empty($recipt_res_query)){
                $recipt_res = $recipt_res_query->toArray();
            }else{
                $recipt_res = array();
            }
			$onCreditAmt = $cashAmt = $cardAmt = $bnkTrnAmt = $chkAmt = $miscAmt = 0;
			foreach($result as $s => $val){
                $recit_id = $val['product_receipt_id'];
                if(array_key_exists($recit_id,$recipt_res)){
                    $recit_time_obj = $recipt_res[$recit_id];
                }
				$pay_time = strtotime(date("Y-m-d",strtotime($val['created'])));
				$recit_time = strtotime(date("Y-m-d",strtotime($recit_time_obj)));
				if($pay_time != $recit_time){
					$pmtAmt1 = $val['amount'];
					$pay_method = $val['payment_method'];
					switch($pay_method){
						case 'On Credit':
							$onCreditAmt += $pmtAmt1;
							break;
						case 'Cash':
							$cashAmt += $pmtAmt1;
							break;
						case 'Bank Transfer':
							$bnkTrnAmt += $pmtAmt1;
							break;
						case 'Cheque':
							$chkAmt += $pmtAmt1;
							break;
						case 'Card':
							$cardAmt += $pmtAmt1;
							break;
						case 'Misc':
							$miscAmt += $pmtAmt1;
							break;
					}
				}
			}
			$credit_to_other_changed = array('credit' => $onCreditAmt,'cash' => $cashAmt, 'bank_transfer' => $bnkTrnAmt, 'cheque' => $chkAmt, 'card' => $cardAmt, 'misc' => $miscAmt);
		}
		$y_credit_to_other_changed  = array(
											'credit' => 0,
											'cash' => 0,
											'bank_transfer' => 0,
											'cheque' => 0,
											'card' => 0,
											'misc' => 0								  
										  );
		
		$y_credit_to_other_changed = $this->prv_credit_to_card($productReceiptTable,$paymentDetailTable);
		$this->set(compact('y_credit_to_other_changed'));
		$yreceiptIdArr_query = $saleSourceTable->find('list',[
                                                                'keyField' => 'id',
                                                                'valueField' => 'product_receipt_id',
                            									'conditions' => ['Date(created)' => date('Y-m-d', strtotime(' -1 day')),
                                                                'refund_status' => 0,]
                                                              ]);
		if(!empty($yreceiptIdArr_query)){
            $yreceiptIdArr = $yreceiptIdArr_query->toArray();
        }else{
            $yreceiptIdArr = array();
        }
		if(count($yreceiptIdArr)){
            $YcashDetails_query = $this->ProductPayments->find('all',['conditions' => ['ProductPayments.payment_method' => 'Cash','Date(ProductPayments.created)' => date('Y-m-d',strtotime('-1 day')),'ProductPayments.amount >= ' => 0,
'ProductPayments.kiosk_id' => $kioskId]]);
            $YcashDetails_query
                      ->select(['today_sale' => $YcashDetails_query->func()->sum('ProductPayments.amount')]);
            $YcashDetails_result = $YcashDetails_query->hydrate(false);
            if(!empty($YcashDetails_result)){
                $YcashDetails = $YcashDetails_result->first();
            }else{
                $YcashDetails = array();
            }
             //pr($YcashDetails);
			 $yesCash = $YcashDetails['today_sale'];
			//pr($YcashDetails);die;
		}
	
		$paymentDetailTable = TableRegistry::get($paymentDetailSource,[
                                                                        'table' => $paymentDetailSource,
                                                                    ]);
		$paymentDetailQuery = "";
		$yesterdayProductPmtDetails = array(
										'credit' => 0,
										'cash' => 0,
										'bank_transfer' => 0,
										'cheque' => 0,
										'card' => 0,
										'misc' => 0,
										);
		if(count($yreceiptIdArr)){
            if(empty($yreceiptIdArr)){
                $yreceiptIdArr = array(0 => null);
            }
			$paymentDetails = $paymentDetailTable->find('all', array(
														'conditions' => array('product_receipt_id IN' => $yreceiptIdArr),
														//'recursive' => -1,
													));
			//pr($yreceiptIdArr);
			//pr($paymentDetails);die;
            $paymentDetails = $paymentDetails->hydrate(false);
            if(!empty($paymentDetails)){
                $paymentDetails = $paymentDetails->toArray();
            }else{
                $paymentDetails = array();
            }
			$yesterdayProductPmtDetails = $this->getPaymentDetailSale($paymentDetails,$productReceiptTable);
		}
		if(empty($yreceiptIdArr)){
            $yreceiptIdArr = array(0 => null);
        }
		$ybulkDiscountArr_query = $productReceiptTable->find('list',[
                                                                'keyField' => 'id',
                                                                'valueField' => 'bulk_discount',
                                                                'conditions' => ['id IN'=>$yreceiptIdArr],
                                                                //'recursive' => -1
                                                              ]);
        if(!empty($ybulkDiscountArr_query)){
            $ybulkDiscountArr = $ybulkDiscountArr_query->toArray();
        }else{
            $ybulkDiscountArr = array();
        }
        if(empty($yreceiptIdArr)){
            $yreceiptIdArr = array(0 => null);
        }
		$ybulkVatArr_query = $productReceiptTable->find('list',[
                                                            'keyField' => 'id',
                                                            'valueField' => 'vat',
                                                            'conditions' => ['id IN'=>$yreceiptIdArr],
                                                            //'recursive' => -1
                                                         ]);
        if(!empty($ybulkVatArr_query)){
            $ybulkVatArr = $ybulkVatArr_query->toArray();
        }else{
            $ybulkVatArr = array();
        }
		$ysaleIds = array_keys($yreceiptIdArr);
		$yrecitIds = array_values($yreceiptIdArr);
		$yproductSaleArr = array();
			
		if(!empty($ysaleIds)){
			$yproductSaleArr_query = $saleSourceTable->find('all',array(
									'fields' => array(
													  'product_id',
													  'sale_price',
													  'discount',
													  'product_receipt_id',
													  'quantity'
													  ),
									'conditions' => array(
														  'id IN' => $ysaleIds,
														  'refund_status' => 0
														),
									//'recursive' => -1
											 ));
			$yproductSaleArr_query = $yproductSaleArr_query->hydrate(false);
            if(!empty($yproductSaleArr_query)){
                $yproductSaleArr = $yproductSaleArr_query->toArray();
            }else{
                $yproductSaleArr = array();
            }
            if(empty($yrecitIds)){
                $yrecitIds = array(0 => null);
            }
			$yproductRefundArr_query = $saleSourceTable->find('all',array(
							'fields' => array(
											  'product_id',
											  'sale_price',
											  'discount',
											  'product_receipt_id',
											  'quantity'
											  ),
							'conditions' => array(
												  //"NOT" => array('KioskProductSale.id' => $ysaleIds),
												  //[FOR TESTING PURPOSE WE COMMENTED ABOVE LINE ON 8TH JULY 2016]
												  //we are excluding todays refund from gross sale which it should be
													'refund_status IN' => array(1,2),
													'product_receipt_id IN' => $yrecitIds,
												),
							//'recursive' => -1
									 ));
            $yproductRefundArr_query = $yproductRefundArr_query->hydrate(false);
            if(!empty($yproductRefundArr_query)){
                $yproductRefundArr = $yproductRefundArr_query->toArray();
            }else{
                $yproductRefundArr = array();
            }
		}
		//pr($yproductRefundArr);die;
		$newYproductRefundArr = array();
		if(!empty($yproductRefundArr)){
			foreach($yproductRefundArr as $yproductRefundInfo){
				$yRefProductId = $yproductRefundInfo['product_id'];
				$yRefReceiptId = $yproductRefundInfo['product_receipt_id'];
				$yRefKey = "$yRefProductId|$yRefReceiptId";
				//$newYproductRefundArr[$yRefKey] = $yproductRefundInfo['KioskProductSale']['quantity'];
				if(array_key_exists($yRefKey,$newYproductRefundArr)){
					$newYproductRefundArr[$yRefKey]+=$yproductRefundInfo['quantity'];
				}else{
					$newYproductRefundArr[$yRefKey] = $yproductRefundInfo['quantity'];
				}
			}
		}
				
		$ytotalSaleArr = array();
		$yQauntityArr = array();
		$yVatArr = array();
		if(!empty($yproductSaleArr)){
			//pr($yproductSaleArr);
			foreach($yproductSaleArr as $key=>$yproductSaleData){
					
				$yProductId = $yproductSaleData['product_id'];
				$yReceiptId = $yproductSaleData['product_receipt_id'];
				$ysalePrice = $yproductSaleData['sale_price'];
				$ydiscount = $yproductSaleData['discount'];
				$quantity = $yproductSaleData['quantity'];
				$ykey = "$yProductId|$yReceiptId";
				
				
				if(array_key_exists($ykey,$newYproductRefundArr)){
					$newYproductRefundArr[$ykey]+=$quantity;
				}else{
					$newYproductRefundArr[$ykey] = $quantity;
				}
				if(array_key_exists($yproductSaleData['product_receipt_id'],$ybulkDiscountArr)){
					if($ybulkDiscountArr[$yproductSaleData['product_receipt_id']]>0){
						$ybulkDiscount = $ybulkDiscountArr[$yproductSaleData['product_receipt_id']];
					}else{
						$ybulkDiscount = 0;
					}
				}else{
					$ybulkDiscount = 0;
				}
				
				$ytotalSaleArr[]=($ysalePrice-($ysalePrice*$ydiscount/100+$ysalePrice*$ybulkDiscount/100))*$newYproductRefundArr[$ykey];
			}
		}
		
		$yesterdayProductSale = 0;
		foreach($ytotalSaleArr as $key=>$ySale){
			$yesterdayProductSale+=$ySale;
		}
		if(empty($yreceiptIdArr)){
            $yreceiptIdArr = array(0 => null);
        }
        $Yres_query = $productReceiptTable->find('all',['conditions' => ['id IN' => $yreceiptIdArr]]);
        $Yres_query
                  ->select(['total_sale' => $Yres_query->func()->sum('orig_bill_amount')]);
        //pr($Yres_query);die;
        $Yres_result = $Yres_query->hydrate(false);
        //pr($Yres_result);die;
        if(!empty($Yres_result)){
            $Yres = $Yres_result->first();
        }else{
            $Yres = array();
        }
        //pr($Yres);die;
		$yesterdayProductSale = $Yres['total_sale'];
		
		$todaysPcardPaymentDetail_query = $this->ProductPayments->find('all',['conditions' => ['Date(ProductPayments.created)' => $date,'ProductPayments.kiosk_id'=>$kioskId,'ProductPayments.payment_method' => 'Card']]);
        $todaysPcardPaymentDetail_query
                  ->select(['today_sale' => $todaysPcardPaymentDetail_query->func()->sum('ProductPayments.amount')]);
                  //pr($todaysPcardPaymentDetail_query);die;
        $todaysPcardPaymentDetail_result = $todaysPcardPaymentDetail_query->hydrate(false);
        if(!empty($todaysPcardPaymentDetail_result)){
            $todaysPcardPaymentDetail = $todaysPcardPaymentDetail_result->first();
        }else{
            $todaysPcardPaymentDetail = array();
        }
        
		$todaysPcardPayment = $todaysPcardPaymentDetail['today_sale'];
		if(empty($todaysPcardPayment)){
				$todaysPcardPayment = 0;
		}
		
        $yesterdaysPcardPaymentDetail_query = $this->ProductPayments->find('all',['conditions' => ['Date(ProductPayments.created)' => date('Y-m-d', strtotime(' -1 day')),'ProductPayments.kiosk_id'=>$kioskId,'ProductPayments.payment_method' => 'Card']]);
        $yesterdaysPcardPaymentDetail_query
                  ->select(['yesterday_sale' => $yesterdaysPcardPaymentDetail_query->func()->sum('ProductPayments.amount')]);
        $yesterdaysPcardPaymentDetail_result = $yesterdaysPcardPaymentDetail_query->hydrate(false);
        if(!empty($yesterdaysPcardPaymentDetail_result)){
            $yesterdaysPcardPaymentDetail = $yesterdaysPcardPaymentDetail_result->first();
        }else{
            $yesterdaysPcardPaymentDetail = array();
        }
        
		$yesterdaysPcardPayment = $yesterdaysPcardPaymentDetail['yesterday_sale'];
		
		if(empty($yesterdaysPcardPayment)){
				$yesterdaysPcardPayment = 0;
		}
		//echo $date;die;
        $productRefund_query = $saleSourceTable->find('all',['conditions' => ['Date(created)' => $date]]);
        $productRefund_query
                  ->select(['todayProductRefund' => $productRefund_query->func()->sum('refund_price*quantity')]);
                 // echo $productRefund_query;
		//pr($productRefund_query);die;
	   $productRefund_result =   $productRefund_query->hydrate(false);
        if(!empty($productRefund_result)){
           $productRefund = $productRefund_result->first();
        }else{
            $productRefund = array();
        }
      //  pr($productRefund);
		$todayProductRefund = $productRefund['todayProductRefund'];
		
		//we are returning unlock sale, card payment and unlock refund in an array
		if($kskId && $dte){
			$monthlyKioskSaleArr = array('productData' => array(
																	'date' => $dte,
																	'product_sale' => $todayProductSale,
																	'card_payment' => $todaysPcardPayment,
																	'product_refund' => $todayProductRefund
																)
										);
			return $monthlyKioskSaleArr;
		}
        
        $yesterdayRefund_query = $saleSourceTable->find('all',['conditions' => ['Date(created)' => date('Y-m-d', strtotime(' -1 day'))]]);
        $yesterdayRefund_query
                  ->select(['yesterdayProductRefund' => $yesterdayRefund_query->func()->sum('refund_price*quantity')]);
        $yesterdayRefund_result = $yesterdayRefund_query->hydrate(false);
        if(!empty($yesterdayRefund_result)){
            $yesterdayRefund = $yesterdayRefund_result->first();
        }else{
            $yesterdayRefund = array();
        }
        
		$yestdayProductRefund = $yesterdayRefund['yesterdayProductRefund'];
		$this->set(compact('todayProductSale','yesterdayProductSale','todayProductRefund','yestdayProductRefund','todaysPcardPayment','yesterdaysPcardPayment','todayProductPmtDetails','yesterdayProductPmtDetails','todayCash','yesCash','credit_to_other_changed'));
    }
    
    private function getPaymentDetailSale($todayCreditNote = array(),$productReceiptTable){
		$onCreditAmt = $cashAmt = $cardAmt = $bnkTrnAmt = $chkAmt = $miscAmt = 0;
		foreach($todayCreditNote as $todayCN){
			$id = $todayCN['product_receipt_id'];
			$recipt_data_query = $productReceiptTable->find('all',array('conditions' => array('id' => $id)));
			$recipt_data_query = $recipt_data_query->hydrate(false);
			if(!empty($recipt_data_query)){
				$recipt_data = $recipt_data_query->first();
			}else{
				$recipt_data = array();
			}
			$recipt_created = date('d-m-y',strtotime($recipt_data['created']));
			$pay_created = date('d-m-y',strtotime($todayCN['created']));
			
			$creditRecptID = $todayCN['id'];
			$pmtMethod = trim($todayCN['payment_method']);
			$pmtAmt = $todayCN['amount'];
			switch($pmtMethod){
				case 'On Credit':
					//if(strtotime($recipt_created) == strtotime($pay_created)){
						$onCreditAmt += $pmtAmt;	
					//}
					break;
				case 'Cash':
					$cashAmt += $pmtAmt;
					break;
				case 'Bank Transfer':
					//if(strtotime($recipt_created) == strtotime($pay_created)){
						$bnkTrnAmt += $pmtAmt;
					//}
					break;
				case 'Cheque':
					//if(strtotime($recipt_created) == strtotime($pay_created)){
						$chkAmt += $pmtAmt;
					//}
					break;
				case 'Card':
					//if(strtotime($recipt_created) == strtotime($pay_created)){
						$cardAmt += $pmtAmt;
					//}
					break;
				case 'Misc':
					//if(strtotime($recipt_created) == strtotime($pay_created)){
						$miscAmt += $pmtAmt;
					//}
					break;
			}
			//$totalAmt = $todayCN['CreditReceipt']['credit_amount'];
			
		}
		return $todaysCN = array('credit' => $onCreditAmt,'cash' => $cashAmt, 'bank_transfer' => $bnkTrnAmt, 'cheque' => $chkAmt, 'card' => $cardAmt, 'misc' => $miscAmt);
	}
    
    private function get_mobile_data($kskId = '', $dte = ''){
				if($kskId){
						//for monthly_kiosk_sale page
						$kioskId = $kskId;
				}else{
						$kiosk_id = $this->request->Session()->read('kiosk_id');
						if(!empty($this->request->params['pass']) && array_key_exists(0,$this->request->params['pass'])){//for admin kiosk_total_sale
								$kioskId = $this->request->params['pass'][0];
						}elseif((int)$kiosk_id){
								$kioskId = $kiosk_id;
						}else{
								$kioskId = 0;
						}
				}
				
				if($dte){
						$date = $dte;
				}else{
						$date = date('Y-m-d');
				}
                
                
                $mobileSale_query = $this->MobileReSales->find('all', [
								'conditions' => array("Date(MobileReSales.created)" => $date,'MobileReSales.refund_status IS NULL','MobileReSales.kiosk_id'=>$kioskId)
                                           ]);
                $mobileSale_query 
                       ->select(['todayMobileSale'=>'CASE WHEN MobileReSales.discounted_price is NULL THEN MobileReSales.selling_price ELSE MobileReSales.discounted_price END'])
                       ->select('MobileReSales.discounted_price')
                       ->select( 'MobileReSales.selling_price');
                       //pr($mobileSale_query);die;
                $mobileSale_query = $mobileSale_query->hydrate(false);
                if(!empty($mobileSale_query)){
                    $mobileSale = $mobileSale_query->toArray();
                }else{
                    $mobileSale = array();
                }
				//pr($mobileSale);
				$todayMobileSale = 0;
				if(!empty($mobileSale)){
						foreach($mobileSale as $key=>$data){
								$todayMobileSale+= $data['todayMobileSale'];
						}  
				} 
				if($kioskId == 0){
					$k_id = 10000;
				}else{
					$k_id =$kioskId;
				}
				
                $todaysMcardPaymentDetail_query = $this->MobileReSalePayments->find('all',['conditions' => ['Date(MobileReSalePayments.created)' => $date,'MobileReSalePayments.kiosk_id'=>$k_id,'MobileReSalePayments.payment_method' => 'Card']]);
                $todaysMcardPaymentDetail_query
                          ->select(['today_sale' => $todaysMcardPaymentDetail_query->func()->sum('MobileReSalePayments.amount')]);
                $todaysMcardPaymentDetail_result = $todaysMcardPaymentDetail_query->first();
                if(!empty($todaysMcardPaymentDetail_result)){
                    $todaysMcardPaymentDetail = $todaysMcardPaymentDetail_result->toArray();
                }else{
                    $todaysMcardPaymentDetail = array();
                }
            
				$todaysMcardPayment = $todaysMcardPaymentDetail['today_sale'];
				if(empty($todaysMcardPayment)){
						$todaysMcardPayment = 0;
				}
				
				if($kioskId == 0){
					$kisk_id = 10000;
				}else{
					$kisk_id = $kioskId;
				}
				
                $todaysMcashPaymentDetail_query = $this->MobileReSalePayments->find('all',['conditions' => ['Date(MobileReSalePayments.created)' => $date,'MobileReSalePayments.kiosk_id'=>$kisk_id,'MobileReSalePayments.payment_method' => 'Cash','MobileReSalePayments.amount >=' => 0]]);
                $todaysMcashPaymentDetail_query
                          ->select(['today_sale' => $todaysMcashPaymentDetail_query->func()->sum('MobileReSalePayments.amount')]);
                $todaysMcashPaymentDetail_result = $todaysMcashPaymentDetail_query->first();
                if(!empty($todaysMcashPaymentDetail_result)){
                    $todaysMcashPaymentDetail = $todaysMcashPaymentDetail_result->toArray();
                }else{
                    $todaysMcashPaymentDetail = array();
                }
                
				$todaysMcashPayment = $todaysMcashPaymentDetail['today_sale'];
				if(empty($todaysMcashPayment)){
						$todaysMcashPayment = 0;
				}
				
                $yesterdayMobileSaleData_query = $this->MobileReSales->find('all', [
								 'conditions' => ['date(MobileReSales.created)' => date('Y-m-d',strtotime('-1 day')),'MobileReSales.refund_status IS NULL','MobileReSales.kiosk_id'=>$kioskId]
                                            ]);
                $yesterdayMobileSaleData_query 
                       ->select(['yesterdayMobileSale'=>'CASE WHEN MobileReSales.discounted_price is NULL THEN MobileReSales.selling_price ELSE MobileReSales.discounted_price END'])
                       ->select('MobileReSales.discounted_price')
                       ->select( 'MobileReSales.selling_price');
                       //pr($mobileSale_query);die;
                $yesterdayMobileSaleData_query = $yesterdayMobileSaleData_query->hydrate(false);
                if(!empty($yesterdayMobileSaleData_query)){
                    $yesterdayMobileSaleData = $yesterdayMobileSaleData_query->toArray();
                }else{
                    $yesterdayMobileSaleData = array();
                }
				
				$yesterdayMobileSale = 0;
				if(!empty($yesterdayMobileSaleData)){
						foreach($yesterdayMobileSaleData as $key=>$yesterdayData){
					$yesterdayMobileSale+= $yesterdayData['yesterdayMobileSale'];
						}
				}
                
                $yesterdaysMcardPaymentDetail_query = $this->MobileReSalePayments->find('all',['conditions' => ['Date(MobileReSalePayments.created)' => date('Y-m-d',strtotime('-1 day')),'MobileReSalePayments.kiosk_id'=>$kisk_id,'MobileReSalePayments.payment_method' => 'Card']]);
                $yesterdaysMcardPaymentDetail_query
                          ->select(['yesterday_sale' => $yesterdaysMcardPaymentDetail_query->func()->sum('MobileReSalePayments.amount')]);
                $yesterdaysMcardPaymentDetail_result = $yesterdaysMcardPaymentDetail_query->first();
                if(!empty($yesterdaysMcardPaymentDetail_result)){
                    $yesterdaysMcardPaymentDetail = $yesterdaysMcardPaymentDetail_result->toArray();
                }else{
                    $yesterdaysMcardPaymentDetail = array();
                }
                
                
				$yesterdaysMcardPayment = $yesterdaysMcardPaymentDetail['yesterday_sale'];
				if(empty($yesterdaysMcardPayment)){
						$yesterdaysMcardPayment = 0;
				}
				
                $yesterdaysMcashPaymentDetail_query = $this->MobileReSalePayments->find('all',['conditions' => ['Date(MobileReSalePayments.created)' => date('Y-m-d',strtotime('-1 day')),'MobileReSalePayments.kiosk_id'=>$kisk_id,'MobileReSalePayments.payment_method' => 'Cash','MobileReSalePayments.amount >=' => 0]]);
                $yesterdaysMcashPaymentDetail_query
                          ->select(['yesterday_sale' => $yesterdaysMcashPaymentDetail_query->func()->sum('MobileReSalePayments.amount')]);
                $yesterdaysMcashPaymentDetail_result = $yesterdaysMcashPaymentDetail_query->first();
                if(!empty($yesterdaysMcashPaymentDetail_result)){
                    $yesterdaysMcashPaymentDetail = $yesterdaysMcashPaymentDetail_result->toArray();
                }else{
                    $yesterdaysMcashPaymentDetail = array();
                }
                
				$yesterdaysMcashPayment = $yesterdaysMcashPaymentDetail['yesterday_sale'];
				if(empty($yesterdaysMcashPayment)){
						$yesterdaysMcashPayment = 0;
				}
			
			
                $mobileRefund_query = $this->MobileReSales->find('all',['conditions' => ['Date(MobileReSales.created)' => $date,'MobileReSales.refund_status'=>1,'MobileReSales.kiosk_id'=>$kioskId]]);
                $mobileRefund_query
                          ->select('MobileReSales.refund_price')
                          ->select('MobileReSales.refund_gain')
                          ->select(['todayTotalRefund' => $mobileRefund_query->func()->sum('MobileReSales.refund_price')])
                          ->select(['todayTotalRefundGain' => $mobileRefund_query->func()->sum('MobileReSales.refund_gain')]);
                $mobileRefund_result = $mobileRefund_query->first();
                if(!empty($mobileRefund_result)){
                    $mobileRefund = $mobileRefund_result->toArray();
                }else{
                    $mobileRefund = array();
                }
                							
				$todayMobileRefund = '';
				if(!empty($mobileRefund['todayTotalRefund'])){
						$todayMobileRefund = $mobileRefund['todayTotalRefund'];
				}
				
                $yesterdayMobileRefundData_query = $this->MobileReSales->find('all',['conditions' => ['date(MobileReSales.created) = CURDATE() - INTERVAL 1 DAY','MobileReSales.refund_status'=>1,'MobileReSales.kiosk_id'=>$kioskId]]);
                $yesterdayMobileRefundData_query
                          ->select('MobileReSales.refund_price')
                          ->select('MobileReSales.refund_gain')
                          ->select(['yesterdayTotalRefund' => $yesterdayMobileRefundData_query->func()->sum('MobileReSales.refund_price')])
                          ->select(['yesterdayTotalRefundGain' => $yesterdayMobileRefundData_query->func()->sum('MobileReSales.refund_gain')]);
                $yesterdayMobileRefundData_result = $yesterdayMobileRefundData_query->first();
                if(!empty($yesterdayMobileRefundData_result)){
                    $yesterdayMobileRefundData = $yesterdayMobileRefundData_result->toArray();
                }else{
                    $yesterdayMobileRefundData = array();
                }
                
				$yesterdayMobileRefund = '';
				if(!empty($yesterdayMobileRefundData['yesterdayTotalRefund'])){
						$yesterdayMobileRefund = $yesterdayMobileRefundData['yesterdayTotalRefund'];
				}
				
				if($kioskId == 0){
					$k_id = 10000;
				}else{
					$k_id =$kioskId;
				}
				
				$conn = ConnectionManager::get('default');
                $stmt = $conn->execute("SELECT `cost_price`, `topedup_price`,
											case when `topedup_price` is NULL OR `topedup_price` = 0 THEN 
											`cost_price` 
											ELSE 
											`topedup_price` 
											END 
											as `todayMobilePurchase`
											from `mobile_purchases`as MobilePurchase WHERE date(`created`) = '$date' AND `purchased_by_kiosk`=$k_id AND `purchase_status` = 0");
                $mobilePurchase = $stmt ->fetchAll('assoc');
				$todayMobilePurchase = 0;
				if(!empty($mobilePurchase)){
						foreach($mobilePurchase as $key=>$purchaseData){
								$todayMobilePurchase+= $purchaseData['todayMobilePurchase'];
						}
				}
				
				//we are returning unlock sale, card payment and unlock refund in an array
				if($kskId && $dte){
						$monthlyKioskSaleArr = array('mobileData' => array(
																				'date' => $dte,
																				'mobile_sale' => $todayMobileSale,
																				'card_payment' => $todaysMcardPayment,
																				'mobile_refund' => $todayMobileRefund,
																				'mobile_purchase' => $todayMobilePurchase
																								)
																		 );
						return $monthlyKioskSaleArr;
				}
				
                $conn = ConnectionManager::get('default');
                $stmt = $conn->execute("SELECT `cost_price`, `topedup_price`, 
											case when `topedup_price` is NULL OR `topedup_price` = 0 THEN 
											`cost_price` 
											ELSE 
											`topedup_price` 
											END 
											as `yesterdayMobilePurchase`
											from `mobile_purchases`as MobilePurchase WHERE date(`created`) = CURDATE() - INTERVAL 1 DAY AND `kiosk_id`=$k_id AND `purchase_status` = 0");
                $yesterdayMobilePurchaseData = $stmt ->fetchAll('assoc');
				
				$yesterdayMobilePurchase = 0;
				if(!empty($yesterdayMobilePurchaseData)){
						foreach($yesterdayMobilePurchaseData as $key=>$yesterdayPurchaseData){
					$yesterdayMobilePurchase+= $yesterdayPurchaseData['yesterdayMobilePurchase'];
						}
				}
				
				$this->set(compact('todayMobileSale', 'yesterdayMobileSale','todayMobileRefund', 'yesterdayMobileRefund','todayMobilePurchase','yesterdayMobilePurchase','todaysMcardPayment','yesterdaysMcardPayment','todaysMcashPayment','yesterdaysMcashPayment'));
    }
    
    public function dashboard(){
		//echo'hi';die;
		if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER ||
		   $this->request->session()->read('Auth.User.group_id') == SALESMAN ||
		   $this->request->session()->read('Auth.User.group_id') == inventory_manager||
		   $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER
		   ){
			$this->dashboard_admin();
			//$this->render('dashboard_admin');
		}elseif($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
			$this->dashboard_kiosk();
			$this->render('dashboard_kiosk');
		}elseif($this->request->session()->read('Auth.User.group_id') == MANAGERS){
			$this->dashboard_manager();
			//$this->render('dashboard_manager'); //udate on oct15,2015
		}elseif($this->request->session()->read('Auth.User.group_id') == UNLOCK_TECHNICIANS){
			$this->dashboard_unlock();
			$this->render('dashboard_unlock');
		}elseif($this->request->session()->read('Auth.User.group_id') == REPAIR_TECHNICIANS){
			$this->dashboard_service_center();
			$this->render('dashboard_service_center');
		}else{
			$this->render('dashboard');
		}
    }
     private function dashboard_manager(){
		$this->kioskTotalSale();
	    $this->render('kiosk_total_sale');
    }
    private function dashboard_admin(){
		$this->kioskTotalSale();
		$this->render('kiosk_total_sale');
    }
    
	 public function kioskTotalSale(){
		//echo'hi';die;
		$vat = $this->VAT;
		$this->set(compact('vat'));
		$kiosks_query = $this->Kiosks->find('list',array('conditions' => array('Kiosks.status'=>1,'`Kiosks`.`id` <>'=> 10000),
												'keyField' => 'id',
												'valueField' => 'name',
												  'order' => 'Kiosks.name asc' ));
		$kiosks_query = $kiosks_query->hydrate(false);
		if(!empty($kiosks_query)){
			$kiosks = $kiosks_query->toArray();
		}else{
			$kiosks = array();
		}
		$kiosks[0] = "Warehouse";
		 
		$this->get_repair_sale();
		$this->get_unlock_sale();
		$this->get_product_sale();
		$this->get_credit_refund(); //for admin
		$this->get_mobile_data();
		$this->get_t_credit_refund();
		$this->mobile_monthly_data();
		$this->get_blk_mobile_data(); // for blk
		$this->blk_mobile_monthly_data(); // for blk
		$this->monthly_product_sale();
		$this->monthly_unlock_sale();
		$this->monthly_repair_sale();
		$this->get_t_product_sale();
		$this->get_t_month_product_sale();
		$this->set(compact('kiosks','manager_kiosks'));
    }
	
	private function dashboard_kiosk(){
		$currency = $this->setting['currency_symbol'];
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		$this->get_repair_sale();
		$this->get_unlock_sale();
		$this->get_product_sale();
		$this->get_credit_refund(); //for kiosk
        $this->get_t_credit_refund();
		$this->get_mobile_data();
		$this->mobile_monthly_data();
		$this->monthly_product_sale();
		$this->monthly_unlock_sale();
		$this->monthly_repair_sale();
		
		//pr($this->today_refund($kiosk_id));die;
		extract($this->previousMonthBlkMobileSale($kiosk_id));
		extract($this->currentMonthBlkMobileSale($kiosk_id));
		extract($this->todayMobileSale($kiosk_id,$date = ""));
		extract($this->yesterdayMobileSale($kiosk_id,$date = ""));
		extract($this->today_refund($kiosk_id,$date = ""));
		extract($this->yesterday_refund($kiosk_id));
		extract($this->currentMnthRefund($kiosk_id));
		extract($this->privousMnthRefund($kiosk_id));
		$this->get_t_product_sale();
		$this->get_t_month_product_sale();
		$this->set(compact('currentMonthBlkMobileRefund'));
		$this->set(compact('previousMonthBlkMobileRefund'));
		$this->set(compact('yesterdayBlkMobileRefund'));
		$this->set(compact('todayBlkMobileRefund'));
		$this->set(compact('todayBlkMobileSale','todaysBlkMcardPayment','todaysBlkMcashPayment'));
		$this->set(compact('yesterdayBlkMobileSale','yesterdaysBlkMcardPayment','yesterdaysBlkMcashPayment'));
		$this->set(compact('currentMonthBlkMobileSale','currentMonthBlkMcardPayment','currentMonthBlkMcashPayment'));
		$this->set(compact('previousMonthBlkMobileSale','previousMonthBlkMcardPayment'));
		$vat = $this->VAT;
		$this->set(compact('vat'));
		
		
		//checking for newly transferred orders for the current kiosk through stock transfer
		$kioskOrders_query = $this->KioskOrders->find('all',array('conditions'=>array('KioskOrders.kiosk_id'=>$kiosk_id,'DATE(KioskOrders.created)>=DATE_ADD(CURDATE(), INTERVAL -3 DAY)'),'fields'=>array('id','created')));
		$kioskOrders_query = $kioskOrders_query->hydrate(false);
		if(!empty($kioskOrders_query)){
			$kioskOrders = $kioskOrders_query->toArray();
		}else{
			$kioskOrders = array(); 
		}
		
		$kioskOrderIds = array();
		foreach($kioskOrders as $key=>$kioskOrder){
			$kioskOrderIds[] =$kioskOrder['id'];
		}
		if(empty($kioskOrderIds)){
			$kioskOrderIds = array(0 => null);
		}
		$stockTransfer_query = $this->StockTransfer->find('all',array('conditions'=>array('StockTransfer.kiosk_order_id IN'=>$kioskOrderIds),
																	  'contain' => ['Products']
																	  ));
		$stockTransfer_query = $stockTransfer_query->hydrate(false);
		if(!empty($stockTransfer_query)){
			$stockTransfer =  $stockTransfer_query->toArray();
		}else{
			$stockTransfer = array();
		}
		//pr($stockTransfer);die;
		$notificationStatement = array();
		foreach($stockTransfer as $key=>$productDetails){
			if($productDetails['quantity']!=0){
			$notificationStatement[] = $productDetails['quantity']." ".$productDetails['product']['product']." with product code ".$productDetails['product']['product_code']." and price ".$currency.$productDetails['product']['selling_price']." have been transferred. Please receive them.<br/>";
			}
		}
		
		//checking the newly created orders globally in products table
		$products_query = $this->Products->find('all',array('conditions'=>array('DATE(Products.created)>=DATE_ADD(CURDATE(), INTERVAL -3 DAY)')));
		$products_query = $products_query->hydrate(false);
		if(!empty($products_query)){
			$products = $products_query->toArray();
		}else{
			$products = array();
		}
		$productNofification = array();
		foreach($products as $key=>$product){
			$productNofification[] = "A new product:{$product['product']} with the product-code:{$product['product_code']} and price of {$currency}{$product['selling_price']} has been added to the global stock.<br/>";
		}
		
		//checkin input of new products in warehouse stocks
		$warehouseStock_query = $this->WarehouseStock->find('all',array('conditions'=>array('DATE(WarehouseStock.created)>=DATE_ADD(CURDATE(), INTERVAL -3 DAY)'),'fields'=>array('id','product_id')));
		$warehouseStock_query = $warehouseStock_query->hydrate(false);
		if(!empty($warehouseStock_query)){
			$warehouseStock = $warehouseStock_query->toArray();
		}else{
			$warehouseStock =  array();
		}
		$warehouseProductIds = array();
		foreach($warehouseStock as $key=>$warehouseStockProducts){
			$warehouseProductIds[$warehouseStockProducts['product_id']] = $warehouseStockProducts['product_id'];
		}
		if(empty($warehouseProductIds)){
			$warehouseProductIds = array(0 => null);
		}
		$warehouseProducts_query = $this->Products->find('all',array('conditions'=>array('Products.id IN'=>$warehouseProductIds),'fields'=>array('id','product','product_code','selling_price')));
		$warehouseProducts_query = $warehouseProducts_query->hydrate(false);
		if(!empty($warehouseProducts_query)){
			$warehouseProducts = $warehouseProducts_query->toArray();
		}else{
			$warehouseProducts = array();
		}
		
		$warehouseProductNotification = array();
		foreach($warehouseProducts as $key=>$warehouseProduct){
			$warehouseProductNotification[] = "Product:{$warehouseProduct['product']} with the product-code:{$warehouseProduct['product_code']} and price of Approx. {$currency}{$warehouseProduct['selling_price']} has been added to the global stock.<br/>";
		}
	
		//checkin modification in mobile repair prices
		
		$problemType = Configure::read('problem_type');
		$mobileRepairPrices_query = $this->MobileRepairPrices->find('all',array('conditions'=>array('DATE(MobileRepairPrices.modified)>=DATE_ADD(CURDATE(), INTERVAL -3 DAY)')));
		$mobileRepairPrices_query = $mobileRepairPrices_query->hydrate(false);
		if(!empty($mobileRepairPrices_query)){
			$mobileRepairPrices = $mobileRepairPrices_query->toArray();
		}else{
			$mobileRepairPrices = array();
		}
		
		
		$brandName_query = $this->Brands->find('list',array(
													  'keyField' => 'id',
													  'valueField' => 'brand',
													  ));
		$brandName_query = $brandName_query->hydrate(false);
		if(!empty($brandName_query)){
			$brandName = $brandName_query->toArray();	
		}else{
			$brandName = array();
		}
		$mobileRepairPriceNotification = array();
		$repairMobileModelIds = array();
		foreach($mobileRepairPrices as $key=>$mobileRepairPrice){
			$repairMobileModelIds[$mobileRepairPrice['mobile_model_id']] = $mobileRepairPrice['mobile_model_id'];
		}
		if(empty($repairMobileModelIds)){
			$repairMobileModelIds = array(0 => null);
		}
		$repairMobileModelNames_query = $this->MobileModels->find('list',array('conditions'=>array('MobileModels.id IN'=>$repairMobileModelIds),
																			   'keyField' => 'id',
																			   'valueField' => 'model',
																			   ));
		$repairMobileModelNames_query = $repairMobileModelNames_query->hydrate(false);
		if(!empty($repairMobileModelNames_query)){
			$repairMobileModelNames = $repairMobileModelNames_query->toArray();
		}else{
			$repairMobileModelNames = array();
		}
		foreach($mobileRepairPrices as $key=>$mobileRepairPrice){
			$mobileRepairPriceNotification[] = "Repair price of {$brandName[$mobileRepairPrice['brand_id']]}:{$repairMobileModelNames[$mobileRepairPrice['mobile_model_id']]}(problem type:{$problemType[$mobileRepairPrice['problem_type']]})has been updated to {$currency}{$mobileRepairPrice['repair_price']} with a repair day time frame of {$mobileRepairPrice['repair_days']} days.<br/>";
		}
		
		//checking modification in mobile unlock prices
		$mobileUnlockPrices_query = $this->MobileUnlockPrices->find('all',array('conditions'=>array('DATE(MobileUnlockPrices.modified)>=DATE_ADD(CURDATE(), INTERVAL -3 DAY)')));
		$mobileUnlockPrices_query = $mobileUnlockPrices_query->hydrate(false);
		if(!empty($mobileUnlockPrices_query)){
			$mobileUnlockPrices = $mobileUnlockPrices_query->toArray();
		}else{
			$mobileUnlockPrices = array();
		}
		$networks_query = $this->Networks->find('list',array(
													   'keyField' => 'id',
													   'valueField' => 'name',
													   ));
		$networks_query = $networks_query->hydrate(false);
		if(!empty($networks_query)){
			$networks = $networks_query->toArray();
		}else{
			$networks =  array();
		}
		$unlockMobileModelIds = array();
		foreach($mobileUnlockPrices as $key=>$mobileUnlockPrice){
			$unlockMobileModelIds[$mobileUnlockPrice['mobile_model_id']] = $mobileUnlockPrice['mobile_model_id'];
		}
		if(empty($unlockMobileModelIds)){
				$unlockMobileModelIds = array(0 => null);
		}
		
		$unlockMobileModelNames_query = $this->MobileModels->find('list',array('conditions'=>array('MobileModels.id IN'=>$unlockMobileModelIds),
																		 'keyField' => 'id',
																		 'valueField' => 'model',
																		 ));
		$unlockMobileModelNames_query = $unlockMobileModelNames_query->hydrate(false);
		if(!empty($unlockMobileModelNames_query)){
			$unlockMobileModelNames = $unlockMobileModelNames_query->toArray();	
		}else{
			$unlockMobileModelNames = array();
		}
		$mobileUnlockPriceNotification = array();
		foreach($mobileUnlockPrices as $key=>$mobileUnlockPrice){
			$mobileUnlockPriceNotification[] = "Unlock price of {$brandName[$mobileUnlockPrice['brand_id']]}:{$unlockMobileModelNames[$mobileUnlockPrice['mobile_model_id']]}(network type:{$networks[$mobileUnlockPrice['network_id']]})has been updated to {$currency}{$mobileUnlockPrice['unlocking_price']} with a repair day time frame of {$mobileUnlockPrice['unlocking_days']} days.<br/>";
		}
		$this->set(compact('notificationStatement','currency','productNofification','warehouseProductNotification','mobileRepairPriceNotification','mobileUnlockPriceNotification'));
    }
	
//    public function kioskTotalSale(){
//		$vat = $this->VAT;
//		$this->set(compact('vat'));
//		$kiosks_query = $this->Kiosks->find('list',[
//                                                'conditions' => ['Kiosks.status'=>1,'`Kiosks`.`id` <>'=> 10000],
//												'keyField' => 'id',
//                                                'valueField' => 'name',
//												'order' => ['Kiosks.name asc']
//                                             ]);
//        if(!empty($kiosks_query)){
//            $kiosks = $kiosks_query->toArray();
//        }else{
//            $kiosks = array();
//        }
//		$kiosks[0] = "Warehouse";
//		$this->get_repair_sale();
//		$this->get_unlock_sale();
//		$this->get_product_sale();
//		$this->get_credit_refund(); //for admin
//		$this->get_mobile_data();
//		$this->mobile_monthly_data();
//		$this->get_blk_mobile_data(); // for blk
//		$this->blk_mobile_monthly_data(); // for blk
//		$this->monthly_product_sale();
//		$this->monthly_unlock_sale();
//		$this->monthly_repair_sale();
//		$this->get_t_product_sale();
//		$this->get_t_month_product_sale();
//		$this->set(compact('kiosks'));
//    }
    
    private function get_credit_refund($kskId = '', $dte = ''){
		//Configure::load('common-arrays');
		$pmtTypes = Configure::read('payment_type');
		if($kskId){
			//for monthly_kiosk_sale page
			$kioskId = $kskId;
		}else{
			$kiosk_id = $this->request->Session()->read('kiosk_id');
			if(!empty($this->request->params['pass']) && array_key_exists(0,$this->request->params['pass'])){
				//for admin kiosk_total_sale
				$kioskId = $this->request->params['pass'][0];
			}elseif((int)$kiosk_id){
				$kioskId = $kiosk_id;
			}else{
				$kioskId = 0;
			}
		}
		
		$date = date('Y-m-d');		
		if($dte){$date = $dte;}
		
		if((int)$kioskId == 0){
			$creditNotePmtSource = "credit_payment_details";
			$creditNoteReceiptSource = "credit_receipts";
		}else{
			$creditNotePmtSource = "kiosk_{$kioskId}_credit_payment_details";
			$creditNoteReceiptSource = "kiosk_{$kioskId}_credit_receipts";
		}
				
		//$this->CreditPaymentDetail->setSource($creditNotePmtSource);
        $creditNotePmtTable = TableRegistry::get($creditNotePmtSource,[
                                                                                    'table' => $creditNotePmtSource,
                                                                                ]);
		//$this->CreditReceipt->setSource($creditNoteReceiptSource);
        $creditNoteReceiptTable = TableRegistry::get($creditNoteReceiptSource,[
                                                                                    'table' => $creditNoteReceiptSource,
                                                                                ]);
		
		//get product sale
		$creditNoteRawQry = "SELECT `CreditPaymentDetail`.id,`CreditPaymentDetail`.credit_receipt_id,
									`CreditPaymentDetail`.payment_method,`CreditPaymentDetail`.description,
									`CreditPaymentDetail`.amount,`CreditPaymentDetail`.payment_status,
									`CreditPaymentDetail`.status,`CreditPaymentDetail`.created as payCreated
										, `CreditReceipt`.*
										  FROM
										  `$creditNotePmtSource` AS `CreditPaymentDetail`
										  LEFT JOIN
										  `$creditNoteReceiptSource` AS `CreditReceipt`
										  ON (`CreditPaymentDetail`.`credit_receipt_id` = `CreditReceipt`.`id`)
										  WHERE (
													%s
												)
										  ORDER BY `CreditPaymentDetail`.`id`";
		$creditNoteQry = sprintf($creditNoteRawQry, "(DATE(`CreditPaymentDetail`.`created`) = '$date')");
        $conn = ConnectionManager::get('default');
        $stmt = $conn->execute($creditNoteQry);
        $todayCreditNote = $stmt ->fetchAll('assoc');
        //$todayCreditNote = $this->CreditPaymentDetail->query($creditNoteQry);        
		list($todaysCNSale,$today_credit_to_other_changes_CN) = $this->getCreditNoteSale($todayCreditNote);
		
		//yesterday credits
		$yesterday = date('Y-m-d', strtotime(' -1 day'));
		$creditNoteQry = sprintf($creditNoteRawQry, "(DATE(`CreditPaymentDetail`.`created`) = '$yesterday')");
        $conn = ConnectionManager::get('default');
        $stmt = $conn->execute($creditNoteQry);
        $yesterdayCreditNote = $stmt ->fetchAll('assoc');
		//$yesterdayCreditNote = $this->CreditPaymentDetail->query($creditNoteQry);
		list($yesterdayCNSale,$y_credit_to_other_changes_CN) = $this->getCreditNoteSale($yesterdayCreditNote);
		//die;
		//-----------------------------------------------------
		$currentDate = date('Y-m-d',strtotime(' +1 day'));
		//adding an extra day to get correct result of current month
		$monthStart = date('Y-m-1');
		$month_ini = new \DateTime("first day of last month");
        //pr($month_ini);die; //using datetime class of PHP
		$month_end = new \DateTime("last day of last month");
		$previousMonthStart = $month_ini->format('Y-m-d');
		$previousMonthEnd = $month_end->format('Y-m-d');
		$previousMonthEndPlus = date('Y-m-d',strtotime($previousMonthEnd.' +1 day'));
		//adding one more day to last month to get correct data from db for last month
		//-----------------------------------------------------
		//current month credits
		$creditNoteQry = sprintf($creditNoteRawQry, "`CreditPaymentDetail`.`created` BETWEEN '$monthStart' AND '$currentDate'");
		//$currMonthCreditNote = $this->CreditPaymentDetail->query($creditNoteQry);
        $conn = ConnectionManager::get('default');
        $stmt = $conn->execute($creditNoteQry);
        $currMonthCreditNote = $stmt ->fetchAll('assoc');
		list($currMonthCNSale,$cm_credit_to_other_changes_CN) = $this->getCreditNoteSale($currMonthCreditNote);
		//current month credits
		$creditNoteQry = sprintf($creditNoteRawQry, "`CreditPaymentDetail`.`created` BETWEEN '$previousMonthStart' AND '$previousMonthEndPlus'");
        $conn = ConnectionManager::get('default');
        $stmt = $conn->execute($creditNoteQry);
        $prevMonthCreditNote = $stmt ->fetchAll('assoc');
		//$prevMonthCreditNote = $this->CreditPaymentDetail->query($creditNoteQry);
		list($prevMonthCNSale,$pm_credit_to_other_changes_CN) = $this->getCreditNoteSale($prevMonthCreditNote);
		
		//pr($currMonthCNSale);
		$this->set(compact('todaysCNSale', 'yesterdayCNSale', 'currMonthCNSale', 'prevMonthCNSale',
						   'today_credit_to_other_changes_CN','y_credit_to_other_changes_CN','cm_credit_to_other_changes_CN','pm_credit_to_other_changes_CN'
						   ));
    }
    
    public function repairComments4Kiosk(){
        $kiosk_id = $this->request->Session()->read('kiosk_id');
               //for ADMINISTRATORS (we are showing all the comments to admins, including kiosk's)
        if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER ||
          $this->request->session()->read('Auth.User.group_id') == MANAGERS){
            $repairusers_query = $this->Users->find('list',[
                                                            'conditions' => [
                                                                             'Users.group_id IN' => [REPAIR_TECHNICIANS, ADMINISTRATORS, MANAGERS, KIOSK_USERS]],
															 'keyField' => 'id',
                                                            'valueField' => 'username',
																	 
													 ]);
            if(!empty($repairusers_query)){
                $repairusers = $repairusers_query->toArray();
            }else{
                $repairusers = array();
            }
        }else{
             $repairusers_query = $this->Users->find('list',[
                                                            'conditions' => [
                                                                             'Users.group_id IN' => [REPAIR_TECHNICIANS, ADMINISTRATORS, MANAGERS]],
															 'keyField' => 'id',
                                                            'valueField' => 'username',
																	 
													 ]);
            if(!empty($repairusers_query)){
                $repairusers = $repairusers_query->toArray();
            }else{
                $repairusers = array();
            }
           
        }
        $kiosks_query = $this->Kiosks->find('list',[
                                                'conditions' => ['Kiosks.status'=>1],
												'keyField' => 'id',
                                                'valueField' => 'name',
												'order' => ['Kiosks.name asc']
                                             ]);
        if(!empty($kiosks_query)){
            $kiosks = $kiosks_query->toArray();
        }else{
            $kiosks = array();
        }
        if($kiosk_id){
               $this->paginate = [
                                    'contain' =>['MobileRepairs'], 
                                    'conditions' => [
                                        'CommentMobileRepairs.user_id IN' => array_keys($repairusers),
                                         'MobileRepairs.kiosk_id' => $kiosk_id,
                                         'DATE(CommentMobileRepairs.created) >= DATE_ADD(CURDATE(),INTERVAL -20 DAY)' 
                                                     ],
                                    'limit' => 100,
                                    'order' => ['CommentMobileRepairs.id desc']
                                ];
           
        }else{
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
				   //  pr($managerKiosk);
					 if(!empty($managerKiosk)){
						   //$conditionArr['MobilePurchases.kiosk_id IN'] = $managerKiosk;
						   $this->paginate = [
                                    'contain' =>['MobileRepairs'],
                                    'conditions' => [
										'MobileRepairs.kiosk_id IN' => $managerKiosk,
                                        'CommentMobileRepairs.user_id IN' => array_keys($repairusers),
                                         'DATE(CommentMobileRepairs.created) >= DATE_ADD(CURDATE(),INTERVAL -20 DAY)' 
                                                     ],
                                    'limit' => 100,
                                    'order' => ['CommentMobileRepairs.id desc']
                                ];
					 }
				}else{ 
					$this->paginate = [
                                    'contain' =>['MobileRepairs'],
                                    'conditions' => [
                                        'CommentMobileRepairs.user_id IN' => array_keys($repairusers),
                                        
                                         'DATE(CommentMobileRepairs.created) >= DATE_ADD(CURDATE(),INTERVAL -20 DAY)' 
                                                     ],
                                    'limit' => 100,
                                    'order' => ['CommentMobileRepairs.id desc']
                                ];
				 
				}
              
        }
       //  pr($this->paginate);
        $comments_query = $this->paginate('CommentMobileRepairs');
        if(!empty($comments_query)){
            $comments = $comments_query->toArray();
        }else{
            $comments = array();
        }
       //pr($comments);
        $this->set(compact('comments','kiosks','repairusers'));
    }
    
     public function searchRepairComments4Kiosk(){
        $kiosk_id = $this->request->Session()->read('kiosk_id');
        if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER ||
           $this->request->session()->read('Auth.User.group_id') == MANAGERS){
            $repairusers_query = $this->Users->find('list',[
                                                            'conditions' => [
                                                                             'Users.group_id IN' => [REPAIR_TECHNICIANS, ADMINISTRATORS, MANAGERS, KIOSK_USERS]],
															 'keyField' => 'id',
                                                            'valueField' => 'username',
																	 
													 ]);
            if(!empty($repairusers_query)){
                $repairusers = $repairusers_query->toArray();
            }else{
                $repairusers = array();
            }
        }else{
             $repairusers_query = $this->Users->find('list',[
                                                            'conditions' => [
                                                                             'Users.group_id IN' => [REPAIR_TECHNICIANS, ADMINISTRATORS, MANAGERS]],
															 'keyField' => 'id',
                                                            'valueField' => 'username',
																	 
													 ]);
            if(!empty($repairusers_query)){
                $repairusers = $repairusers_query->toArray();
            }else{
                $repairusers = array();
            }
        }
        $kiosks_query = $this->Kiosks->find('list',[
                                                'conditions' => ['Kiosks.status'=>1],
												'keyField' => 'id',
                                                'valueField' => 'name',
												'order' => ['Kiosks.name asc']
                                             ]);
        if(!empty($kiosks_query)){
            $kiosks = $kiosks_query->toArray();
        }else{
            $kiosks = array();
        }
       $conditionArr = array();
        $id = $this->request->query['id'];
        if(!empty($id)){
            $conditionArr[] = array('CommentMobileRepairs.mobile_repair_id' => $id);
        }
        $start_date = $this->request->query['start_date'];
        if(!empty($start_date)){
            $conditionArr[] = array("DATE(CommentMobileRepairs.created) >=" => date('Y-m-d',strtotime($start_date)));
        }
        $end_date = $this->request->query['end_date'];
        if(!empty($end_date)){
            $conditionArr[] = array("DATE(CommentMobileRepairs.created) <=" => date('Y-m-d',strtotime($end_date)));
        }
        
        if(!array_key_exists('DATE(CommentMobileRepairs.created) >=',array_values($conditionArr))){
            //if no date is chosen will show data starting from 20 days from current date as per the original page
            $conditionArr[] = array('DATE(CommentMobileRepairs.created) >= DATE_ADD(CURDATE(),INTERVAL -20 DAY)');
        }
        
        $kiosk = '';
        //pr($this->request->query);die;
        if(array_key_exists('kiosk',$this->request->query)){
            $kiosk = $this->request->query['kiosk'];
        }
        if($kiosk_id){//kiosk users
            $conditionArr[] = array('MobileRepairs.kiosk_id' => $kiosk_id);
        }elseif(!empty($kiosk) &&//only admins and managers will be able to search through kiosks
           ($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER ||
            $this->request->session()->read('Auth.User.group_id') == MANAGERS)){
            $conditionArr[] = array('MobileRepairs.kiosk_id' => $kiosk);
        }
        if(count($conditionArr)){
			//echo "kkdsdsd";
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
			   //  pr($managerKiosk);
				 if(!empty($managerKiosk)){
					   $conditionArr['MobileRepairs.kiosk_id IN'] = $managerKiosk;
				 }
			}
            $this->paginate = [
                                    'contain' =>['MobileRepairs'],
                                    'conditions' => [
                                        'CommentMobileRepairs.user_id IN' => array_keys($repairusers),
                                         $conditionArr
                                                     ],
                                    ['limit' => 100],
                                    ['order' => 'CommentMobileRepairs.id desc'],
                                ];
            
        }else{
				   $this->paginate = [
                                'contain' =>['MobileRepairs'],
                                    'conditions' => [
                                        'CommentMobileRepairs.user_id IN' => array_keys($repairusers),
                                         'MobileRepairs.kiosk_id' => $kiosk_id,
                                         'DATE(CommentMobileRepairs.created) >= DATE_ADD(CURDATE(),INTERVAL -20 DAY)' 
                                                     ],
                                    ['limit' => 100],
                                    ['order' => 'CommentMobileRepairs.id desc'],
                                ];
            
        }
        
        $comments_query = $this->paginate('CommentMobileRepairs');
        if(!empty($comments_query)){
            $comments = $comments_query->toArray();
        }else{
            $comments = array();
        }
        $this->set(compact('comments','kiosks','repairusers'));
        $this->render('repair_comments4_kiosk');
    }
    public function comments4UnlockCenter(){
         $users_query = $this->Users->find('list',[
                                                            'conditions' => [
                                                                             'Users.group_id IN' => [
                                                                                                     KIOSK_USERS,
                                                                                                     ADMINISTRATORS,
                                                                                                     MANAGERS
                                                                                                     ]
                                                                             ],
															 'keyField' => 'id',
                                                            'valueField' => 'username',
																	 
													 ]);
            if(!empty($users_query)){
                $users = $users_query->toArray();
            }else{
                $users = array();
            }
         $kiosks_query = $this->Kiosks->find('list',[
                                                'conditions' => ['kiosk_type'=>1],
												'keyField' => 'id',
                                                'valueField' => 'name',
												'order' => ['Kiosks.name asc']
                                             ]);
        if(!empty($kiosks_query)){
            $kiosks = $kiosks_query->toArray();
        }else{
            $kiosks = array();
        }
          
           $this->paginate = [
                                    'contain' =>['MobileUnlocks'], 
                                    'conditions' => [
                                         'CommentMobileUnlocks.user_id IN' => array_keys($users),
                                         'DATE(CommentMobileUnlocks.created) >= DATE_ADD(CURDATE(),INTERVAL -20 DAY)' 
                                                     ],
                                    'limit' =>[100] ,
                                    'order' => ['CommentMobileUnlocks.id desc']
                                ];
            
         
     //   pr($this->paginate);die;
         $comments_query = $this->paginate('CommentMobileUnlocks');
        if(!empty($comments_query)){
            $comments = $comments_query->toArray();
        }else{
            $comments = array();
        }
      // pr($comments);die;
        $this->set(compact('comments','kiosks','users'));
    }
    
    public function searchComments4UnlockCenter(){
	 $users_query = $this->Users->find('list',[
                                                            'conditions' => [
                                                                             'Users.group_id IN' => [
                                                                                                     KIOSK_USERS,
                                                                                                     ADMINISTRATORS,
                                                                                                     MANAGERS
                                                                                                     ]
                                                                             ],
															 'keyField' => 'id',
                                                            'valueField' => 'username',
																	 
													 ]);
            if(!empty($users_query)){
                $users = $users_query->toArray();
            }else{
                $users = array();
            }
         $kiosks_query = $this->Kiosks->find('list',[
                                                'conditions' => ['kiosk_type'=>1],
												'keyField' => 'id',
                                                'valueField' => 'name',
												'order' => ['Kiosks.name asc']
                                             ]);
        if(!empty($kiosks_query)){
            $kiosks = $kiosks_query->toArray();
        }else{
            $kiosks = array();
        }
          
	$conditionArr = array();
	$id = $this->request->query['id'];
	if(!empty($id)){
	    $conditionArr[] = array('CommentMobileUnlocks.mobile_unlock_id' => $id);
	}
	$start_date = $this->request->query['start_date'];
	if(!empty($start_date)){
	    $conditionArr[] = array("DATE(CommentMobileUnlocks.created) >=" => date('Y-m-d',strtotime($start_date)));
	}
	$end_date = $this->request->query['end_date'];
	if(!empty($end_date)){
	    $conditionArr[] = array("DATE(CommentMobileUnlocks.created) <=" => date('Y-m-d',strtotime($end_date)));
	}
	
	if(!array_key_exists('DATE(CommentMobileUnlocks.created) >=',array_values($conditionArr))){
	    //if no date is chosen will show data starting from 20 days from current date as per the original page
	    $conditionArr[] = array('DATE(CommentMobileUnlocks.created) >= DATE_ADD(CURDATE(),INTERVAL -20 DAY)');
	}
	
	$kiosk = '';
	if(array_key_exists('kiosk',$this->request->query)){
	    $kiosk = $this->request->query['kiosk'];
	}
	
	if(!empty($kiosk) &&//only unlock technician
	   ($this->request->session()->read('Auth.User.group_id') == UNLOCK_TECHNICIANS)){
	    $conditionArr[] = array('MobileUnlocks.kiosk_id' => $kiosk);
	}
	if(count($conditionArr)){
        $this->paginate = [
                                    'contain' =>['MobileUnlocks'], 
                                    'conditions' => [
                                         'CommentMobileUnlocks.user_id IN' => array_keys($users),
                                         $conditionArr
                                                     ],
                                    'limit' =>[100] ,
                                    'order' => ['CommentMobileUnlocks.id desc']
                                ];
    }else{
         $this->paginate = [
                                    'contain' =>['MobileUnlocks'], 
                                    'conditions' => [
                                         'CommentMobileUnlocks.user_id IN' => array_keys($users),
                                         'DATE(CommentMobileUnlocks.created) >= DATE_ADD(CURDATE(),INTERVAL -20 DAY)' 
                                                     ],
                                    'limit' =>[100] ,
                                    'order' => ['CommentMobileUnlocks.id desc']
                                ];
	     
	}
	  $comments_query = $this->paginate('CommentMobileUnlocks');
        if(!empty($comments_query)){
            $comments = $comments_query->toArray();
        }else{
            $comments = array();
        }
	 
	  $this->set(compact('comments','kiosks','users'));
	$this->render('comments4_unlock_center');
    }
    public function unlockComments4Kiosk(){
       $kiosk_id = $this->request->Session()->read('kiosk_id');
        if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER ||
           $this->request->session()->read('Auth.User.group_id') == MANAGERS){
             $unlockusers_query = $this->Users->find('list',[
                                                            'conditions' => [
                                                                             'Users.group_id IN' => [UNLOCK_TECHNICIANS, ADMINISTRATORS, MANAGERS, KIOSK_USERS]],
															 'keyField' => 'id',
                                                            'valueField' => 'username',
																	 
													 ]);
            if(!empty($unlockusers_query)){
                $unlockusers = $unlockusers_query->toArray();
            }else{
                $unlockusers = array();
            }
       }else{
             $unlockusers_query = $this->Users->find('list',[
                                                            'conditions' => [
                                                                             'Users.group_id IN' => [UNLOCK_TECHNICIANS, ADMINISTRATORS, MANAGERS]],
															 'keyField' => 'id',
                                                            'valueField' => 'username',
																	 
													 ]);
             if(!empty($unlockusers_query)){
                $unlockusers = $unlockusers_query->toArray();
            }else{
                $unlockusers = array();
            }
        }
        $kiosks_query = $this->Kiosks->find('list',[
                                                'conditions' => ['Kiosks.status'=>1],
												'keyField' => 'id',
                                                'valueField' => 'name',
												'order' => ['Kiosks.name asc']
                                             ]);
        if(!empty($kiosks_query)){
            $kiosks = $kiosks_query->toArray();
        }else{
            $kiosks = array();
        }if($kiosk_id){
           $this->paginate = [
                                    'contain' =>['MobileUnlocks'], 
                                    'conditions' => [
                                         'CommentMobileUnlocks.user_id IN' => array_keys($unlockusers),
                                         'MobileUnlocks.kiosk_id' => $kiosk_id,
                                         'DATE(CommentMobileUnlocks.created) >= DATE_ADD(CURDATE(),INTERVAL -20 DAY)' 
                                                     ],
                                    'limit' =>100 ,
                                    'order' => ['CommentMobileUnlocks.id desc']
                                ];
            
        }else{
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
				   //  pr($managerKiosk);
					 if(!empty($managerKiosk)){
						   //$conditionArr['MobilePurchases.kiosk_id IN'] = $managerKiosk;
						   $this->paginate = [
                                'contain' =>['MobileUnlocks'], 
                                    'conditions' => [
										'MobileUnlocks.kiosk_id IN' => $managerKiosk,
                                         'CommentMobileUnlocks.user_id IN' => array_keys($unlockusers),
                                         'DATE(CommentMobileUnlocks.created) >= DATE_ADD(CURDATE(),INTERVAL -20 DAY)' 
                                                     ],
                                    'limit' => 100,
                                    'order' => ['CommentMobileUnlocks.id desc'] 
                                ];
						   
					 }
				}else{
						$this->paginate = [
                                'contain' =>['MobileUnlocks'], 
                                    'conditions' => [
                                         'CommentMobileUnlocks.user_id IN' => array_keys($unlockusers),
                                         'DATE(CommentMobileUnlocks.created) >= DATE_ADD(CURDATE(),INTERVAL -20 DAY)' 
                                                     ],
                                    'limit' => 100,
                                    'order' => ['CommentMobileUnlocks.id desc'] 
                                ];
				}
            
        }
     //   pr($this->paginate);die;
         $comments_query = $this->paginate('CommentMobileUnlocks');
        if(!empty($comments_query)){
            $comments = $comments_query->toArray();
        }else{
            $comments = array();
        }
      // pr($comments);die;
        $this->set(compact('comments','kiosks','unlockusers'));
    }
    
    public function searchUnlockComments4Kiosk(){
          $kiosk_id = $this->request->Session()->read('kiosk_id');
          if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER ||
           $this->request->session()->read('Auth.User.group_id') == MANAGERS){
             $unlockusers_query = $this->Users->find('list',[
                                                            'conditions' => [
                                                                             'Users.group_id IN' => [UNLOCK_TECHNICIANS, ADMINISTRATORS, MANAGERS, KIOSK_USERS]],
															 'keyField' => 'id',
                                                            'valueField' => 'username',
																	 
													 ]);
            if(!empty($unlockusers_query)){
                $unlockusers = $unlockusers_query->toArray();
            }else{
                $unlockusers = array();
            }
          
        }else{
             $unlockusers_query = $this->Users->find('list',[
                                                            'conditions' => [
                                                                             'Users.group_id IN' => [UNLOCK_TECHNICIANS, ADMINISTRATORS, MANAGERS]],
															 'keyField' => 'id',
                                                            'valueField' => 'username',
																	 
													 ]);
            if(!empty($unlockusers_query)){
                $unlockusers = $unlockusers_query->toArray();
            }else{
                $unlockusers = array();
            }
	   
	} 
    //   pr($unlockusers); 
       $kiosks_query = $this->Kiosks->find('list',[
                                                'conditions' => ['Kiosks.status'=>1],
												'keyField' => 'id',
                                                'valueField' => 'name',
												'order' => ['Kiosks.name asc']
                                             ]);
        if(!empty($kiosks_query)){
            $kiosks = $kiosks_query->toArray();
        }else{
            $kiosks = array();
        }
        $conditionArr = array();
        $id = $this->request->query['id'];
        if(!empty($id)){
            $conditionArr[] = array('CommentMobileUnlocks.mobile_unlock_id' => $id);
        }
        $start_date = $this->request->query['start_date'];
        if(!empty($start_date)){
            $conditionArr[] = array("DATE(CommentMobileUnlocks.created) >=" => date('Y-m-d',strtotime($start_date)));
        }
        $end_date = $this->request->query['end_date'];
        if(!empty($end_date)){
            $conditionArr[] = array("DATE(CommentMobileUnlocks.created) <=" => date('Y-m-d',strtotime($end_date)));
        }
        
        if(!array_key_exists('DATE(CommentMobileUnlocks.created) >=',array_values($conditionArr))){
            //if no date is chosen will show data starting from 20 days from current date as per the original page
            $conditionArr[] = array('DATE(CommentMobileUnlocks.created) >= DATE_ADD(CURDATE(),INTERVAL -20 DAY)');
        }
        
        $kiosk = '';
      //  pr($this->request->query);
        if(array_key_exists('kiosk',$this->request->query)){
            $kiosk = $this->request->query['kiosk'];
        }
        
        if($kiosk_id){//kiosk users
            $conditionArr[] = array('MobileUnlocks.kiosk_id' => $kiosk_id);
        }elseif(!empty($kiosk) &&//only admins and managers will be able to search through kiosks
           ($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER ||
            $this->request->session()->read('Auth.User.group_id') == MANAGERS)){
            $conditionArr[] = array('MobileUnlocks.kiosk_id' => $kiosk);
        }
        if(count($conditionArr)){
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
			   //  pr($managerKiosk);
				 if(!empty($managerKiosk)){
					   $conditionArr['MobileUnlocks.kiosk_id IN'] = $managerKiosk;
				 }
			}
            $this->paginate = [
                                    'contain' =>['MobileUnlocks'],
                                    'conditions' => [
                                         'CommentMobileUnlocks.user_id IN' => array_keys($unlockusers),
                                            $conditionArr 
                                                     ],
                                    'limit' =>[100],
                                    'order' => ['CommentMobileUnlocks.id desc']
                                ];
        }else{
             $this->paginate = [
                                    'contain' =>['MobileUnlocks'], 
                                    'conditions' => [
                                         'CommentMobileUnlocks.user_id IN' => array_keys($unlockusers),
                                         'DATE(CommentMobileUnlocks.created) >= DATE_ADD(CURDATE(),INTERVAL -20 DAY' 
                                                     ],
                                     'limit' => [100],
                                     'order' => ['CommentMobileUnlocks.id desc'] 
                                ];
        }
         $comments_query = $this->paginate('CommentMobileUnlocks');
        if(!empty($comments_query)){
            $comments = $comments_query->toArray();
        }else{
            $comments = array();
        }
       $this->set(compact('comments','kiosks','unlockusers'));
        $this->render('unlock_comments4_kiosk');
    }
    
    public function newOrdersNotification(){
        $kiosk_id = $this->request->Session()->read('kiosk_id');
		$currency = Configure::read('CURRENCY_TYPE');
		$kioskOrders_query = $this->KioskOrders->find('all',array(
                                                           'conditions'=>array('KioskOrders.kiosk_id'=>$kiosk_id,
                                                                               'DATE(KioskOrders.created)>=DATE_ADD(CURDATE(), INTERVAL -3 DAY)'),
                                                           'fields'=>array('id','created')
                                                           )
                                                     );
        $kioskOrders_query = $kioskOrders_query->hydrate(false);
        if(!empty($kioskOrders_query)){
            $kioskOrders = $kioskOrders_query->toArray();
        }else{
            $kioskOrders = array();
        }
        $kioskOrderIds = array();
		foreach($kioskOrders as $key=>$kioskOrder){
			$kioskOrderIds[] =$kioskOrder['id'];
		}
        if(empty($kioskOrderIds)){
            $kioskOrderIds = array(0 => null);
        }
		$stockTransfer_query = $this->StockTransfer->find('all',array(
                                        'contain' => ['Products'],
                                                                      'conditions'=>array(
                                                                                          'StockTransfer.kiosk_order_id IN'=>$kioskOrderIds)));
        $stockTransfer_query = $stockTransfer_query->hydrate(false);
        if(!empty($stockTransfer_query)){
            $stockTransfer = $stockTransfer_query->toArray();
        }else{
            $stockTransfer = array();
        }
		$notificationStatement = array();
		foreach($stockTransfer as $key=>$productDetails){
           //  pr($productDetails);
			if($productDetails['quantity']!=0){
				$notificationStatement[] = $productDetails['quantity']." ".$productDetails['product']['product']." with product code ".$productDetails['product']['product_code']." and price ".$currency.$productDetails['product']['selling_price']." have been transferred. Please receive them.<br/>";
			}
		}
		$this->set(compact('notificationStatement','currency','productNofification','warehouseProductNotification','mobileRepairPriceNotification','mobileUnlockPriceNotification'));
	}
    
    private function getCreditNoteSale($todayCreditNote = array()){
		//pr($todayCreditNote);
		$cardEntryAmt = $chkEntryAmt = $bankEntryAmt = $creditentryAmt = $cashEntryAmt = $onCreditAmt = $cashAmt = $bnkTrnAmt = $chkAmt = $cardAmt = 0;
		foreach($todayCreditNote as $todayCN){
			$payCreated = date("y-m-d",strtotime($todayCN['payCreated']));
			$created = date("y-m-d",strtotime($todayCN['created']));
			
			$payment_created = strtotime($payCreated);
			$recipt_created = strtotime($created);
			
			$creditRecptID = $todayCN['id'];
			$pmtMethod = $todayCN['payment_method'];
			$pmtAmt = $todayCN['amount'];
			switch($pmtMethod){
				case 'On Credit':
					if($payment_created == $recipt_created){
						$onCreditAmt += $pmtAmt;
					}else{
						$creditentryAmt += $pmtAmt;
					}
					break;
				case 'Cash':
					if($payment_created == $recipt_created){
						$cashAmt += $pmtAmt;
					}else{
						$cashEntryAmt += $pmtAmt;
					}
					break;
				case 'Bank Transfer':
					if($payment_created == $recipt_created){
						$bnkTrnAmt += $pmtAmt;
					}else{
						$bankEntryAmt += $pmtAmt;
					}
					break;
				case 'Cheque':
					if($payment_created == $recipt_created){
						$chkAmt += $pmtAmt;
					}else{
						$chkEntryAmt += $pmtAmt;
					}
					break;
				case 'Card':
					if($payment_created == $recipt_created){
						$cardAmt += $pmtAmt;
					}else{
						$cardEntryAmt += $pmtAmt;
					}
					break;
			}
			//$totalAmt = $todayCN['CreditReceipt']['credit_amount'];
			
		}
		 $todaysCN = array('credit' => $onCreditAmt,'cash' => $cashAmt, 'bank_transfer' => $bnkTrnAmt, 'cheque' => $chkAmt, 'card' => $cardAmt,'cashEntryAmt' => $cashEntryAmt);
		 //pr($todaysCN);
			$credit_to_other_changes_CN = array('credit' => $creditentryAmt,'cash' => $cashEntryAmt, 'bank_transfer' => $bankEntryAmt, 'cheque' => $chkEntryAmt, 'card' => $cardEntryAmt);
			return array($todaysCN,$credit_to_other_changes_CN);
	}
    
    private function mobile_monthly_data(){
				$kiosk_id = $this->request->Session()->read('kiosk_id');
				if(!empty($this->request->params['pass']) && array_key_exists(0,$this->request->params['pass'])){//for admin kiosk_total_sale
						$kioskId = $this->request->params['pass'][0];
				}elseif((int)$kiosk_id){
						$kioskId = $kiosk_id;
				}else{
						$kioskId = 0;
				}

				$monthStart = date('Y-m-1');
				$month_ini = new \DateTime("first day of last month"); //using datetime class of PHP
				$month_end = new \DateTime("last day of last month");
				$previousMonthStart = $month_ini->format('Y-m-d');
				$previousMonthEnd = $month_end->format('Y-m-d');
				$previousMonthEndPlus = date('Y-m-d',strtotime($previousMonthEnd.' +1 day'));//adding one more day to last month to get correct data from db for last month
				//current mobile sale
                $currentMonthMobileSaleData_conn = ConnectionManager::get('default');
                $currentMonthMobileSaleData_stmt = $currentMonthMobileSaleData_conn->execute("SELECT `discounted_price`, `selling_price`, 
											case when `discounted_price` is NULL OR `discounted_price` = 0 THEN 
											`selling_price` 
											ELSE 
											`discounted_price` 
											END 
											as `currentMonthMobileSale`
											from `mobile_re_sales`as MobileReSale WHERE (`created` BETWEEN '$monthStart' AND CURDATE() + INTERVAL 1 DAY) AND `refund_status` is NULL AND `kiosk_id` = $kioskId"); 
                $currentMonthMobileSaleData = $currentMonthMobileSaleData_stmt ->fetchAll('assoc');
				//pr($currentMonthMobileSaleData);die;
				$currentMonthMobileSale = 0;
				if(!empty($currentMonthMobileSaleData)){
					//pr($currentMonthMobileSaleData);die;
						foreach($currentMonthMobileSaleData as $key=>$data){
					$currentMonthMobileSale+= $data['currentMonthMobileSale'];
						}  
				}
				if($kioskId == 0){
					$kiosk_Id = 10000;
				}else{
					$kiosk_Id = $kioskId;
				}
				
				
				$currentDate = date('Y-m-d',strtotime(' +1 day'));//adding an extra day to get correct result of current month;
                $currentMonthMcardPaymentDetail_query = $this->MobileReSalePayments->find('all',['conditions' =>[("MobileReSalePayments.created BETWEEN '$monthStart' AND '$currentDate'"),'MobileReSalePayments.kiosk_id'=>$kiosk_Id,'MobileReSalePayments.payment_method' => 'Card']]);
                $currentMonthMcardPaymentDetail_query
                          ->select(['currentMonthMobileSale' => $currentMonthMcardPaymentDetail_query->func()->sum('MobileReSalePayments.amount')]);
                $currentMonthMcardPaymentDetail_query = $currentMonthMcardPaymentDetail_query->hydrate(false);
                if(!empty($currentMonthMcardPaymentDetail_query)){
                    $currentMonthMcardPaymentDetail = $currentMonthMcardPaymentDetail_query->first();
                }else{
                    $currentMonthMcardPaymentDetail = array();
                }
                //pr($currentMonthMcardPaymentDetail);die;
				$currentMonthMcardPayment = $currentMonthMcardPaymentDetail['currentMonthMobileSale'];
				if(empty($currentMonthMcardPayment)){
						$currentMonthMcardPayment = 0;
				}
				
                $currentMonthMcashPaymentDetail_query = $this->MobileReSalePayments->find('all',['conditions' =>[("MobileReSalePayments.created BETWEEN '$monthStart' AND '$currentDate'"),'MobileReSalePayments.kiosk_id'=>$kiosk_Id,'MobileReSalePayments.payment_method' => 'Cash','MobileReSalePayments.amount >=' => 0]]);
                $currentMonthMcashPaymentDetail_query
                          ->select(['currentMonthMobileSale' => $currentMonthMcashPaymentDetail_query->func()->sum('MobileReSalePayments.amount')]);
                $currentMonthMcashPaymentDetail_query = $currentMonthMcashPaymentDetail_query->hydrate(false);
                if(!empty($currentMonthMcashPaymentDetail_query)){
                    $currentMonthMcashPaymentDetail = $currentMonthMcashPaymentDetail_query->first();
                }else{
                    $currentMonthMcashPaymentDetail = array();
                }

				$currentMonthMcashPayment = $currentMonthMcashPaymentDetail['currentMonthMobileSale'];
				if(empty($currentMonthMcashPayment)){
						$currentMonthMcashPayment = 0;
				}

				$conn = ConnectionManager::get('default');
                $stmt = $conn->execute("SELECT `discounted_price`, `selling_price`, 
											case when `discounted_price` is NULL OR `discounted_price` = 0 THEN 
											`selling_price` 
											ELSE 
											`discounted_price` 
											END 
											as `previousMonthMobileSale`
											from `mobile_re_sales`as MobileReSale WHERE (`created` BETWEEN '$previousMonthStart' AND '$previousMonthEndPlus') AND `refund_status` is NULL AND `kiosk_id` = $kioskId"); 
                $previousMonthMobileSaleData = $stmt ->fetchAll('assoc');
						//pr($previousMonthMobileSaleData);					
				$previousMonthMobileSale = 0;
				if(!empty($previousMonthMobileSaleData)){
						foreach($previousMonthMobileSaleData as $key=>$data){
					$previousMonthMobileSale+= $data['previousMonthMobileSale'];
						}  
				}
				
                $previousMonthMcardPaymentDetail_query = $this->MobileReSalePayments->find('all',[
                              'conditions' =>[("MobileReSalePayments.created BETWEEN '$previousMonthStart' AND
                                               '$previousMonthEndPlus'"),'MobileReSalePayments.kiosk_id'=>$kiosk_Id,
                                               'MobileReSalePayments.payment_method' => 'Card'
                                              ]
                                                                                            ]
                                                                                       );
                $previousMonthMcardPaymentDetail_query
                          ->select(['previousMonthMobileSale' => $previousMonthMcardPaymentDetail_query->func()->sum('MobileReSalePayments.amount')]);
                $previousMonthMcardPaymentDetail_query = $previousMonthMcardPaymentDetail_query->hydrate(false);
                if(!empty($previousMonthMcardPaymentDetail_query)){
                    $previousMonthMcardPaymentDetail = $previousMonthMcardPaymentDetail_query->first();
                }else{
                    $previousMonthMcardPaymentDetail = array();
                }
				$previousMonthMcardPayment = $previousMonthMcardPaymentDetail['previousMonthMobileSale'];
				if(empty($previousMonthMcardPayment)){
						$previousMonthMcardPayment = 0;
				}
				
				//-------------sourabh------------
                
                $previousMonthMcashPaymentDetail_query = $this->MobileReSalePayments->find('all',[
                              'conditions' =>[("MobileReSalePayments.created BETWEEN '$previousMonthStart' AND '$previousMonthEndPlus'"),
														'MobileReSalePayments.kiosk_id'=>$kiosk_Id,
														'MobileReSalePayments.payment_method' => 'Cash',
														'MobileReSalePayments.amount >=' => 0
                                              ]
                                                                                            ]
                                                                                       );
                $previousMonthMcashPaymentDetail_query
                          ->select(['previousMonthMobileSale' => $previousMonthMcashPaymentDetail_query->func()->sum('MobileReSalePayments.amount')]);
                $previousMonthMcashPaymentDetail_query = $previousMonthMcashPaymentDetail_query->hydrate(false);
                if(!empty($previousMonthMcashPaymentDetail_query)){
                    $previousMonthMcashPaymentDetail = $previousMonthMcashPaymentDetail_query->first();
                }else{
                    $previousMonthMcashPaymentDetail = array();
                }
              
				$previousMonthMcashPayment = $previousMonthMcashPaymentDetail['previousMonthMobileSale'];
				if(empty($previousMonthMcashPayment)){
						$previousMonthMcashPayment = 0;
				}
				//-------------sourabh------------
				
				$currentMonthMobileRefundData_conn = ConnectionManager::get('default');
                $currentMonthMobileRefundData_stmt = $currentMonthMobileRefundData_conn->execute("SELECT `refund_price`, `refund_gain`, 
											SUM(`refund_price`)
											as `currentMonthMobileRefund`,
											SUM(`refund_gain`)
											as `currentMonthMobileRefundGain`
											from `mobile_re_sales`as MobileReSale WHERE (`created` BETWEEN '$monthStart' AND CURDATE() + INTERVAL 1 DAY) AND `refund_status` = 1 AND `kiosk_id` = $kioskId"); 
                $currentMonthMobileRefundData = $currentMonthMobileRefundData_stmt ->fetchAll('assoc');
				//pr($currentMonthMobileRefundData);die;
				$currentMonthMobileRefund = 0;
				if(!empty($currentMonthMobileRefundData[0]['currentMonthMobileRefund'])){
						$currentMonthMobileRefund = $currentMonthMobileRefundData[0]['currentMonthMobileRefund'];
				}
				
				$previousMonthMobileRefundData_conn = ConnectionManager::get('default');
                $previousMonthMobileRefundData_stmt = $previousMonthMobileRefundData_conn->execute("SELECT `refund_price`, `refund_gain`, 
											SUM(`refund_price`)
											as `previousMonthMobileRefund`,
											SUM(`refund_gain`)
											as `previousMonthMobileRefundGain`
											from `mobile_re_sales`as MobileReSale WHERE (`created` BETWEEN '$previousMonthStart' AND '$previousMonthEndPlus') AND `refund_status` = 1 AND `kiosk_id` = $kioskId"); 
                $previousMonthMobileRefundData = $previousMonthMobileRefundData_stmt ->fetchAll('assoc');
				
				$previousMonthMobileRefund = 0;
				if(!empty($previousMonthMobileRefundData[0]['previousMonthMobileRefund'])){
						$previousMonthMobileRefund = $previousMonthMobileRefundData[0]['previousMonthMobileRefund'];
				}
					
				//current month mobile purchase
				if($kioskId == 0){
					$kiosk_Id = 10000;
				}else{
					$kiosk_Id = $kioskId;
				}
				
				$currentMonthMobilePurchaseData_conn = ConnectionManager::get('default');
                $currentMonthMobilePurchaseData_stmt = $currentMonthMobilePurchaseData_conn->execute("SELECT `cost_price`, `topedup_price`,
											case when `topedup_price` is NULL OR `topedup_price` = 0 THEN 
											`cost_price` 
											ELSE 
											`topedup_price` 
											END 
											as `currentMonthMobilePurchase`
											from `mobile_purchases`as MobilePurchase WHERE `created` BETWEEN '$monthStart' AND CURDATE() + INTERVAL 1 DAY AND `purchased_by_kiosk` = $kiosk_Id"); 
                $currentMonthMobilePurchaseData = $currentMonthMobilePurchaseData_stmt ->fetchAll('assoc');
				
				$currentMonthMobilePurchase = 0;
				if(!empty($currentMonthMobilePurchaseData)){
						foreach($currentMonthMobilePurchaseData as $key=>$purchaseData){
					$currentMonthMobilePurchase+= $purchaseData['currentMonthMobilePurchase'];
						}
				}
				$previousMonthMobilePurchaseData_conn = ConnectionManager::get('default');
                $previousMonthMobilePurchaseData_stmt = $previousMonthMobilePurchaseData_conn->execute("SELECT `cost_price`, `topedup_price`,
											case when `topedup_price` is NULL OR `topedup_price` = 0 THEN 
											`cost_price` 
											ELSE 
											`topedup_price` 
											END 
											as `previousMonthMobilePurchase`
											from `mobile_purchases`as MobilePurchase WHERE `created` BETWEEN '$previousMonthStart' AND '$previousMonthEndPlus' AND `purchased_by_kiosk` = $kiosk_Id AND `purchase_status` = 0"); 
                $previousMonthMobilePurchaseData = $previousMonthMobilePurchaseData_stmt ->fetchAll('assoc');
				
				//pr($previousMonthMobilePurchaseData);
                 $previousMonthMobilePurchase = 0;
				if(!empty($previousMonthMobilePurchaseData)){
						foreach($previousMonthMobilePurchaseData as $key=>$lastMonthPurchaseData){
                         
                        $previousMonthMobilePurchase+= $lastMonthPurchaseData['previousMonthMobilePurchase'];
						}
				}else{
                    $previousMonthMobilePurchase = 0;
                }
				
				$this->set(compact('currentMonthMobileSale','currentMonthMobileRefund','currentMonthMobilePurchase','previousMonthMobilePurchase','previousMonthMobileRefund','previousMonthMobileSale','currentMonthMcardPayment','previousMonthMcardPayment','currentMonthMcashPayment','previousMonthMcashPayment'));
    }
    
    private function get_blk_mobile_data($kskId = '', $dte = ''){
        
		if($kskId){
						//for monthly_kiosk_sale page
						$kioskId = $kskId;
				}else{
						$kiosk_id = $this->request->Session()->read('kiosk_id');
						if(!empty($this->request->params['pass']) && array_key_exists(0,$this->request->params['pass'])){//for admin kiosk_total_sale
								$kioskId = $this->request->params['pass'][0];
						}elseif((int)$kiosk_id){
								$kioskId = $kiosk_id;
						}else{
								$kioskId = 0;
						}
				}
				
				if($dte){
						$date = $dte;
				}else{
						$date = date('Y-m-d');
				}
				 
				//get mobile sale
				
				extract($this->todayMobileSale($kioskId,$date));
				extract($this->yesterdayMobileSale($kioskId,$date));
				extract($this->today_refund($kioskId,$date = ""));
				extract($this->yesterday_refund($kioskId));
				//mobile purchase
				$mobilePurchase_conn = ConnectionManager::get('default');
                $mobilePurchase_stmt = $mobilePurchase_conn->execute("SELECT `cost_price`, `topedup_price`,
											case when `topedup_price` is NULL OR `topedup_price` = 0 THEN 
											`cost_price` 
											ELSE 
											`topedup_price` 
											END 
											as `todayMobilePurchase`
											from `mobile_purchases`as MobilePurchase WHERE date(`created`) = '$date' AND `kiosk_id`=$kioskId"); 
                $mobilePurchase = $mobilePurchase_stmt ->fetchAll('assoc');
				//`ADMIN_DOMAIN`.`
				
				$todayBlkMobilePurchase = 0;
				if(!empty($mobilePurchase)){
						foreach($mobilePurchase as $key=>$purchaseData){
								$todayBlkMobilePurchase+= $purchaseData[0]['todayMobilePurchase'];
						}
				}
				
				//we are returning unlock sale, card payment and unlock refund in an array
				if($kskId && $dte){
						$monthlyKioskSaleArr = array('mobileData' => array(
																				'date' => $dte,
																				'mobile_sale' => $todayBlkMobileSale,
																				'card_payment' => $todaysBlkMcardPayment,
																				'mobile_refund' => $todayBlkMobileRefund,
																				'mobile_purchase' => $todayBlkMobilePurchase
																								)
																		 );
						return $monthlyKioskSaleArr;
				}
				
                $yesterdayMobilePurchaseData_conn = ConnectionManager::get('default');
                $yesterdayMobilePurchaseData_stmt = $yesterdayMobilePurchaseData_conn->execute("SELECT `cost_price`, `topedup_price`, 
											case when `topedup_price` is NULL OR `topedup_price` = 0 THEN 
											`cost_price` 
											ELSE 
											`topedup_price` 
											END 
											as `yesterdayMobilePurchase`
											from `mobile_purchases`as MobilePurchase WHERE date(`created`) = CURDATE() - INTERVAL 1 DAY AND `kiosk_id`=$kioskId"); 
                $yesterdayMobilePurchaseData = $yesterdayMobilePurchaseData_stmt ->fetchAll('assoc');
        
				$yesterdayBlkMobilePurchase = 0;
				if(!empty($yesterdayMobilePurchaseData)){
						foreach($yesterdayMobilePurchaseData as $key=>$yesterdayPurchaseData){
					$yesterdayBlkMobilePurchase+= $yesterdayPurchaseData['yesterdayMobilePurchase'];
						}
				}
				
				$this->set(compact('todayBlkMobileSale', 'yesterdayBlkMobileSale','todayBlkMobileRefund', 'yesterdayBlkMobileRefund','todayBlkMobilePurchase','yesterdayBlkMobilePurchase','todaysBlkMcardPayment','yesterdaysBlkMcardPayment','todaysBlkMcashPayment','yesterdaysBlkMcashPayment'));
	}
    
    private function yesterdayMobileSale($kioskId = 0,$date = ""){
		if(empty($date)){
			$date = date('Y-m-d');
		}
		if($kioskId == 0){
					$kioskId = 10000;
			}
		$yesterdayMobileSaleData_query = $this->MobileReSales->find('all', [
								 'conditions' => ['date(MobileReSales.created)' => date('Y-m-d',strtotime('-1 day')),'MobileReSales.refund_status IS NULL','MobileReSales.kiosk_id'=>$kioskId]
                                            ]);
		$yesterdayMobileSaleData_query = $this->MobileBlkReSales->find('all', [ 
                                                                                'conditions' => [
                                                                                                    'date(MobileBlkReSales.created)' => date('Y-m-d',strtotime('-1 day')),
                                                                                                    'MobileBlkReSales.refund_status IS NULL',
                                                                                                    'MobileBlkReSales.kiosk_id'=>$kioskId
                                                                              ]
									]);
        $yesterdayMobileSaleData_query 
               ->select(['yesterdayMobileSale'=>'CASE WHEN MobileBlkReSales.discounted_price is NULL THEN MobileBlkReSales.selling_price ELSE MobileBlkReSales.discounted_price END'])
               ->select('MobileBlkReSales.discounted_price')
               ->select( 'MobileBlkReSales.selling_price');
               //pr($mobileSale_query);die;
        $yesterdayMobileSaleData_query = $yesterdayMobileSaleData_query->hydrate(false);
        if(!empty($yesterdayMobileSaleData_query)){
            $yesterdayMobileSaleData = $yesterdayMobileSaleData_query->toArray();
        }else{
            $yesterdayMobileSaleData = array();
        }
    
			$yesterdayBlkMobileSale = 0;
			if(!empty($yesterdayMobileSaleData)){
					foreach($yesterdayMobileSaleData as $key=>$yesterdayData){
				$yesterdayBlkMobileSale+= $yesterdayData['yesterdayMobileSale'];
					}
			}
			if($kioskId == 0){
					$kioskId = 10000;
			}
            
            $yesterdaysMcardPaymentDetail_query = $this->MobileBlkReSalePayments->find('all',[
                              'conditions' =>['Date(MobileBlkReSalePayments.created)' => date('Y-m-d',strtotime('-1 day')),'MobileBlkReSalePayments.kiosk_id'=>$kioskId,'MobileBlkReSalePayments.payment_method' => 'Card'
                                              ]
                                                                                            ]);
                $yesterdaysMcardPaymentDetail_query
                          ->select(['yesterday_sale' => $yesterdaysMcardPaymentDetail_query->func()->sum('MobileBlkReSalePayments.amount')]);
                $yesterdaysMcardPaymentDetail_query = $yesterdaysMcardPaymentDetail_query->hydrate(false);
                if(!empty($yesterdaysMcardPaymentDetail_query)){
                    $yesterdaysMcardPaymentDetail = $yesterdaysMcardPaymentDetail_query->first();
                }else{
                    $yesterdaysMcardPaymentDetail = array();
                }
    
			$yesterdaysBlkMcardPayment = $yesterdaysMcardPaymentDetail['yesterday_sale'];
			if(empty($yesterdaysMcardPaymentDetail)){
					$yesterdaysBlkMcardPayment = 0;
			}
			if($kioskId == 0){
					$kiosk_Id = 10000;
				}else{
					$kiosk_Id = $kioskId;
				}
                
                $yesterdaysMcashPaymentDetail_query = $this->MobileBlkReSalePayments->find('all',[
                              'conditions' =>['Date(MobileBlkReSalePayments.created)' => date('Y-m-d',strtotime('-1 day')),
														  'MobileBlkReSalePayments.kiosk_id'=>$kiosk_Id,
														  'MobileBlkReSalePayments.payment_method' => 'Cash',
														  'MobileBlkReSalePayments.amount >=' => 0
                                              ]]);
                $yesterdaysMcashPaymentDetail_query
                          ->select(['yesterday_sale' => $yesterdaysMcashPaymentDetail_query->func()->sum('MobileBlkReSalePayments.amount')]);
                $yesterdaysMcashPaymentDetail_query = $yesterdaysMcashPaymentDetail_query->hydrate(false);
                if(!empty($yesterdaysMcashPaymentDetail_query)){
                    $yesterdaysMcashPaymentDetail = $yesterdaysMcashPaymentDetail_query->first();
                }else{
                    $yesterdaysMcashPaymentDetail = array();
                }
                
				$yesterdaysBlkMcashPayment = $yesterdaysMcashPaymentDetail['yesterday_sale'];
				if(empty($yesterdaysBlkMcashPayment)){
						$yesterdaysBlkMcashPayment = 0;
				}
			
			
			
			$this->set(compact('yesterdayBlkMobileSale','yesterdaysBlkMcardPayment','yesterdaysBlkMcashPayment'));
			return array(
				'yesterdayBlkMobileSale' => $yesterdayBlkMobileSale,
				'yesterdaysBlkMcardPayment' => $yesterdaysBlkMcardPayment,
				'yesterdaysBlkMcashPayment' => $yesterdaysBlkMcashPayment,
			);
	}
    
    private function yesterday_refund($kioskId){
        if($kioskId == 0){
			$kioskId = 10000;
		}
        $yesterdayMobileRefundData_query = $this->MobileBlkReSales->find('all',[
                                                                                'conditions' => [
                                                                                                 'date(MobileBlkReSales.created)' =>  date('Y-m-d',strtotime('-1 day')),//CURDATE() - INTERVAL 1 DAY',
                                                                                                 'MobileBlkReSales.refund_status'=>1,
                                                                                                 'MobileBlkReSales.kiosk_id'=>$kioskId
                                                                                                 ]
                                                                                ]
                                                                         );
                $yesterdayMobileRefundData_query
                        ->select('MobileBlkReSales.refund_price')
                        ->select('MobileBlkReSales.refund_gain')
                        ->select(['yesterdayTotalRefund' => $yesterdayMobileRefundData_query->func()->sum('MobileBlkReSales.refund_price')])
                        ->select(['yesterdayTotalRefundGain' => $yesterdayMobileRefundData_query->func()->sum('MobileBlkReSales.refund_gain')]);
        $yesterdayMobileRefundData_query = $yesterdayMobileRefundData_query->hydrate(false);
        if(!empty($yesterdayMobileRefundData_query)){
            $yesterdayMobileRefundData = $yesterdayMobileRefundData_query->first();
        }else{
            $yesterdayMobileRefundData = array();
        }
       if(!empty($yesterdayMobileRefundData['yesterdayTotalRefund'])){
						$yesterdayBlkMobileRefund = $yesterdayMobileRefundData['yesterdayTotalRefund'];
        }else{
             $yesterdayBlkMobileRefund = '';
        }
				
					$this->set(compact('yesterdayBlkMobileRefund'));
				return array(
							 'yesterdayBlkMobileRefund' => $yesterdayBlkMobileRefund,
							 );
				
	}
    
    private function blk_mobile_monthly_data(){
		$kiosk_id = $this->request->Session()->read('kiosk_id');
				if(!empty($this->request->params['pass']) && array_key_exists(0,$this->request->params['pass'])){//for admin kiosk_total_sale
						$kioskId = $this->request->params['pass'][0];
				}elseif((int)$kiosk_id){
						$kioskId = $kiosk_id;
				}else{
						$kioskId = 0;
				}
			
				$monthStart = date('Y-m-1');
				$month_ini = new \DateTime("first day of last month"); //using datetime class of PHP
				$month_end = new \DateTime("last day of last month");
				$previousMonthStart = $month_ini->format('Y-m-d');
				$previousMonthEnd = $month_end->format('Y-m-d');
				$previousMonthEndPlus = date('Y-m-d',strtotime($previousMonthEnd.' +1 day'));//adding one more day to 
				extract($this->currentMonthBlkMobileSale($kioskId));
				extract($this->previousMonthBlkMobileSale($kioskId));
				if($kioskId == 0){
					$k_id = 10000;
				}else{
					$k_id = $kioskId;
				}
	
                $previousMonthMcashPaymentDetail_query = $this->MobileBlkReSalePayments->find('all',[
                              'conditions' =>[("MobileBlkReSalePayments.created BETWEEN '$previousMonthStart' AND '$previousMonthEndPlus'"),
														'MobileBlkReSalePayments.kiosk_id'=>$k_id,
														'MobileBlkReSalePayments.payment_method' => 'Cash',
														'MobileBlkReSalePayments.amount >=' => 0
                                              ]]);
                $previousMonthMcashPaymentDetail_query
                          ->select(['previousMonthMobileSale' => $previousMonthMcashPaymentDetail_query->func()->sum('MobileBlkReSalePayments.amount')]);
                $previousMonthMcashPaymentDetail_query = $previousMonthMcashPaymentDetail_query->hydrate(false);
                if(!empty($previousMonthMcashPaymentDetail_query)){
                    $previousMonthMcashPaymentDetail = $previousMonthMcashPaymentDetail_query->first();
                }else{
                    $previousMonthMcashPaymentDetail = array();
                }
                
                
				$previousMonthBlkMcashPayment = $previousMonthMcashPaymentDetail['previousMonthMobileSale'];
				if(empty($previousMonthMcashPaymentDetail)){
						$previousMonthBlkMcashPayment = 0;
				}
				extract($this->currentMnthRefund($kioskId));
				extract($this->privousMnthRefund($kioskId));
				
                $currentMonthMobilePurchaseData_conn = ConnectionManager::get('default');
                $currentMonthMobilePurchaseData_stmt = $currentMonthMobilePurchaseData_conn->execute("SELECT `cost_price`, `topedup_price`,
											case when `topedup_price` is NULL OR `topedup_price` = 0 THEN 
											`cost_price` 
											ELSE 
											`topedup_price` 
											END 
											as `currentMonthMobilePurchase`
											from `mobile_purchases`as MobilePurchase WHERE `created` BETWEEN '$monthStart' AND CURDATE() + INTERVAL 1 DAY AND `purchased_by_kiosk`=$k_id AND `purchase_status` = 0"); 
                $currentMonthMobilePurchaseData = $currentMonthMobilePurchaseData_stmt ->fetchAll('assoc');
				$currentMonthBlkMobilePurchase = 0;
				if(!empty($currentMonthMobilePurchaseData)){
						foreach($currentMonthMobilePurchaseData as $key=>$purchaseData){
					$currentMonthBlkMobilePurchase+= $purchaseData['currentMonthMobilePurchase'];
						}
				}
				
				$previousMonthMobilePurchaseData_conn = ConnectionManager::get('default');
                $previousMonthMobilePurchaseData_stmt = $previousMonthMobilePurchaseData_conn->execute("SELECT `cost_price`, `topedup_price`,
											case when `topedup_price` is NULL OR `topedup_price` = 0 THEN 
											`cost_price` 
											ELSE 
											`topedup_price` 
											END 
											as `previousMonthMobilePurchase`
											from `mobile_purchases`as MobilePurchase WHERE `created` BETWEEN '$previousMonthStart' AND '$previousMonthEndPlus' AND `kiosk_id` = $kioskId"); 
                $previousMonthMobilePurchaseData = $previousMonthMobilePurchaseData_stmt ->fetchAll('assoc');
				
				$previousMonthBlkMobilePurchase = 0;
				if(!empty($previousMonthMobilePurchaseData)){
					//pr($previousMonthMobilePurchaseData);die;
						foreach($previousMonthMobilePurchaseData as $key=>$lastMonthPurchaseData){
					$previousMonthBlkMobilePurchase+= $lastMonthPurchaseData['previousMonthMobilePurchase'];
						}
				}
				
				$this->set(compact('currentMonthBlkMobileSale','currentMonthBlkMobileRefund','currentMonthBlkMobilePurchase','previousMonthBlkMobilePurchase','previousMonthBlkMobileRefund','previousMonthBlkMobileSale','currentMonthBlkMcardPayment','previousMonthBlkMcardPayment','currentMonthBlkMcashPayment','previousMonthBlkMcashPayment'));
	}
    
    private function currentMonthBlkMobileSale($kioskId = 0){
		$monthStart = date('Y-m-1');
		$month_ini = new \DateTime("first day of last month"); //using datetime class of PHP
		$month_end = new \DateTime("last day of last month");
		if($kioskId == 0){
			$kiosk_Id = 10000;
		}else{
			$kiosk_Id = $kioskId;
		}
        $conn = ConnectionManager::get('default');
        $stmt = $conn->execute("SELECT `discounted_price`, `selling_price`, 
											case when `discounted_price` is NULL OR `discounted_price` = 0 THEN 
											`selling_price` 
											ELSE 
											`discounted_price` 
											END 
											as `currentMonthMobileSale`
											from `mobile_blk_re_sales`as MobileReSale WHERE (`created` BETWEEN '$monthStart' AND CURDATE() + INTERVAL 1 DAY) AND `refund_status` is NULL AND `kiosk_id` = $kiosk_Id"); 
        $currentMonthMobileSaleData = $stmt ->fetchAll('assoc');
		
		$currentMonthBlkMobileSale = 0;
		if(!empty($currentMonthMobileSaleData)){
				foreach($currentMonthMobileSaleData as $key=>$data){
			$currentMonthBlkMobileSale+= $data['currentMonthMobileSale'];
				}  
		}
		
		$currentDate = date('Y-m-d',strtotime(' +1 day'));
        
        $currentMonthMcardPaymentDetail_query = $this->MobileBlkReSalePayments->find('all',[
                              'conditions' =>[("MobileBlkReSalePayments.created BETWEEN '$monthStart' AND '$currentDate'"),
													'MobileBlkReSalePayments.kiosk_id'=>$kiosk_Id,'MobileBlkReSalePayments.payment_method' => 'Card'
                                              ]]);
        $currentMonthMcardPaymentDetail_query
                  ->select(['currentMonthMobileSale' => $currentMonthMcardPaymentDetail_query->func()->sum('MobileBlkReSalePayments.amount')]);
        $currentMonthMcardPaymentDetail_query = $currentMonthMcardPaymentDetail_query->hydrate(false);
        if(!empty($currentMonthMcardPaymentDetail_query)){
            $currentMonthMcardPaymentDetail = $currentMonthMcardPaymentDetail_query->first();
        }else{
            $currentMonthMcardPaymentDetail = array();
        }
        
			$currentMonthBlkMcardPayment = $currentMonthMcardPaymentDetail['currentMonthMobileSale'];
			if(empty($currentMonthMcardPaymentDetail)){
					$currentMonthBlkMcardPayment = 0;
			}
	
            $currentMonthMcashPaymentDetail_query = $this->MobileBlkReSalePayments->find('all',[
                              'conditions' =>[("MobileBlkReSalePayments.created BETWEEN '$monthStart' AND '$currentDate'"),
														'MobileBlkReSalePayments.kiosk_id'=>$kiosk_Id,
														'MobileBlkReSalePayments.payment_method' => 'Cash',
														'MobileBlkReSalePayments.amount >=' => 0
                                              ]]);
            $currentMonthMcashPaymentDetail_query
                      ->select(['currentMonthMobileSale' => $currentMonthMcashPaymentDetail_query->func()->sum('MobileBlkReSalePayments.amount')]);
            $currentMonthMcashPaymentDetail_query = $currentMonthMcashPaymentDetail_query->hydrate(false);
            if(!empty($currentMonthMcashPaymentDetail_query)){
                $currentMonthMcashPaymentDetail = $currentMonthMcashPaymentDetail_query->first();
            }else{
                $currentMonthMcashPaymentDetail = array();
            }
            
            
			$currentMonthBlkMcashPayment = $currentMonthMcashPaymentDetail['currentMonthMobileSale'];
			if(empty($currentMonthMcashPaymentDetail)){
					$currentMonthBlkMcashPayment = 0;
			}
			$this->set(compact('currentMonthBlkMobileSale','currentMonthBlkMcardPayment','currentMonthBlkMcashPayment'));
		return array(
			  'currentMonthBlkMobileSale' => $currentMonthBlkMobileSale,
			  'currentMonthBlkMcardPayment' => $currentMonthBlkMcardPayment,
			  'currentMonthBlkMcashPayment' => $currentMonthBlkMcashPayment,
			  );
	}
    
    private function previousMonthBlkMobileSale($kioskId = 0){
		$monthStart = date('Y-m-1');
		$month_ini = new \DateTime("first day of last month"); //using datetime class of PHP
		$month_end = new \DateTime("last day of last month");
		$previousMonthStart = $month_ini->format('Y-m-d');
		$previousMonthEnd = $month_end->format('Y-m-d');
		$previousMonthEndPlus = date('Y-m-d',strtotime($previousMonthEnd.' +1 day'));//adding one more day to last month to get correct
		if($kioskId == 0){
			$kiosk_Id = 10000;
		}else{
			$kiosk_Id = $kioskId;
		}
		$conn = ConnectionManager::get('default');
        $stmt = $conn->execute("SELECT `discounted_price`, `selling_price`, 
											case when `discounted_price` is NULL OR `discounted_price` = 0 THEN 
												`selling_price` 
											ELSE 
												`discounted_price` 
											END 
												as `previousMonthMobileSale`
											from `mobile_blk_re_sales`as MobileReSale WHERE (`created` BETWEEN '$previousMonthStart' AND '$previousMonthEndPlus') AND `refund_status` is NULL AND `kiosk_id` = $kiosk_Id"); 
        $previousMonthBlkMobileSaleData = $stmt ->fetchAll('assoc');
											
		$previousMonthBlkMobileSale = 0;
		if(!empty($previousMonthBlkMobileSaleData)){
			foreach($previousMonthBlkMobileSaleData as $key=>$data){
				$previousMonthBlkMobileSale+= $data['previousMonthMobileSale'];
			}  
		}
		
		
        $previousMonthMcardPaymentDetail_query = $this->MobileBlkReSalePayments->find('all',[
                              'conditions' =>[("MobileBlkReSalePayments.created BETWEEN '$previousMonthStart' AND '$previousMonthEndPlus'"),
										'MobileBlkReSalePayments.kiosk_id'=>$kiosk_Id,'MobileBlkReSalePayments.payment_method' => 'Card'
                                              ]]);
        $previousMonthMcardPaymentDetail_query
                  ->select(['previousMonthMobileSale' => $previousMonthMcardPaymentDetail_query->func()->sum('MobileBlkReSalePayments.amount')]);
        $previousMonthMcardPaymentDetail_query = $previousMonthMcardPaymentDetail_query->hydrate(false);
        if(!empty($previousMonthMcardPaymentDetail_query)){
            $previousMonthMcardPaymentDetail = $previousMonthMcardPaymentDetail_query->first();
        }else{
            $previousMonthMcardPaymentDetail = array();
        }
        
		if(empty($previousMonthMcardPaymentDetail)){
			$previousMonthBlkMcardPayment = 0;
		}
		$previousMonthBlkMcardPayment = $previousMonthMcardPaymentDetail['previousMonthMobileSale'];
		
		if($kioskId == 0){
					$k_id = 10000;
				}else{
					$k_id = $kioskId;
				}
                
                $previousMonthMcashPaymentDetail_query = $this->MobileBlkReSalePayments->find('all',[
                              'conditions' =>[("MobileBlkReSalePayments.created BETWEEN '$previousMonthStart' AND '$previousMonthEndPlus'"),
														'MobileBlkReSalePayments.kiosk_id'=>$k_id,
														'MobileBlkReSalePayments.payment_method' => 'Cash',
														'MobileBlkReSalePayments.amount >=' => 0
                                              ]]);
                $previousMonthMcashPaymentDetail_query
                          ->select(['previousMonthMobileSale' => $previousMonthMcashPaymentDetail_query->func()->sum('MobileBlkReSalePayments.amount')]);
                $previousMonthMcashPaymentDetail_query = $previousMonthMcashPaymentDetail_query->hydrate(false);
                if(!empty($previousMonthMcashPaymentDetail_query)){
                    $previousMonthMcashPaymentDetail = $previousMonthMcashPaymentDetail_query->first();
                }else{
                    $previousMonthMcashPaymentDetail = array();
                }
                
				$previousMonthBlkMcashPayment = $previousMonthMcashPaymentDetail['previousMonthMobileSale'];
				if(empty($previousMonthMcashPaymentDetail)){
						$previousMonthBlkMcashPayment = 0;
				}
		
		
		$this->set(compact('previousMonthBlkMobileSale','previousMonthBlkMcardPayment','previousMonthBlkMcashPayment'));
		return array(
			  'previousMonthBlkMobileSale' => $previousMonthBlkMobileSale,
			  'previousMonthBlkMcardPayment' => $previousMonthBlkMcardPayment,
			  'previousMonthBlkMcashPayment' => $previousMonthBlkMcashPayment
			  );
	}
    
    private function currentMnthRefund($kioskId = 0){
		if($kioskId == 0){
			$kioskId = 10000;
		}
		$month_ini = new \DateTime("first day of last month"); //using datetime class of PHP
		$month_end = new \DateTime("last day of last month");
		$monthStart = date('Y-m-1');
        $conn = ConnectionManager::get('default');
        $stmt = $conn->execute("SELECT `refund_price`, `refund_gain`, 
											SUM(`refund_price`)
											as `currentMonthMobileRefund`,
											SUM(`refund_gain`)
											as `currentMonthMobileRefundGain`
											from `mobile_blk_re_sales`as MobileReSale WHERE (`created` BETWEEN '$monthStart' AND CURDATE() + INTERVAL 1 DAY) AND `refund_status` = 1 AND `kiosk_id` = $kioskId"); 
        $currentMonthMobileRefundData = $stmt ->fetchAll('assoc');
				//`ADMIN_DOMAIN`.
				//pr($currentMonthMobileRefundData);
				
				if(!empty($currentMonthMobileRefundData[0]['currentMonthMobileRefund'])){
						$currentMonthBlkMobileRefund = $currentMonthMobileRefundData[0]['currentMonthMobileRefund'];
				}else{
                    $currentMonthBlkMobileRefund = 0;
                }
				
				$this->set(compact('currentMonthBlkMobileRefund'));
				return array(
							 'currentMonthBlkMobileRefund' => $currentMonthBlkMobileRefund,
							 );
	}
    
    private function privousMnthRefund($kioskId = 0){
		if($kioskId == 0){
			$kioskId = 10000;
		}
		$month_ini = new \DateTime("first day of last month"); //using datetime class of PHP
		$month_end = new \DateTime("last day of last month");
		$previousMonthStart = $month_ini->format('Y-m-d');
		$previousMonthEnd = $month_end->format('Y-m-d');
		$previousMonthEndPlus = date('Y-m-d',strtotime($previousMonthEnd.' +1 day'));
        $conn = ConnectionManager::get('default');
        $stmt = $conn->execute("SELECT `refund_price`, `refund_gain`, 
											SUM(`refund_price`)
											as `previousMonthMobileRefund`,
											SUM(`refund_gain`)
											as `previousMonthMobileRefundGain`
											from `mobile_blk_re_sales`as MobileReSale WHERE (`created` BETWEEN '$previousMonthStart' AND '$previousMonthEndPlus') AND `refund_status` = 1 AND `kiosk_id` = $kioskId"); 
        $previousMonthMobileRefundData = $stmt ->fetchAll('assoc');
				//`ADMIN_DOMAIN`.
				
				
				if(!empty($previousMonthMobileRefundData[0]['previousMonthMobileRefund'])){
						$previousMonthBlkMobileRefund = $previousMonthMobileRefundData[0]['previousMonthMobileRefund'];
				}else{
                    $previousMonthBlkMobileRefund = 0;
                }
				
				$this->set(compact('previousMonthBlkMobileRefund'));
				return array(
							 'previousMonthBlkMobileRefund' => $previousMonthBlkMobileRefund,
							 );
	}
    private function monthly_product_sale(){
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		if(!empty($this->request->params['pass']) && array_key_exists(0,$this->request->params['pass'])){
			$kioskId = $this->request->params['pass'][0];
		}elseif((int)$kiosk_id){
			$kioskId = $kiosk_id;
		}else{
			$kioskId = 0;
		}
		
		if($kioskId == 0){
			$saleSource = "kiosk_product_sales";
			$productReceiptSource = "product_receipts";
			$paymentDetailSource = "payment_details";
		}else{
			$saleSource = "kiosk_{$kioskId}_product_sales";
			$productReceiptSource = "kiosk_{$kioskId}_product_receipts";
			$paymentDetailSource = "kiosk_{$kioskId}_payment_details";
		}
		
         $PaymentDetailsTable = TableRegistry::get($paymentDetailSource,[
                                                                    'table' => $paymentDetailSource,
                                                                ]);
         $reciptTable = TableRegistry::get($productReceiptSource,[
                                                                    'table' => $productReceiptSource,
                                                                ]);
          $saleTable = TableRegistry::get($saleSource,[
                                                                    'table' => $saleSource,
                                                                ]);
        
        		
		$currentDate = date('Y-m-d',strtotime(' +1 day'));//adding an extra day to get correct result of current month
		$monthStart = date('Y-m-1');
		$month_ini = new \DateTime("first day of last month"); //using datetime class of PHP
		$month_end = new \DateTime("last day of last month");
		$previousMonthStart = $month_ini->format('Y-m-d');
		$previousMonthEnd = $month_end->format('Y-m-d');
		$previousMonthEndPlus = date('Y-m-d',strtotime($previousMonthEnd.' +1 day'));
		//adding one more day to last month to get correct data from db for last month
		$current_month_credit_to_other_changed = $this->custom_month_credit_to_card($monthStart,$currentDate,$kioskId,$source=1);
		$prv_month_credit_to_other_changed = $this->custom_month_credit_to_card($previousMonthStart,$previousMonthEndPlus,$kioskId,$source=1);
		$this->set(compact('current_month_credit_to_other_changed','prv_month_credit_to_other_changed'));
		$receiptIdArr_query = $saleTable->find('list',[
                                                                        'keyField' => 'id',
                                                                        'valueField' => 'product_receipt_id',
                                                                        'conditions' => ["created BETWEEN '$monthStart' AND '$currentDate'",
                                                                                'refund_status' => 0,
                                                                                ],
                                                                    ]);
        //pr($receiptIdArr_query);
        $receiptIdArr_query = $receiptIdArr_query->hydrate(false);
        if(!empty($receiptIdArr_query)){
            $receiptIdArr = $receiptIdArr_query->toArray();
        }else{
            $receiptIdArr = array();
        }
		$yreceiptIdArr = $saleTable->find('list',[
                                                                    'keyField' => 'id',
                                                                    'valueField' => 'product_receipt_id',
                                                                    'conditions' => ["created BETWEEN '$previousMonthStart' AND '$previousMonthEndPlus'",
                                                                    'refund_status' => 0,
                                                                                    ],
                                                                ]);
        
        $yreceiptIdArr = $yreceiptIdArr->hydrate(false);
        if(!empty($yreceiptIdArr)){
            $yreceiptIdArr = $yreceiptIdArr->toArray();
        }else{
            $yreceiptIdArr = array();
        }
		//pr($receiptIdArr);die;
        if(empty($receiptIdArr)){
            $receiptIdArr = array(0 => null);
        }
        $cashDetailsOfMnth_query = $this->ProductPayments->find('all',[
                              'conditions' =>[("ProductPayments.created BETWEEN '$monthStart' AND '$currentDate'"),
																			 '`ProductPayments`.`payment_method`' => 'Cash',
																			 '`ProductPayments`.`amount` >= ' => 0,
																			  '`ProductPayments`.`kiosk_id`' => $kioskId,
														'`ProductPayments`.`product_receipt_id` IN' => $receiptIdArr
                                              ]]);
        $cashDetailsOfMnth_query
                  ->select(['today_sale' => $cashDetailsOfMnth_query->func()->sum('ProductPayments.amount')]);
        //pr($cashDetailsOfMnth_query);die;
        $cashDetailsOfMnth_query = $cashDetailsOfMnth_query->hydrate(false);
        
        if(!empty($cashDetailsOfMnth_query)){
            $cashDetailsOfMnth = $cashDetailsOfMnth_query->first();
        }else{
            $cashDetailsOfMnth = array();
        }
		//pr($cashDetailsOfMnth);die;
			 $thisMnthCash = $cashDetailsOfMnth['today_sale'];
             if(empty($yreceiptIdArr)){
                $yreceiptIdArr = array(0 => null);
             }
            $cashDetailsOfprvMnth_query = $this->ProductPayments->find('all',[
                                  'conditions' =>[("ProductPayments.created BETWEEN '$previousMonthStart' AND '$previousMonthEnd'"),
                                                                                  '`ProductPayments`.`payment_method`' => 'Cash',
                                                                                  '`ProductPayments`.`amount` >= ' => 0,
                                                                                  '`ProductPayments`.`kiosk_id`' => $kioskId,
                                                                                  '`ProductPayments`.`product_receipt_id` IN' => $yreceiptIdArr
                                                  ]]);
            $cashDetailsOfprvMnth_query
                      ->select(['today_sale' => $cashDetailsOfprvMnth_query->func()->sum('ProductPayments.amount')]);
            //pr($cashDetailsOfMnth_query);die;
            $cashDetailsOfprvMnth_query = $cashDetailsOfprvMnth_query->hydrate(false);
            
            if(!empty($cashDetailsOfprvMnth_query)){
                $cashDetailsOfprvMnth = $cashDetailsOfprvMnth_query->first();
            }else{
                $cashDetailsOfprvMnth = array();
            }
           
			 $prvMnthCash = $cashDetailsOfprvMnth['today_sale'];
		//----------------------------------------
		$currentMonthProductPmtDetails = array(
										'credit' => 0,
										'cash' => 0,
										'bank_transfer' => 0,
										'cheque' => 0,
										'card' => 0,
										'misc' => 0,
										);
		//$this->PaymentDetails->setSource($paymentDetailSource);
        
        $PaymentDetails_source = $paymentDetailSource;
        $PaymentDetailsTable = TableRegistry::get($PaymentDetails_source,[
                                                                    'table' => $PaymentDetails_source,
                                                                ]);
        
		$paymentDetailQuery = "";
		if(count($receiptIdArr)){
            if(empty($receiptIdArr)){
                $receiptIdArr = array(0 => null);
            }
			$paymentDetails_query = $PaymentDetailsTable->find('all', array(
														'conditions' => array('product_receipt_id IN' => $receiptIdArr),
														'recursive' => -1,
													));
            $paymentDetails_query = $paymentDetails_query->hydrate(false);
            if(!empty($paymentDetails_query)){
                $paymentDetails = $paymentDetails_query->toArray();
            }else{
                $paymentDetails = array();
            }
			//pr($paymentDetails);die;
			$currentMonthProductPmtDetails = $this->getPaymentDetailSale($paymentDetails,$reciptTable);
			//pr($currentMonthProductPmtDetails);
		}
		$bulkDiscountArr_query = $reciptTable->find('list',[
																	'keyField' => 'id',
                                                                    'valueField' => 'bulk_discount',
																	'conditions' => ['id IN' => $receiptIdArr]
                                                                ]);
        $bulkDiscountArr_query = $bulkDiscountArr_query->hydrate(false);
        if(!empty($bulkDiscountArr_query)){
            $bulkDiscountArr = $bulkDiscountArr_query->toArray();
        }else{
            $bulkDiscountArr = array();
        }
		
		$vatArr_query = $reciptTable->find('list',[
                                                            'keyField' => 'id',
                                                            'valueField' => 'vat',
                                                            'conditions' => ['id IN' => $receiptIdArr],
                                                            //'recursive' => -1
                                                      ]);
		$vatArr_query = $vatArr_query->hydrate(false);
        if(!empty($vatArr_query)){
            $vatArr = $vatArr_query->toArray();
        }else{
            $vatArr = array();
        }
		$saleIds = array_keys($receiptIdArr);
		$productSaleArr = array();
				
		if(!empty($saleIds)){
			$productSaleArr_query = $saleTable->find('all',array(
																		'fields' => array('product_id',
																						  'sale_price',
																						  'discount',
																						  'product_receipt_id',
																						  'quantity'),
																		'conditions' => array('id IN' => $saleIds,
																							'refund_status' => 0),
																		)
															);
            $productSaleArr_query = $productSaleArr_query->hydrate(false);
            if(!empty($productSaleArr_query)){
                $productSaleArr = $productSaleArr_query->toArray();
            }else{
                $productSaleArr = array();
            }
			$productRefundArr_query = $saleTable->find('all',array(
																		'fields' => array('product_id',
																						  'sale_price',
																						  'discount',
																						  'product_receipt_id',
																						  'quantity'),
																		'conditions' => array('id IN' => $saleIds,
																							'refund_status IN' => array(1,2)),
																		)
															  );
            $productRefundArr_query = $productRefundArr_query->hydrate(false);
            if(!empty($productRefundArr_query)){
                $productRefundArr = $productRefundArr_query->toArray();
            }else{
                $productRefundArr = array();
            }
		}
				
		$newProductRefundArr = array();
		if(!empty($productRefundArr)){
			foreach($productRefundArr as $key => $newProductRefundInfo){
				$refProductId = $newProductRefundInfo['product_id'];
				$refReceiptId = $newProductRefundInfo['product_receipt_id'];
				$refKey = "$refProductId|$refReceiptId";
				//$newProductRefundArr[$refKey] = $newProductRefundInfo['KioskProductSale']['quantity'];
				if(array_key_exists($refKey,$newProductRefundArr)){
						$newProductRefundArr[$refKey]+=$newProductRefundInfo['quantity'];
				}else{
						$newProductRefundArr[$refKey] = $newProductRefundInfo['quantity'];
				}
			}
		}
				
		$totalSaleArr = array();
		$newCurrMonQuantity = array();
		if(!empty($productSaleArr)){
			foreach($productSaleArr as $key=>$productSaleData){
				$salePrice = $productSaleData['sale_price'];
				$discount = $productSaleData['discount'];
				$quantity = $productSaleData['quantity'];
				$productId = $productSaleData['product_id'];
				$receiptId = $productSaleData['product_receipt_id'];
				$combKey = "$productId|$receiptId";
						
				
					
				if(array_key_exists($combKey,$newProductRefundArr)){
					$newProductRefundArr[$combKey]+=$quantity;
				}else{
					$newProductRefundArr[$combKey] = $quantity;
				}
				
				if(array_key_exists($productSaleData['product_receipt_id'],$bulkDiscountArr)){	
					if($bulkDiscountArr[$productSaleData['product_receipt_id']]>0){
						$bulkDiscount = $bulkDiscountArr[$productSaleData['product_receipt_id']];
					}else{
						$bulkDiscount = 0;
					}
				}
				
				$totalSaleArr[] = ($salePrice-($salePrice*$discount/100+$salePrice*$bulkDiscount/100))*$newProductRefundArr[$combKey];
				
			}
		}
				
		$currentMonthProductSale = 0;
		foreach($totalSaleArr as $key=>$monthSale){
				$currentMonthProductSale+=$monthSale;
		}
       // pr($receiptIdArr);
        $Mres_query = $reciptTable->find('all',['conditions' =>['id IN' => $receiptIdArr]]);
                $Mres_query
                          ->select(['total_sale' => $Mres_query->func()->sum('orig_bill_amount')]);
		$Mres_query = $Mres_query->hydrate(false);
        if(!empty($Mres_query)){
            $Mres = $Mres_query->first();
        }else{
            $Mres = array();
        }
		$currentMonthProductSale = $Mres['total_sale'];
		
		
		$previousMonthProductPmtDetails = array(
										'credit' => 0,
										'cash' => 0,
										'bank_transfer' => 0,
										'cheque' => 0,
										'card' => 0,
										'misc' => 0,
										);
		$PaymentDetails_source = $paymentDetailSource;
        $PaymentDetailsTable = TableRegistry::get($PaymentDetails_source,[
                                                                    'table' => $PaymentDetails_source,
                                                                ]);
		$paymentDetailQuery = "";
        
		if(count($receiptIdArr)){
            if(empty($yreceiptIdArr)){
                $yreceiptIdArr = array(0 => null);
            }
			$paymentDetails_query = $PaymentDetailsTable->find('all', array(
														'conditions' => array('product_receipt_id IN' => $yreceiptIdArr),
													));
            $paymentDetails_query = $paymentDetails_query->hydrate(false);
            if(!empty($paymentDetails_query)){
                $paymentDetails = $paymentDetails_query->toArray();
            }else{
                $paymentDetails = array();
            }
           // pr($paymentDetails);die;
			$previousMonthProductPmtDetails = $this->getPaymentDetailSale($paymentDetails,$reciptTable);
			
			
		}
		$ybulkDiscountArr_query = $reciptTable->find('list',[
                                                                    'keyField' => 'id',
                                                                    'valueField' => 'bulk_discount',
                                                                    'conditions' => ['id IN'=>$yreceiptIdArr],
                                                                ]
														);
        $ybulkDiscountArr_query = $ybulkDiscountArr_query->hydrate(false);
        if(!empty($ybulkDiscountArr_query)){
            $ybulkDiscountArr = $ybulkDiscountArr_query->toArray();
        }else{
            $ybulkDiscountArr = array();
        }
		
		$yVatArr_query = $reciptTable->find('list',[
                                                            'keyField' => 'id',
                                                            'valueField' => 'vat',
                                                            'conditions' => ['id IN'=>$yreceiptIdArr],
                                                            //'recursive' => -1
                                                       ]
												);
        $yVatArr_query = $yVatArr_query->hydrate(false);
        if(!empty($yVatArr_query)){
            $yVatArr = $yVatArr_query->toArray();
        }else{
            $yVatArr = array();
        }
		$ysaleIds = array_keys($yreceiptIdArr);
		$yproductSaleArr = array();
				
		if(!empty($ysaleIds)){
			$yproductSaleArr_query = $saleTable->find('all',array(
																		'fields' => array('product_id',
																						  'sale_price',
																						  'discount',
																						  'product_receipt_id',
																						  'quantity'),
																		'conditions' => array('id IN' => $ysaleIds,
																							'refund_status' => 0),
															));
            $yproductSaleArr_query = $yproductSaleArr_query->hydrate(false);
            if(!empty($yproductSaleArr_query)){
                $yproductSaleArr = $yproductSaleArr_query->toArray();
            }else{
                $yproductSaleArr = array();
            }
			
			$yproductRefundArr_query = $saleTable->find('all',array(
																		'fields' => array('product_id',
																						  'sale_price',
																						  'discount',
																						  'product_receipt_id',
																						  'quantity'),
																		'conditions' => array('id IN' => $ysaleIds,
																							'refund_status IN' => array(1,2)),
															));
            $yproductRefundArr_query = $yproductRefundArr_query->hydrate(false);
            if(!empty($yproductRefundArr_query)){
                $yproductRefundArr = $yproductRefundArr_query->toArray();
            }else{
                $yproductRefundArr = array();
            }
		}
				
		$newYproductRefundArr = array();
		if(!empty($yproductRefundArr)){
				foreach($yproductRefundArr as $yproductRefundInfo){
					$yRefProductId = $yproductRefundInfo['product_id'];
					$yRefReceiptId = $yproductRefundInfo['product_receipt_id'];
					$yRefKey = "$yRefProductId|$yRefReceiptId";
					//$newYproductRefundArr[$yRefKey] = $yproductRefundInfo['KioskProductSale']['quantity'];
					if(array_key_exists($yRefKey,$newYproductRefundArr)){
						$newYproductRefundArr[$yRefKey]+=$yproductRefundInfo['quantity'];
					}else{
						$newYproductRefundArr[$yRefKey] = $yproductRefundInfo['quantity'];
					}
				}
		}
				
		$ytotalSaleArr = array();
		$yQauntityArr = array();
		if(!empty($yproductSaleArr)){
			foreach($yproductSaleArr as $key=>$yproductSaleData){
				//pr($yproductSaleData);
				$yProductId = $yproductSaleData['product_id'];
				$yReceiptId = $yproductSaleData['product_receipt_id'];
				$ysalePrice = $yproductSaleData['sale_price'];
				$ydiscount = $yproductSaleData['discount'];
				$quantity = $yproductSaleData['quantity'];
				$ykey = "$yProductId|$yReceiptId";
				
				
				if(array_key_exists($ykey,$newYproductRefundArr)){
					$newYproductRefundArr[$ykey]+=$quantity;
				}else{
					$newYproductRefundArr[$ykey] = $quantity;
				}
				if(array_key_exists($yproductSaleData['product_receipt_id'],$ybulkDiscountArr)){	
					if($ybulkDiscountArr[$yproductSaleData['product_receipt_id']]>0){
						$ybulkDiscount = $ybulkDiscountArr[$yproductSaleData['product_receipt_id']];
					}else{
						$ybulkDiscount = 0;
					}
				}
				
				$ytotalSaleArr[] = ($ysalePrice-($ysalePrice*$ydiscount/100+$ysalePrice*$ybulkDiscount/100))*$newYproductRefundArr[$ykey];
			}
		}
				
		$previousMonthProductSale = 0;
		foreach($ytotalSaleArr as $key=>$ySale){
			$previousMonthProductSale+=$ySale;
		}
		
        $Pres_query = $reciptTable->find('all',['conditions' =>['id IN' => $yreceiptIdArr]]);
                $Pres_query
                          ->select(['total_sale' => $Pres_query->func()->sum('orig_bill_amount')]);
		$Pres_query = $Pres_query->hydrate(false);
        if(!empty($Pres_query)){
            $Pres = $Pres_query->first();
        }else{
            $Pres = array();
        }
        
		$previousMonthProductSale = $Pres['total_sale'];		
		
        $currentMonthPcardPaymentDetail_query = $this->ProductPayments->find('all',['conditions' =>[("ProductPayments.created BETWEEN '$monthStart' AND '$currentDate'"),
												'ProductPayments.kiosk_id'=>$kioskId,'ProductPayments.payment_method' => 'Card','ProductPayments.product_receipt_id IN' => $receiptIdArr]]);
                $currentMonthPcardPaymentDetail_query
                          ->select(['currentMonthProductSale' => $currentMonthPcardPaymentDetail_query->func()->sum('ProductPayments.amount')]);
		$currentMonthPcardPaymentDetail_query = $currentMonthPcardPaymentDetail_query->hydrate(false);
        if(!empty($currentMonthPcardPaymentDetail_query)){
            $currentMonthPcardPaymentDetail = $currentMonthPcardPaymentDetail_query->first();
        }else{
            $currentMonthPcardPaymentDetail = array();
        }
        
		$currentMonthPcardPayment = $currentMonthPcardPaymentDetail['currentMonthProductSale'];
		if(empty($currentMonthPcardPayment)){
			$currentMonthPcardPayment = 0;
		}
	    if(empty($yreceiptIdArr)){
            $yreceiptIdArr = array(0 => null);
        }
        $previousMonthPcardPaymentDetail_query = $this->ProductPayments->find('all',['conditions' =>[("ProductPayments.created BETWEEN '$previousMonthStart' AND '$previousMonthEndPlus'"),
												'ProductPayments.kiosk_id'=>$kioskId,'ProductPayments.payment_method' => 'Card',
												'`ProductPayments`.`product_receipt_id` IN' => $yreceiptIdArr]]);
                $previousMonthPcardPaymentDetail_query
                          ->select(['previousMonthProductSale' => $previousMonthPcardPaymentDetail_query->func()->sum('ProductPayments.amount')]);
		$previousMonthPcardPaymentDetail_query = $previousMonthPcardPaymentDetail_query->hydrate(false);
        if(!empty($previousMonthPcardPaymentDetail_query)){
            $previousMonthPcardPaymentDetail = $previousMonthPcardPaymentDetail_query->first();
        }else{
            $previousMonthPcardPaymentDetail = array();
        }
        
		$previousMonthPcardPayment = $previousMonthPcardPaymentDetail['previousMonthProductSale'];
		if(empty($previousMonthPcardPayment)){
			$previousMonthPcardPayment = 0;
		}
		
        $currentMonthProductRefundData_query = $saleTable->find('all',['conditions' =>["created BETWEEN '$monthStart' AND '$currentDate'"]]);
        $currentMonthProductRefundData_query
                          ->select(['currentMonthProductRefund' => $currentMonthProductRefundData_query->func()->sum('refund_price*quantity')]);
		$currentMonthProductRefundData_query = $currentMonthProductRefundData_query->hydrate(false);
        if(!empty($currentMonthProductRefundData_query)){
            $currentMonthProductRefundData = $currentMonthProductRefundData_query->first();
        }else{
            $currentMonthProductRefundData = array();
        }
				
		$currentMonthProductRefund = $currentMonthProductRefundData['currentMonthProductRefund'];
	
        $previousMonthProductRefundData_query = $saleTable->find('all',['conditions' =>["created BETWEEN '$previousMonthStart' AND '$previousMonthEndPlus'"]]);
        $previousMonthProductRefundData_query
                          ->select(['previousMonthProductRefund' => $previousMonthProductRefundData_query->func()->sum('refund_price*quantity')]);
		$previousMonthProductRefundData_query = $previousMonthProductRefundData_query->hydrate(false);
        if(!empty($previousMonthProductRefundData_query)){
            $previousMonthProductRefundData = $previousMonthProductRefundData_query->first();
        }else{
            $previousMonthProductRefundData = array();
        }
        
		$previousMonthProductRefund = $previousMonthProductRefundData['previousMonthProductRefund'];
		
		$this->set(compact('currentMonthProductSale','previousMonthProductSale','currentMonthProductRefund','previousMonthProductRefund','currentMonthPcardPayment','previousMonthPcardPayment','currentMonthProductPmtDetails','previousMonthProductPmtDetails','thisMnthCash','prvMnthCash'));	
    }
    
    private function monthly_unlock_sale(){
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		if(!empty($this->request->params['pass']) && array_key_exists(0,$this->request->params['pass'])){//for admin kiosk_total_sale
			$kioskId = $this->request->params['pass'][0];
		}elseif((int)$kiosk_id){
			$kioskId = $kiosk_id;
		}else{
			$kioskId = 0;
		}
		
		$currentDate = date('Y-m-d',strtotime(' +1 day'));//adding an extra day to get correct result of current month
		$monthStart = date('Y-m-1');
		$month_ini = new \DateTime("first day of last month"); //using datetime class of PHP
		$month_end = new \DateTime("last day of last month");
		$previousMonthStart = $month_ini->format('Y-m-d');
		$previousMonthEnd = $month_end->format('Y-m-d');
		$previousMonthEndPlus = date('Y-m-d',strtotime($previousMonthEnd.' +1 day'));//adding one more day to last month to get correct data from db for last month
		
        $currentMonthUnlockSaleData_query = $this->MobileUnlockSales->find('all',['conditions' =>[("MobileUnlockSales.created BETWEEN '$monthStart' AND '$currentDate'"),
												  'MobileUnlockSales.kiosk_id'=>$kioskId]]);
        $currentMonthUnlockSaleData_query
                          ->select(['currentMonthSale' => $currentMonthUnlockSaleData_query->func()->sum('MobileUnlockSales.amount')]);
		$currentMonthUnlockSaleData_query = $currentMonthUnlockSaleData_query->hydrate(false);
        if(!empty($currentMonthUnlockSaleData_query)){
            $currentMonthUnlockSaleData = $currentMonthUnlockSaleData_query->first();
        }else{
            $currentMonthUnlockSaleData = array();
        }
		
		$currentMonthUnlockSale = $currentMonthUnlockSaleData['currentMonthSale'];
        
        $currentMonthUcardPaymentDetail_query = $this->UnlockPayments->find('all',['conditions' =>[("UnlockPayments.created BETWEEN '$monthStart' AND '$currentDate'"),
																			  'UnlockPayments.kiosk_id' => $kioskId,
																			  'UnlockPayments.payment_method' => 'Card']]);
        $currentMonthUcardPaymentDetail_query
                          ->select(['currentMonthUnlockSale' => $currentMonthUcardPaymentDetail_query->func()->sum('UnlockPayments.amount')]);
		$currentMonthUcardPaymentDetail_query = $currentMonthUcardPaymentDetail_query->hydrate(false);
        if(!empty($currentMonthUcardPaymentDetail_query)){
            $currentMonthUcardPaymentDetail = $currentMonthUcardPaymentDetail_query->first();
        }else{
            $currentMonthUcardPaymentDetail = array();
        }
		
		$currentMonthUcardPayment = $currentMonthUcardPaymentDetail['currentMonthUnlockSale'];
		if(empty($currentMonthUcardPayment)){
				$currentMonthUcardPayment = 0;
		}
        
        $currentMonthUcashPaymentDetail_query = $this->UnlockPayments->find('all',['conditions' =>[("UnlockPayments.created BETWEEN '$monthStart' AND '$currentDate'"),
																	  'UnlockPayments.kiosk_id' => $kioskId,
																	  'UnlockPayments.payment_method' => 'Cash',
																	  'UnlockPayments.amount >=' => 0]]);
        $currentMonthUcashPaymentDetail_query
                          ->select(['currentMonthUnlockSale' => $currentMonthUcashPaymentDetail_query->func()->sum('UnlockPayments.amount')]);
		$currentMonthUcashPaymentDetail_query = $currentMonthUcashPaymentDetail_query->hydrate(false);
        if(!empty($currentMonthUcashPaymentDetail_query)){
            $currentMonthUcashPaymentDetail = $currentMonthUcashPaymentDetail_query->first();
        }else{
            $currentMonthUcashPaymentDetail = array();
        }
        
		$currentMonthUcashPayment = $currentMonthUcashPaymentDetail['currentMonthUnlockSale'];
		if(empty($currentMonthUcashPayment)){
			$currentMonthUcashPayment = 0;
		}
	
        $previousMonthUnlockSaleData_query = $this->MobileUnlockSales->find('all',['conditions' =>[("MobileUnlockSales.created BETWEEN '$previousMonthStart' AND '$previousMonthEndPlus'"),
											  'MobileUnlockSales.kiosk_id' => $kioskId]]);
        $previousMonthUnlockSaleData_query
                          ->select(['previousMonthSale' => $previousMonthUnlockSaleData_query->func()->sum('MobileUnlockSales.amount')]);
		$previousMonthUnlockSaleData_query = $previousMonthUnlockSaleData_query->hydrate(false);
        if(!empty($previousMonthUnlockSaleData_query)){
            $previousMonthUnlockSaleData = $previousMonthUnlockSaleData_query->first();
        }else{
            $previousMonthUnlockSaleData = array();
        }
		
		$previousMonthUnlockSale = $previousMonthUnlockSaleData['previousMonthSale'];
        
        $previousMonthUcardPaymentDetail_query = $this->UnlockPayments->find('all',['conditions' =>[("UnlockPayments.created BETWEEN '$previousMonthStart' AND '$previousMonthEndPlus'"),
												  'UnlockPayments.kiosk_id'=>$kioskId,'UnlockPayments.payment_method' => 'Card']]);
        $previousMonthUcardPaymentDetail_query
                          ->select(['previousMonthUnlockSale' => $previousMonthUcardPaymentDetail_query->func()->sum('UnlockPayments.amount')]);
		$previousMonthUcardPaymentDetail_query = $previousMonthUcardPaymentDetail_query->hydrate(false);
        if(!empty($previousMonthUcardPaymentDetail_query)){
            $previousMonthUcardPaymentDetail = $previousMonthUcardPaymentDetail_query->first();
        }else{
            $previousMonthUcardPaymentDetail = array();
        }
        
		$previousMonthUcardPayment = $previousMonthUcardPaymentDetail['previousMonthUnlockSale'];
		if(empty($previousMonthUcardPayment)){
			$previousMonthUcardPayment = 0;
		}

        
        $previousMonthUcashPaymentDetail_query = $this->UnlockPayments->find('all',['conditions' =>[("UnlockPayments.created BETWEEN '$previousMonthStart' AND '$previousMonthEndPlus'"),
												  'UnlockPayments.kiosk_id' => $kioskId,
												  'UnlockPayments.payment_method' => 'Cash',
												  'UnlockPayments.amount >=' => 0]]);
        $previousMonthUcashPaymentDetail_query
                          ->select(['previousMonthUnlockSale' => $previousMonthUcashPaymentDetail_query->func()->sum('UnlockPayments.amount')]);
		$previousMonthUcashPaymentDetail_query = $previousMonthUcashPaymentDetail_query->hydrate(false);
        if(!empty($previousMonthUcashPaymentDetail_query)){
            $previousMonthUcashPaymentDetail = $previousMonthUcashPaymentDetail_query->first();
        }else{
            $previousMonthUcashPaymentDetail = array();
        }
        
		$previousMonthUcashPayment = $previousMonthUcashPaymentDetail['previousMonthUnlockSale'];
		if(empty($previousMonthUcashPayment)){
			$previousMonthUcashPayment = 0;
		}
        
        $currentMonthUnlockRefundData_query = $this->MobileUnlockSales->find('all',['conditions' =>[("MobileUnlockSales.refund_on BETWEEN '$monthStart' AND '$currentDate'"),
												'MobileUnlockSales.kiosk_id'=>$kioskId,
												'MobileUnlockSales.refund_status' => 1]]);
        $currentMonthUnlockRefundData_query
                          ->select(['currentMonthUrefund' => $currentMonthUnlockRefundData_query->func()->sum('MobileUnlockSales.refund_amount')]);
		$currentMonthUnlockRefundData_query = $currentMonthUnlockRefundData_query->hydrate(false);
        if(!empty($currentMonthUnlockRefundData_query)){
            $currentMonthUnlockRefundData = $currentMonthUnlockRefundData_query->first();
        }else{
            $currentMonthUnlockRefundData = array();
        }
        
		$currentMonthUnlockRefund = 0;
		if(!empty($currentMonthUnlockRefundData['currentMonthUrefund'])){
			$currentMonthUnlockRefund = -$currentMonthUnlockRefundData['currentMonthUrefund'];
		}
        
        $previousMonthUnlockRefundData_query = $this->MobileUnlockSales->find('all',['conditions' =>[("MobileUnlockSales.refund_on BETWEEN '$previousMonthStart' AND '$previousMonthEndPlus'"),
												'MobileUnlockSales.kiosk_id'=>$kioskId,
												'MobileUnlockSales.refund_status' => 1]]);
        $previousMonthUnlockRefundData_query
                          ->select(['previousMonthUrefund' => $previousMonthUnlockRefundData_query->func()->sum('MobileUnlockSales.refund_amount')]);
		$currentMonthUnlockRefundData_query = $previousMonthUnlockRefundData_query->hydrate(false);
        if(!empty($previousMonthUnlockRefundData_query)){
            $previousMonthUnlockRefundData = $previousMonthUnlockRefundData_query->first();
        }else{
            $previousMonthUnlockRefundData = array();
        }
		
		$previousMonthUnlockRefund = 0;
		if(!empty($previousMonthUnlockRefundData['previousMonthUrefund'])){
			$previousMonthUnlockRefund = -$previousMonthUnlockRefundData['previousMonthUrefund'];
		}
		
		$this->set(compact('currentMonthUnlockSale','previousMonthUnlockSale','currentMonthUnlockRefund', 'previousMonthUnlockRefund','currentMonthUcardPayment','previousMonthUcardPayment','currentMonthUcashPayment','previousMonthUcashPayment'));
    }
    
    private function monthly_repair_sale(){
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		if(!empty($this->request->params['pass']) && array_key_exists(0,$this->request->params['pass'])){//for admin kiosk_total_sale
			$kioskId = $this->request->params['pass'][0];
		}elseif((int)$kiosk_id){
			$kioskId = $kiosk_id;
		}else{
			$kioskId = 0;
		}
				
		$currentDate = date('Y-m-d',strtotime(' +1 day'));//adding an extra day to get correct result of current month
		$monthStart = date('Y-m-1');
		$month_ini = new \DateTime("first day of last month"); //using datetime class of PHP
		$month_end = new \DateTime("last day of last month");
		$previousMonthStart = $month_ini->format('Y-m-d');
		$previousMonthEnd = $month_end->format('Y-m-d');
		$previousMonthEndPlus = date('Y-m-d',strtotime($previousMonthEnd.' +1 day'));//adding one more day to last month to get correct data from db for last month
        
        $currentMonthRepairSaleData_query = $this->MobileRepairSales->find('all',['conditions' =>[("MobileRepairSales.created BETWEEN '$monthStart' AND '$currentDate'"),
												'MobileRepairSales.kiosk_id'=>$kioskId]]);
        $currentMonthRepairSaleData_query
                          ->select(['currentMonthRepairSale' => $currentMonthRepairSaleData_query->func()->sum('MobileRepairSales.amount')]);
		$currentMonthRepairSaleData_query = $currentMonthRepairSaleData_query->hydrate(false);
        if(!empty($currentMonthRepairSaleData_query)){
            $currentMonthRepairSaleData = $currentMonthRepairSaleData_query->first();
        }else{
            $currentMonthRepairSaleData = array();
        }
		
		$currentMonthRepairSale = $currentMonthRepairSaleData['currentMonthRepairSale'];
        
        $currentMonthRcardPaymentDetail_query = $this->RepairPayments->find('all',['conditions' =>[("RepairPayments.created BETWEEN '$monthStart' AND '$currentDate'"),
												'RepairPayments.kiosk_id'=>$kioskId,'RepairPayments.payment_method' => 'Card']]);
        $currentMonthRcardPaymentDetail_query
                          ->select(['currentMonthRepairSale' => $currentMonthRcardPaymentDetail_query->func()->sum('RepairPayments.amount')]);
		$currentMonthRcardPaymentDetail_query = $currentMonthRcardPaymentDetail_query->hydrate(false);
        if(!empty($currentMonthRcardPaymentDetail_query)){
            $currentMonthRcardPaymentDetail = $currentMonthRcardPaymentDetail_query->first();
        }else{
            $currentMonthRcardPaymentDetail = array();
        }
        
		$currentMonthRcardPayment = $currentMonthRcardPaymentDetail['currentMonthRepairSale'];
		if(empty($currentMonthRcardPayment)){
			$currentMonthRcardPayment = 0;
		}
        
        $currentMonthRcashPaymentDetail_query = $this->RepairPayments->find('all',['conditions' =>[("RepairPayments.created BETWEEN '$monthStart' AND '$currentDate'"),
												'RepairPayments.kiosk_id'=>$kioskId,
												'RepairPayments.payment_method' => 'Cash',
												'RepairPayments.amount >=' => 0]]);
        $currentMonthRcashPaymentDetail_query
                          ->select(['currentMonthRepairSale' => $currentMonthRcashPaymentDetail_query->func()->sum('RepairPayments.amount')]);
		$currentMonthRcashPaymentDetail_query = $currentMonthRcashPaymentDetail_query->hydrate(false);
        if(!empty($currentMonthRcashPaymentDetail_query)){
            $currentMonthRcashPaymentDetail = $currentMonthRcashPaymentDetail_query->first();
        }else{
            $currentMonthRcashPaymentDetail = array();
        }
		
		$currentMonthRcashPayment = $currentMonthRcashPaymentDetail['currentMonthRepairSale'];
		if(empty($currentMonthRcashPayment)){
			$currentMonthRcashPayment = 0;
		}
        
        $previousMonthRepairSaleData_query = $this->MobileRepairSales->find('all',['conditions' =>[("MobileRepairSales.created BETWEEN '$previousMonthStart' AND '$previousMonthEndPlus'"),
												'MobileRepairSales.kiosk_id'=>$kioskId]]);
        $previousMonthRepairSaleData_query
                          ->select(['previousMonthRepairSale' => $previousMonthRepairSaleData_query->func()->sum('MobileRepairSales.amount')]);
		$previousMonthRepairSaleData_query = $previousMonthRepairSaleData_query->hydrate(false);
        if(!empty($previousMonthRepairSaleData_query)){
            $previousMonthRepairSaleData = $previousMonthRepairSaleData_query->first();
        }else{
            $previousMonthRepairSaleData = array();
        }
        
		$previousMonthRepairSale = $previousMonthRepairSaleData['previousMonthRepairSale'];
        
        $previousMonthRcardPaymentDetail_query = $this->RepairPayments->find('all',['conditions' =>[("RepairPayments.created BETWEEN '$previousMonthStart' AND '$previousMonthEndPlus'"),
												'RepairPayments.kiosk_id'=>$kioskId,'RepairPayments.payment_method' => 'Card']]);
        $previousMonthRcardPaymentDetail_query
                          ->select(['previousMonthRepairSale' => $previousMonthRcardPaymentDetail_query->func()->sum('RepairPayments.amount')]);
		$previousMonthRcardPaymentDetail_query = $previousMonthRcardPaymentDetail_query->hydrate(false);
        if(!empty($previousMonthRcardPaymentDetail_query)){
            $previousMonthRcardPaymentDetail = $previousMonthRcardPaymentDetail_query->first();
        }else{
            $previousMonthRcardPaymentDetail = array();
        }
        
		$previousMonthRcardPayment = $previousMonthRcardPaymentDetail['previousMonthRepairSale'];
		if(empty($previousMonthRcardPayment)){
				$previousMonthRcardPayment = 0;
		}
        
        $previousMonthRcashPaymentDetail_query = $this->RepairPayments->find('all',['conditions' =>[("RepairPayments.created BETWEEN '$previousMonthStart' AND '$previousMonthEndPlus'"),
												'RepairPayments.kiosk_id'=>$kioskId,
												'RepairPayments.payment_method' => 'Cash',
												'RepairPayments.amount >=' => 0]]);
        $previousMonthRcashPaymentDetail_query
                          ->select(['previousMonthRepairSale' => $previousMonthRcashPaymentDetail_query->func()->sum('RepairPayments.amount')]);
		$previousMonthRcashPaymentDetail_query = $previousMonthRcashPaymentDetail_query->hydrate(false);
        if(!empty($previousMonthRcashPaymentDetail_query)){
            $previousMonthRcashPaymentDetail = $previousMonthRcashPaymentDetail_query->first();
        }else{
            $previousMonthRcashPaymentDetail = array();
        }
        
		$previousMonthRcashPayment = $previousMonthRcashPaymentDetail['previousMonthRepairSale'];
		if(empty($previousMonthRcashPayment)){
				$previousMonthRcashPayment = 0;
		}
        
        $currentMonthRepairRefundData_query = $this->MobileRepairSales->find('all',['conditions' =>[("MobileRepairSales.refund_on BETWEEN '$monthStart' AND '$currentDate'"),
												'MobileRepairSales.kiosk_id'=>$kioskId,
												'MobileRepairSales.refund_status' => 1]]);
        $currentMonthRepairRefundData_query
                          ->select(['currentMonthRepairRefund' => $currentMonthRepairRefundData_query->func()->sum('MobileRepairSales.refund_amount')]);
		$currentMonthRepairRefundData_query = $currentMonthRepairRefundData_query->hydrate(false);
        if(!empty($currentMonthRepairRefundData_query)){
            $currentMonthRepairRefundData = $currentMonthRepairRefundData_query->first();
        }else{
            $currentMonthRepairRefundData = array();
        }
		
		$currentMonthRepairRefund = 0;
		if(!empty($currentMonthRepairRefundData['currentMonthRepairRefund'])){
				$currentMonthRepairRefund = -$currentMonthRepairRefundData['currentMonthRepairRefund'];   
		}
        
        $previousMonthRepairRefundData_query = $this->MobileRepairSales->find('all',['conditions' =>[("MobileRepairSales.refund_on BETWEEN '$previousMonthStart' AND '$previousMonthEndPlus'"),
												'MobileRepairSales.kiosk_id' => $kioskId,
												'MobileRepairSales.refund_status' => 1]]);
        $previousMonthRepairRefundData_query
                          ->select(['previousMonthRepairRefund' => $previousMonthRepairRefundData_query->func()->sum('MobileRepairSales.refund_amount')]);
		$previousMonthRepairRefundData_query = $previousMonthRepairRefundData_query->hydrate(false);
        if(!empty($previousMonthRepairRefundData_query)){
            $previousMonthRepairRefundData = $previousMonthRepairRefundData_query->first();
        }else{
            $previousMonthRepairRefundData = array();
        }
		
		$previousMonthRepairRefund = 0;
		if(!empty($previousMonthRepairRefundData['previousMonthRepairRefund'])){
			$previousMonthRepairRefund = -$previousMonthRepairRefundData['previousMonthRepairRefund'];   
		}
				
		$this->set(compact('currentMonthRepairSale','previousMonthRepairSale','currentMonthRepairRefund', 'previousMonthRepairRefund','currentMonthRcardPayment','previousMonthRcardPayment','currentMonthRcashPayment','previousMonthRcashPayment'));	
    }
    
    private function get_t_product_sale($kskId = '', $dte = ''){
		if($kskId){
			//for monthly_kiosk_sale page
			$kioskId = $kskId;
		}else{
			$kiosk_id = $this->request->Session()->read('kiosk_id');
			if(!empty($this->request->params['pass']) && array_key_exists(0,$this->request->params['pass'])){
				//for admin kiosk_total_sale
				$kioskId = $this->request->params['pass'][0];
			}elseif((int)$kiosk_id){
				$kioskId = $kiosk_id;
			}else{
				$kioskId = 0;
			}
		}
		if((int)$kioskId == 0){
			$saleSource = "t_kiosk_product_sales";
			$productReceiptSource = "t_product_receipts";
			$paymentDetailSource = "t_payment_details";
			$invoiceOrderSource = "invoice_orders";
			$todSale = 0;
		}else{
			$saleSource = "t_kiosk_product_sales";
			$productReceiptSource = "t_product_receipts";
			$paymentDetailSource = "t_payment_details";
			$invoiceOrderSource = "kiosk_{$kioskId}_invoice_orders";
		}
		
        $ProductReceiptsTable = TableRegistry::get($productReceiptSource,[
                                                                            'table' => $productReceiptSource,
                                                                        ]);
		//$this->KioskProductSales->setSource($saleSource);
        $KioskProductSalesTable = TableRegistry::get($saleSource,[
                                                                    'table' => $saleSource,
                                                                ]);
		$date = date('Y-m-d');
		// today sale-----------
		$receiptIdArr_query = $KioskProductSalesTable->find('list',[
																	'keyField' => 'id',
                                                                    'valueField' => 'product_receipt_id',
																	'conditions' => ['Date(created)' => $date,
																						  'kiosk_id'=> $kioskId,
																						  'refund_status' => 0,
                                                                                    ],
                                                             ]
													  );
        $receiptIdArr_query = $receiptIdArr_query->hydrate(false);
        if(!empty($receiptIdArr_query)){
            $receiptIdArr = $receiptIdArr_query->toArray();
        }else{
            $receiptIdArr = array();
        }
        $PaymentDetailsTable = TableRegistry::get($paymentDetailSource,[
                                                                    'table' => $paymentDetailSource,
                                                                ]);
		//$this->PaymentDetails->setSource($paymentDetailSource);
        if(empty($receiptIdArr)){
            $receiptIdArr = array(0 => null);
        }
        $t_today_total_amount_query = $PaymentDetailsTable->find('all',['conditions' => ['product_receipt_id IN' => $receiptIdArr]]);
                  $t_today_total_amount_query
                          ->select(['total_sale' => $t_today_total_amount_query->func()->sum('amount')]);
        $t_today_total_amount_query = $t_today_total_amount_query->hydrate(false);
        if(!empty($t_today_total_amount_query)){
            $t_today_total_amount = $t_today_total_amount_query->toArray();
        }else{
            $t_today_total_amount = array();
        }
		
		$t_today_total_amount = $t_today_total_amount[0]['total_sale'];
        if(empty($receiptIdArr)){
            $receiptIdArr = array(0 => null);
        }
		$alltodaypaymentData_query = $PaymentDetailsTable->find('all', array(
														'conditions' => array('product_receipt_id IN' => $receiptIdArr)
													));
        $alltodaypaymentData_query = $alltodaypaymentData_query->hydrate(false);
        if(!empty($alltodaypaymentData_query)){
            $alltodaypaymentData = $alltodaypaymentData_query->toArray();
        }else{
            $alltodaypaymentData = array();
        }
		$t_today_pay_details = $this->get_t_PaymentDetailSale($alltodaypaymentData);
		$yreceiptIdArr_query = $KioskProductSalesTable->find('list',[
                                                                    'keyField' => 'id',
                                                                    'valueField' => 'product_receipt_id',
                                                                    'conditions' => ['Date(created)' => date('Y-m-d', strtotime(' -1 day')),
                                                					'kiosk_id'=> $kioskId,
                                                                    'refund_status' => 0,
                                                                                    ],
                                                              ]);
		$yreceiptIdArr_query = $yreceiptIdArr_query->hydrate(false);
        if(!empty($yreceiptIdArr_query)){
            $yreceiptIdArr = $yreceiptIdArr_query->toArray();
        }else{
            $yreceiptIdArr = array();
        }
        if(empty($yreceiptIdArr)){
            $yreceiptIdArr = array(0 => null);
        }
        $t_yes_total_amount_query = $PaymentDetailsTable->find('all',['conditions' => ['product_receipt_id IN' => $yreceiptIdArr]]);
                  $t_yes_total_amount_query
                          ->select(['total_sale' => $t_yes_total_amount_query->func()->sum('amount')]);
        $t_yes_total_amount_query = $t_yes_total_amount_query->hydrate(false);
        if(!empty($t_yes_total_amount_query)){
            $t_yes_total_amount = $t_yes_total_amount_query->toArray();
        }else{
            $t_yes_total_amount = array();
        }
        
		$t_yes_total_amount = $t_yes_total_amount[0]['total_sale'];
        if(empty($yreceiptIdArr)){
            $yreceiptIdArr = array(0 => null);
        }
		$allyespaymentData_query = $PaymentDetailsTable->find('all', array(
														'conditions' => array('product_receipt_id IN' => $yreceiptIdArr)
													));
        $allyespaymentData_query = $allyespaymentData_query->hydrate(false);
        if(!empty($allyespaymentData_query)){
            $allyespaymentData = $allyespaymentData_query->toArray();
        }else{
            $allyespaymentData = array();
        }
		$yes_pay_details = $this->get_t_PaymentDetailSale($allyespaymentData);
		
		$date = date('Y-m-d');		
		if($dte){$date = $dte;}
		$credit_to_other_changed  = array(
											'credit' => 0,
											'cash' => 0,
											'bank_transfer' => 0,
											'cheque' => 0,
											'card' => 0,
											'misc' => 0								  
										  );
		if(true){  //$kioskId == 0
			$result_query =  $PaymentDetailsTable->find('all',array('conditions' => array('date(created)' => $date,'kiosk_id' =>$kioskId),
												   ));
            $result_query = $result_query->hydrate(false);
            if(!empty($result_query)){
                $result = $result_query->toArray();
            }else{
                $result = array();
            }
            //pr($result);die;
             $recipt_arr = array();
			foreach($result as $paykey => $payvalue){
                $recipt_arr[$payvalue['product_receipt_id']] = $payvalue['product_receipt_id'];
            }
            if(empty($recipt_arr)){
                $recipt_arr = array(0 => null);
            }
            
            $recipt_res_query  = $ProductReceiptsTable->find('list',array('conditions' => array('id IN' => $recipt_arr),
                                       'keyField' => 'id',
                                       'valueField' => 'created',
                                       ));
            
            $recipt_res_query = $recipt_res_query->hydrate(false);
            if(!empty($recipt_res_query)){
                $recipt_res = $recipt_res_query->toArray();
            }else{
                $recipt_res = array();
            }
            
			$onCreditAmt = $cashAmt = $cardAmt = $bnkTrnAmt = $chkAmt = $miscAmt = 0;
			foreach($result as $s => $val){
                $recipt_id = $val['product_receipt_id'];
                if(array_key_exists($recipt_id,$recipt_res)){
                    $created = $recipt_res[$recipt_id];
                }
				$pay_time = strtotime(date("Y-m-d",strtotime($val['created'])));
				$recit_time = strtotime(date("Y-m-d",strtotime($created)));
				if($pay_time != $recit_time){
					$pmtAmt1 = $val['amount'];
					$pay_method = $val['payment_method'];
					switch($pay_method){
						case 'On Credit':
							$onCreditAmt += $pmtAmt1;
							break;
						case 'Cash':
							$cashAmt += $pmtAmt1;
							break;
						case 'Bank Transfer':
							$bnkTrnAmt += $pmtAmt1;
							break;
						case 'Cheque':
							$chkAmt += $pmtAmt1;
							break;
						case 'Card':
							$cardAmt += $pmtAmt1;
							break;
						case 'Misc':
							$miscAmt += $pmtAmt1;
							break;
					}
				}
			}
			$t_credit_to_other_changed = array('credit' => $onCreditAmt,'cash' => $cashAmt, 'bank_transfer' => $bnkTrnAmt, 'cheque' => $chkAmt, 'card' => $cardAmt, 'misc' => $miscAmt);
		}
		$t_y_prv_credit_to_card = $this->t_prv_credit_to_card($kioskId);
		// yesterday sale --------------
		$this->set(compact('t_today_total_amount','t_yes_total_amount','t_today_pay_details','yes_pay_details','t_credit_to_other_changed','t_y_prv_credit_to_card'));
	}
    
    private function get_t_PaymentDetailSale($pay_arr = array()){
		$onCreditAmt = $cashAmt = $cardAmt = $bnkTrnAmt = $chkAmt = $miscAmt = 0;
		foreach($pay_arr as $payment){
            //pr($payment);die;
			$creditRecptID = $payment['id'];
			$pmtMethod = trim($payment['payment_method']);
			$pmtAmt = $payment['amount'];
			switch($pmtMethod){
				case 'On Credit':
					$onCreditAmt += $pmtAmt;
					break;
				case 'Cash':
					$cashAmt += $pmtAmt;
					break;
				case 'Bank Transfer':
					$bnkTrnAmt += $pmtAmt;
					break;
				case 'Cheque':
					$chkAmt += $pmtAmt;
					break;
				case 'Card':
					$cardAmt += $pmtAmt;
					break;
				case 'Misc':
					$miscAmt += $pmtAmt;
					break;
			}
			
		}
		return $payment_arr = array('credit' => $onCreditAmt,'cash' => $cashAmt, 'bank_transfer' => $bnkTrnAmt, 'cheque' => $chkAmt, 'card' => $cardAmt, 'misc' => $miscAmt);
	}
    
    private function get_t_month_product_sale(){
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		if(!empty($this->request->params['pass']) && array_key_exists(0,$this->request->params['pass'])){
			//for admin kiosk_total_sale
			$kioskId = $this->request->params['pass'][0];
		}elseif((int)$kiosk_id){
			$kioskId = $kiosk_id;
		}else{
			$kioskId = 0;
		}
		
		
		if((int)$kioskId == 0){
			$saleSource = "t_kiosk_product_sales";
			$productReceiptSource = "t_product_receipts";
			$paymentDetailSource = "t_payment_details";
			$invoiceOrderSource = "invoice_orders";
			$todSale = 0;
		}else{
			$saleSource = "t_kiosk_product_sales";
			$productReceiptSource = "t_product_receipts";
			$paymentDetailSource = "t_payment_details";
			$invoiceOrderSource = "kiosk_{$kioskId}_invoice_orders";
		}
		$ProductReceiptsTable = TableRegistry::get($productReceiptSource,[
                                                                            'table' => $productReceiptSource,
                                                                        ]);
        $KioskProductSalesTable = TableRegistry::get($saleSource,[
                                                                    'table' => $saleSource,
                                                                ]);
		
		$currentDate = date('Y-m-d',strtotime(' +1 day'));//adding an extra day to get correct result of current month
		$monthStart = date('Y-m-1');
		$month_ini = new \DateTime("first day of last month"); //using datetime class of PHP
		$month_end = new \DateTime("last day of last month");
		$previousMonthStart = $month_ini->format('Y-m-d');
		$previousMonthEnd = $month_end->format('Y-m-d');
		$previousMonthEndPlus = date('Y-m-d',strtotime($previousMonthEnd.' +1 day'));
		
		
		$t_current_month_credit_to_other_changed = $this->custom_month_credit_to_card($monthStart,$currentDate,$kioskId,$source=0);
		$t_prv_month_credit_to_other_changed = $this->custom_month_credit_to_card($previousMonthStart,$previousMonthEndPlus,$kioskId,$source=0);
		$this->set(compact('t_current_month_credit_to_other_changed','t_prv_month_credit_to_other_changed'));
		$receiptIdArr_query = $KioskProductSalesTable->find('list',[
                                                                'keyField' => 'id',
                                                                'valueField' => 'product_receipt_id',
                                                                'conditions' => ["created BETWEEN '$monthStart' AND '$currentDate'",
																		  'kiosk_id'=> $kioskId,
																		  'refund_status' => 0,
																		  ]
                                                            ]);
		$receiptIdArr_query = $receiptIdArr_query->hydrate(false);
        if(!empty($receiptIdArr_query)){
            $receiptIdArr = $receiptIdArr_query->toArray();
        }else{
            $receiptIdArr = array();
        }
		//$this->PaymentDetail->setSource($paymentDetailSource);
        $PaymentDetailsTable = TableRegistry::get($paymentDetailSource,[
                                                                    'table' => $paymentDetailSource,
                                                                ]);
        if(empty($receiptIdArr)){
            $receiptIdArr = array(0 => null);
        }
        $t_this_mnth_total_amount_query = $PaymentDetailsTable->find('all',['conditions' => ['product_receipt_id IN' => $receiptIdArr]]);
                  $t_this_mnth_total_amount_query
                          ->select(['total_sale' => $t_this_mnth_total_amount_query->func()->sum('amount')]);
        $t_this_mnth_total_amount_query = $t_this_mnth_total_amount_query->hydrate(false);
        if(!empty($t_this_mnth_total_amount_query)){
            $t_this_mnth_total_amount = $t_this_mnth_total_amount_query->toArray();
        }else{
            $t_this_mnth_total_amount = array();
        }
                    		
		$t_this_mnth_total_amount = $t_this_mnth_total_amount[0]['total_sale'];
		if(empty($receiptIdArr)){
            $receiptIdArr = array(0 => null);
        }
		$all_this_month_paymentData_query = $PaymentDetailsTable->find('all', array(
														'conditions' => array('product_receipt_id IN' => $receiptIdArr)
													));
        $all_this_month_paymentData_query = $all_this_month_paymentData_query->hydrate(false);
        if(!empty($all_this_month_paymentData_query)){
            $all_this_month_paymentData = $all_this_month_paymentData_query->toArray();
        }else{
            $all_this_month_paymentData = array();
        }
		$t_this_mnth_pay_details = $this->get_t_PaymentDetailSale($all_this_month_paymentData);
	
		$privsMnthreceiptIdArr_query = $KioskProductSalesTable->find('list',[
                                                                        'keyField' => 'id',
                                                                        'valueField' => 'product_receipt_id',
                                                                        //'fields' => array('id','product_receipt_id'),
                                                                        'conditions' => ["created BETWEEN '$previousMonthStart' AND '$previousMonthEndPlus'",
                                                                        'kiosk_id'=> $kioskId,
                                                                        'refund_status' => 0,
														  ]
                                                                        ]);
        $privsMnthreceiptIdArr_query = $privsMnthreceiptIdArr_query->hydrate(false);
        if(!empty($privsMnthreceiptIdArr_query)){
            $privsMnthreceiptIdArr = $privsMnthreceiptIdArr_query->toArray();
        }else{
            $privsMnthreceiptIdArr = array();
        }
        if(empty($privsMnthreceiptIdArr)){
            $privsMnthreceiptIdArr = array(0 => null);
        }
		$t_prv_mnth_total_amount_query = $PaymentDetailsTable->find('all',['conditions' => ['product_receipt_id IN' => $privsMnthreceiptIdArr]]);
                  $t_prv_mnth_total_amount_query
                          ->select(['total_sale' => $t_prv_mnth_total_amount_query->func()->sum('amount')]);
        $t_prv_mnth_total_amount_query = $t_prv_mnth_total_amount_query->hydrate(false);
        if(!empty($t_prv_mnth_total_amount_query)){
            $t_prv_mnth_total_amount = $t_prv_mnth_total_amount_query->toArray();
        }else{
            $t_prv_mnth_total_amount = array();
        }
        //pr($t_prv_mnth_total_amount);die;
		$t_prv_mnth_total_amount = $t_prv_mnth_total_amount[0]['total_sale'];
        if(empty($privsMnthreceiptIdArr)){
            $privsMnthreceiptIdArr = array(0 => null);
        }
		$all_prv_mnth_paymentData_query = $PaymentDetailsTable->find('all', array(
														'conditions' => array('product_receipt_id IN' => $privsMnthreceiptIdArr)
													));
        $all_prv_mnth_paymentData_query = $all_prv_mnth_paymentData_query->hydrate(false);
        if(!empty($all_prv_mnth_paymentData_query)){
            $all_prv_mnth_paymentData = $all_prv_mnth_paymentData_query->toArray();
        }else{
            $all_prv_mnth_paymentData = array();
        }
		$prv_mnth_pay_details = $this->get_t_PaymentDetailSale($all_prv_mnth_paymentData);
		// prv mnth sale --------------
		$this->set(compact('t_this_mnth_total_amount','t_prv_mnth_total_amount','t_this_mnth_pay_details','prv_mnth_pay_details'));
	}
	public  function mobileRprPriceNtfctn(){
		$currency = $this->setting['currency_symbol'];
		//Configure::load('common-arrays');
		$problemType = Configure::read('problem_type');
		$mobileRepairPrices_query = $this->MobileRepairPrices->find('all',array(
																		 'conditions' => array('DATE(MobileRepairPrices.modified) >= DATE_ADD(CURDATE(), INTERVAL -3 DAY)'),
																		 'order' => array('MobileRepairPrices.modified' => 'DESC')));
		$mobileRepairPrices_query = $mobileRepairPrices_query->hydrate(false);
		if(!empty($mobileRepairPrices_query)){
			$mobileRepairPrices = $mobileRepairPrices_query->toArray();
		}else{
			$mobileRepairPrices = array();
		}
		// pr($mobileRepairPrices);
		$brandName_query = $this->Brands->find('list',
												[
													'keyField' =>'id',
													'valueField' => 'brand',
												]
										 );
		$brandName_query = $brandName_query->hydrate(false);
		if(!empty($brandName_query)){
			$brandName = $brandName_query->toArray();
		}else{
			$brandName = array();
		}
		$repairMobileModelIds = $mobileRepairPriceNotification = array();
		foreach($mobileRepairPrices as $key=>$mobileRepairPrice){
		   $repairMobileModelIds[$mobileRepairPrice['mobile_model_id']] = $mobileRepairPrice['mobile_model_id'];
		}
		if(empty($repairMobileModelIds)){
			$repairMobileModelIds = array(0=>null);
		}
		$repairMobileModelNames_query = $this->MobileModels->find('list', array('conditions' => array('MobileModels.id IN' => $repairMobileModelIds),
																		  'keyField' =>'id',
																		  'valueField' => 'model',
																		 ));
		$repairMobileModelNames_query = $repairMobileModelNames_query->hydrate(false);
		if(!empty($repairMobileModelNames_query)){
			$repairMobileModelNames = $repairMobileModelNames_query->toArray();
		}else{
			$repairMobileModelNames = array();
		}
		foreach($mobileRepairPrices as $key=>$mobileRepairPrice){
			$mobileRepairPriceNotification[] = "Updated to : <span style='color: crimson'><strong>{$currency}{$mobileRepairPrice['repair_price']}</strong></span> for <span style='color: blue'> {$brandName[$mobileRepairPrice['brand_id']]}: {$repairMobileModelNames[$mobileRepairPrice['mobile_model_id']]} [Problem Type:
			{$problemType[$mobileRepairPrice['problem_type']]}]</span>
			 repair .<!--with in <span style='color: crimson'><strong> {$mobileRepairPrice['repair_days']}</strong></span> days.--><br/>";
		}
		$this->set(compact( 'currency', 'mobileRepairPriceNotification', 'mobileRepairPrices','brandName', 'repairMobileModelNames','problemType' )); 
	}
	
	public function newProductsNotification(){
        $vat = $this->setting['vat'];
		$currency = $this->setting['currency_symbol'];
		$products_query = $this->Products->find('all',array('conditions'=>array(
				    'DATE(Products.created) >= DATE_ADD(CURDATE(), INTERVAL -3 DAY)'
							 ),
		 'fields' => array('id','product','image','selling_price', 'product_code', 'category_id','created', 'modified', 'quantity'),
		 'recursive' => -1));
		$products_query = $products_query->hydrate(false);
		if(!empty($products_query)){
			$products = $products_query->toArray();
		}else{
			$products = array();
		}
		$productNofification = array();
		$categoryIds = array();
		//grabbing the category id of the newly entered products
		foreach($products as $key=>$product){
		    $categoryIds[$product['category_id']] = $product['category_id'];
		}
		
		//--------- Code below for notification of update in quantity of warehosue products
		$warehouseStock_query = $this->WarehouseStock->find('all',array(
                                                                        'conditions'=>array(
                                                                                            'OR'=>array('DATE(WarehouseStock.created)=CURDATE()','DATE(WarehouseStock.created)>=DATE_ADD(CURDATE(), INTERVAL -3 DAY)')),'fields'=>array('id','product_id')));
		$warehouseStock_query = $warehouseStock_query->hydrate(false);
		if(!empty($warehouseStock_query)){
			$warehouseStock = $warehouseStock_query->toArray();
		}else{
			$warehouseStock = array();
		}
     
		$warehouseProductIds = array();
		foreach($warehouseStock as $key=>$warehouseStockProducts){
			$warehouseProductIds[$warehouseStockProducts['product_id']] = $warehouseStockProducts['product_id'];
		}
		if(empty($warehouseProductIds)){
            $warehouseProductIds = array('0' =>null);
        }
		$warehouseProducts_query = $this->Products->find('all',array('conditions'=>array('Products.id IN'=>$warehouseProductIds),'fields'=>array('id','product','product_code','selling_price','image', 'category_id', 'modified', 'quantity')));
		$warehouseProducts_query =$warehouseProducts_query->hydrate(false);
		if(!empty($warehouseProducts_query)){
			$warehouseProducts = $warehouseProducts_query->toArray();
		}else{
			$warehouseProducts = array();
		}
		$warehouseProductNotification = array();
		$warehouseProductNotificationArr = array();
		//pr($warehouseProducts);
		
		//grabbing the category id of the stocked in products
		foreach($warehouseProducts as $key=>$warehouseProduct){
		    $categoryIds[$warehouseProduct['category_id']] = $warehouseProduct['category_id'];
		}
		
		$categoryNames = array();
		if(count($categoryIds)){
		    $categoryNames_query = $this->Categories->find('list',array('keyField' => 'id','valueField'=>'category','conditions' => array('Categories.id IN' => $categoryIds)));
			$categoryNames_query = $categoryNames_query->hydrate(false);
			if(!empty($categoryNames_query)){
				$categoryNames = $categoryNames_query->hydrate(false);
			}else{
				$categoryNames = array();
			}
		}
		//pr($products);die;
		foreach($products as $key=>$product){
			$productNofification[$key]['id'] = $product['id'];
			$productNofification[$key]['image'] = $product['image'];
			$productNofification[$key]['Product'] = $product['product'];
			$productNofification[$key]['selling_price'] =  $product['selling_price'];
			$productNofification[$key]['product_code'] = $product['product_code'];
			$productNofification[$key]['category_id'] = $product['category_id'];
			$productNofification[$key]['created'] = $product['created'];
			$productNofification[$key]['modified'] = $product['modified'];
			$productNofification[$key]['quantity'] = $product['quantity'];
		}
		//pr($warehouseProducts);die;
		foreach($warehouseProducts as $key=>$warehouseProduct){
			if(array_key_exists($warehouseProduct['category_id'],$categoryNames)){
			    $catName = $categoryNames[$warehouseProduct['category_id']];
			}else{
			    $catName = '--';
			}
			$modified = date('jS M, Y g:i A', strtotime($warehouseProduct['modified']));
          //  pr($warehouseProduct);
			$warehouseProductNotification[] = "Product:{$warehouseProduct['product']} with the product-code:{$warehouseProduct['product_code']}, quantity: {$warehouseProduct['quantity']}, category: {$catName} and price of Approx. {$currency}{$warehouseProduct['selling_price']} has been added to the global stock on {$modified}.<br/>";
			
			$warehouseProductNotificationArr[$key]['id'] = $warehouseProduct['id'];
			$warehouseProductNotificationArr[$key]['image'] = $warehouseProduct['image'];
			$warehouseProductNotificationArr[$key]['product'] = $warehouseProduct['product'];
			$warehouseProductNotificationArr[$key]['selling_price'] =  $warehouseProduct['selling_price'];
			$warehouseProductNotificationArr[$key]['product_code'] = $warehouseProduct['product_code'];
			$warehouseProductNotificationArr[$key]['category_id'] = $warehouseProduct['category_id'];
			$warehouseProductNotificationArr[$key]['modified'] = $warehouseProduct['modified'];
		} 
		//--------- Code below for notification of update in quantity of warehosue products
		$this->set(compact( 'currency','productNofification','warehouseProductNotification','warehouseProductNotificationArr','categoryNames', 'vat'));
		//$this->set(compact('notificationStatement','currency','productNofification','warehouseProductNotification','mobileRepairPriceNotification','mobileUnlockPriceNotification'));
 
	} 
	public function productsPriceChangeNotification(){
		$vat = $this->setting['vat'];
		$currency = Configure::read('CURRENCY_TYPE');
		
		$products_query = $this->Products->find('all',array('conditions'=>array(
						    'DATE(Products.lu_sp) >= DATE_ADD(CURDATE(), INTERVAL -3 DAY)'),
						    'fields' => array('id','product','image','selling_price', 'product_code','lu_sp'),
							'order' => 'Products.lu_sp desc')
						);
		$products_query = $products_query->hydrate(false);
		if(!empty($products_query)){
			$products = $products_query->toArray();
		}else{
			$products = array();
		}
		//pr($products);
		$productPriceNotification = array();
		foreach($products as $key => $product){
			//$productNofification[] = "A new product:{$product['Product']['product']} with the product-code:{$product['Product']['product_code']} and price of {$currency}{$product['Product']['selling_price']} has been added to the global stock.<br/>";
			$productPriceNotification[$key]['id'] = $product['id'];
			$productPriceNotification[$key]['image'] = $product['image'];
			$productPriceNotification[$key]['Product'] = $product['product'];
			$productPriceNotification[$key]['selling_price'] =  $product['selling_price'];
			$productPriceNotification[$key]['product_code'] = $product['product_code'];
			$productPriceNotification[$key]['lu_sp'] = $product['lu_sp'];
		}
		
		$this->set(compact('productPriceNotification','currency','vat'));
	}
	
	public function specialOfferNotification(){
		$currency = $this->setting['currency_symbol'];
		
		$products_query = $this->Products->find('all',array('conditions'=>array(
						    'special_offer' => 1),
						    'fields' => array('id','product','image','selling_price', 'product_code','modified','discount'),
						    'recursive' => -1,
							'order' => 'Products.lu_sp desc')
						);
		$products_query = $products_query->hydrate(false);
		if(!empty($products_query)){
			$products = $products_query->toArray();
		}else{
			$products = array();
		}
		//pr($products);
		$productPriceNotification = array();
		foreach($products as $key => $product){
			//$productNofification[] = "A new product:{$product['Product']['product']} with the product-code:{$product['Product']['product_code']} and price of {$currency}{$product['Product']['selling_price']} has been added to the global stock.<br/>";
			$productPriceNotification[$key]['id'] = $product['id'];
			$productPriceNotification[$key]['image'] = $product['image'];
			$productPriceNotification[$key]['Product'] = $product['product'];
			$productPriceNotification[$key]['selling_price'] =  $product['selling_price'];
			$productPriceNotification[$key]['product_code'] = $product['product_code'];
			$productPriceNotification[$key]['modified'] = $product['modified'];
			$productPriceNotification[$key]['discount'] = $product['discount'];
		}
		
		$this->set(compact('productPriceNotification','currency'));
	}
    
    //checking modification in mobile unlock prices
     public function  mobileUnlockPriceNotification(){
		$activeStatus = array('0' => 'Offline', '1' => 'Online');
		$statusChangeData_query = $this->MobileUnlockPrices->find('all',array('conditions' => array('DATE(MobileUnlockPrices.status_change_date) >= DATE_ADD(CURDATE(), INTERVAL -3 DAY)'), 'order' => 'MobileUnlockPrices.status_change_date desc'));
        $statusChangeData_query = $statusChangeData_query->hydrate(false);
        if(!empty($statusChangeData_query)){
            $statusChangeData = $statusChangeData_query->toArray();
        }else{
            $statusChangeData = array();
        }
       // pr($statusChangeData);
		$statusModels = array();
		foreach($statusChangeData as $k => $statusChange){
		    $statusModels[$statusChange['MobileUnlockPrice']['mobile_model_id']] = $statusChange['MobileUnlockPrice']['mobile_model_id'];
		}
		if(count($statusModels)){
		    $statusMobileModelNames = $this->MobileModels->find('list', array('conditions' => array('MobileModels.id' => $statusModels),'fields' => array('id', 'model')));
		}
		
		$currency = $this->setting['currency_symbol'];
        $brandName_query = $this->Brands->find('list',
												[
													'keyField' =>'id',
													'valueField' => 'brand',
												]
										 );
		$brandName_query = $brandName_query->hydrate(false);
		if(!empty($brandName_query)){
			$brandName = $brandName_query->toArray();
		}else{
			$brandName = array();
		}
		//$brandName = $this->Brands->find('list', array('fields' => array('id', 'brand')));
		$mobileUnlockPrices_query = $this->MobileUnlockPrices->find('all',array(
                                                                          'conditions' => array('DATE(MobileUnlockPrices.modified) >= DATE_ADD(CURDATE(), INTERVAL -3 DAY)'),
                                                                          'recursive' => -1,
                                                                          'order' => 'MobileUnlockPrices.modified desc'));
        $mobileUnlockPrices_query = $mobileUnlockPrices_query->hydrate(false);
		if(!empty($mobileUnlockPrices_query)){
			$mobileUnlockPrices = $mobileUnlockPrices_query->toArray();
		}else{
			$mobileUnlockPrices = array();
		}
        $networks_query = $this->Networks->find('list',
												[
													'keyField' =>'id',
													'valueField' => 'name',
												]
										 );
		$networks_query = $networks_query->hydrate(false);
		if(!empty($networks_query)){
			$networks = $networks_query->toArray();
		}else{
			$networks = array();
		}
		
		$unlockMobileModelIds = array();
      //  pr($mobileUnlockPrices);
		foreach($mobileUnlockPrices as $key => $mobileUnlockPrice){
			$unlockMobileModelIds[$mobileUnlockPrice['mobile_model_id']] = $mobileUnlockPrice['mobile_model_id'];
		}
	//	pr($unlockMobileModelIds);
        if(empty($unlockMobileModelIds)){
            $unlockMobileModelIds = array(0 => null);       
        }
		$unlockMobileModelNames_query = $this->MobileModels->find('list',[
                                                                        'keyField' =>'id',
                                                                        'valueField' => 'model',
                                                                         'conditions'=>array('MobileModels.id IN'=>$unlockMobileModelIds) 
                                                                          
                                                                   ]
                                                            );
        $unlockMobileModelNames_query = $unlockMobileModelNames_query->hydrate(false);
		if(!empty($networks_query)){
			$unlockMobileModelNames = $unlockMobileModelNames_query->toArray();
		}else{
			$unlockMobileModelNames = array();
		}
       // pr($unlockMobileModelNames);
		$mobileUnlockPriceNotification = array();
		foreach($mobileUnlockPrices as $key => $mobileUnlockPrice){
			$mobileUnlockPriceNotification[] = "Updated to : <span style='color: crimson'><strong>{$currency}{$mobileUnlockPrice['unlocking_price']}</strong></span> for <span style='color: blue'>{$brandName[$mobileUnlockPrice['brand_id']]}:{$unlockMobileModelNames[$mobileUnlockPrice['mobile_model_id']]}[network type:{$networks[$mobileUnlockPrice['network_id']]}]</span><!--has been updated to  with in {$mobileUnlockPrice['unlocking_days']} days-->.<br/>";
		}
		$this->set(compact('currency' ,'mobileUnlockPriceNotification','statusChangeData','statusModels','brandName','activeStatus','networks','statusMobileModelNames','mobileUnlockPrices','unlockMobileModelNames'));
    }
	 private function dashboard_unlock(){
	
    }
	private function dashboard_service_center(){
		$users_query = $this->Users->find('list',array(
											   'conditions' => array('OR' => array(
																	 'Users.group_id' => REPAIR_TECHNICIANS,
																	 )),
											   'keyField' => 'id',
											   'valueField' => 'username',
											   ));
		$users_query = $users_query->hydrate(false);
		if(!empty($users_query)){
			$users = $users_query->toArray();
		}else{
			$users = array();
		}
	    $this->phones_in_queue();
	    $this->phones_received();
	    $this->dispatched_2_technician();
	    $this->dispatched_repaired();
	    $this->dispatched_unrepaired();
		$this->set(compact('users'));
    }
	
	
	private function phones_in_queue(){	
				$kiosk_id = $this->request->Session()->read('kiosk_id');
				$conditions = array('MobileRepairs.status' => DISPATCHED_TO_TECHNICIAN, 'Date(MobileRepairs.modified)' => date('Y-m-d'));

					$phonesInQueue_query = $this->MobileRepairs->find('all',array(
											//'fields' => array('COUNT(MobileRepair.status) as number'),
											'conditions' => $conditions
											)
									 );
					$phonesInQueue_query
										->select(['number' => $phonesInQueue_query->func()->count('MobileRepairs.status')]);
					$phonesInQueue_query = $phonesInQueue_query->hydrate(false);
					if(!empty($phonesInQueue_query)){
						$phonesInQueue = $phonesInQueue_query->toArray();
					}else{
						$phonesInQueue = array();
					}
					$number = $phonesInQueue[0]['number'];
					$this->set(compact('number'));
    }
    
    private function phones_received(){
				$kiosk_id = $this->request->Session()->read('kiosk_id');
				$conditions = array('MobileRepairLogs.repair_status'=>RECEIVED_BY_TECHNICIAN, 'Date(MobileRepairLogs.created)' => date('Y-m-d'));
				//, 'MobileRepairLog.kiosk_id'=>$kiosk_id
				$phonesReceived_query = $this->MobileRepairLogs->find('all',array(
											'conditions' => $conditions
											)
									 );
				$phonesReceived_query
										->select(['phonesReceivedToday' => $phonesReceived_query->func()->count('MobileRepairLogs.repair_status')]);
				$phonesReceived_query = $phonesReceived_query->hydrate(false);
				if(!empty($phonesReceived_query)){
					$phonesReceived = $phonesReceived_query->toArray();
				}else{
					$phonesReceived = array();
				}
				$phonesReceivedToday = $phonesReceived[0]['phonesReceivedToday'];
			//---------------------------
			$todayUserPhoneReceived_query = $this->MobileRepairLogs->find('all',array(
										'conditions' => $conditions,
										'group' => 'user_id'
												)
										 );
			$todayUserPhoneReceived_query
											->select(['total_received' => $todayUserPhoneReceived_query->func()->count('id')])
											->select('user_id');
			$todayUserPhoneReceived_query = $todayUserPhoneReceived_query->hydrate(false);
			if(!empty($todayUserPhoneReceived_query)){
				$todayUserPhoneReceived = $todayUserPhoneReceived_query->toArray();						
			}else{
				$todayUserPhoneReceived = array();
			}
			$userTodayPhoneReceived = array();
			foreach($todayUserPhoneReceived as $sngDispatch){
				$userTodayPhoneReceived[$sngDispatch['user_id']] = $sngDispatch['total_received'];
			}
			//PR($userTodayPhoneReceived);
			//---------------------------
				
				$conditions1 = array('MobileRepairLogs.repair_status'=>RECEIVED_BY_TECHNICIAN, 'Date(MobileRepairLogs.created)' => date('Y-m-d', strtotime(' -1 day')));
				$receivedYesterday_query = $this->MobileRepairLogs->find('all',array(
											'conditions' => $conditions1
											)
									 );
				$receivedYesterday_query
											->select(['phonesReceivedYesterday' => $receivedYesterday_query->func()->count('MobileRepairLogs.repair_status')]);
					$receivedYesterday_query = $receivedYesterday_query->hydrate(false);
					if(!empty($receivedYesterday_query)){
						$receivedYesterday = $receivedYesterday_query->toArray();
					}else{
						$receivedYesterday = array();
					}
			//---------------------------
			$yesterdayUserPhoneReceived_query = $this->MobileRepairLogs->find('all',array(
										'conditions' => $conditions1,
										'group' => 'user_id'
												)
										 );
			$yesterdayUserPhoneReceived_query
											->select(['total_received' => $yesterdayUserPhoneReceived_query->func()->count('id')])
											->select('user_id');
			
			$yesterdayUserPhoneReceived_query = $yesterdayUserPhoneReceived_query->hydrate(false);
			if(!empty($yesterdayUserPhoneReceived_query)){
				$yesterdayUserPhoneReceived = $yesterdayUserPhoneReceived_query->toArray();
			}else{
				$yesterdayUserPhoneReceived = array();
			}
			
			$userYesterdayPhoneReceived = array();
			foreach($yesterdayUserPhoneReceived as $sngDispatch){
                //pr($sngDispatch);die;
				$userYesterdayPhoneReceived[$sngDispatch['user_id']] = $sngDispatch;
			}
			//PR($userYesterdayPhoneReceived);
			//---------------------------
			$conditions2 = array('MobileRepairLogs.repair_status' => RECEIVED_BY_TECHNICIAN, 'Date(MobileRepairLogs.created) >= ' => date('Y-m-d', strtotime(' -30 day')));//, 'MobileRepairLog.kiosk_id' => $kiosk_id
				$receivedMonth_query = $this->MobileRepairLogs->find('all',array(
											'conditions' => $conditions2
											)
									 );
				$receivedMonth_query
									->select(['phonesReceivedMonth' => $receivedMonth_query->func()->count('MobileRepairLogs.repair_status')]);
				$receivedMonth_query = $receivedMonth_query->hydrate(false);
				if(!empty($receivedMonth_query)){
					$receivedMonth = $receivedMonth_query->toArray();
				}else{
					$receivedMonth = array();
				}
			//---------------------------
			$monthUserPhoneReceived_query = $this->MobileRepairLogs->find('all',array(
										'conditions' => $conditions2,
										'group' => 'user_id'
												)
										 );
			$monthUserPhoneReceived_query
											->select(['total_received' => $monthUserPhoneReceived_query->func()->count('id')])
											->select('user_id');
											
			$monthUserPhoneReceived_query = $monthUserPhoneReceived_query->hydrate(false);
			if(!empty($monthUserPhoneReceived_query)){
				$monthUserPhoneReceived = $monthUserPhoneReceived_query->toArray();
			}else{
				$monthUserPhoneReceived = array();
			}
			
			$userMonthPhoneReceived = array();
			//pr($monthUserPhoneReceived);die;
			foreach($monthUserPhoneReceived as $sngDispatch){
				$userMonthPhoneReceived[$sngDispatch['user_id']] = $sngDispatch;
			}
			//PR($userMonthPhoneReceived);
			//---------------------------
				$phonesReceivedYesterday = $receivedYesterday[0]['phonesReceivedYesterday'];
			$phonesReceivedMonth = $receivedMonth[0]['phonesReceivedMonth'];
				
				$this->set(compact('phonesReceivedToday', 'phonesReceivedYesterday', 'phonesReceivedMonth','userMonthPhoneReceived','userTodayPhoneReceived','userYesterdayPhoneReceived'));
    }
	
	
	 private function dispatched_2_technician(){
				$kiosk_id = $this->request->Session()->read('kiosk_id');
				$conditions = array('MobileRepairLogs.repair_status' => DISPATCHED_TO_TECHNICIAN,'Date(MobileRepairLogs.created)' => date('Y-m-d'));
				//, 'MobileRepairLog.kiosk_id'=>$kiosk_id
				$dispatchedToTechnician_query = $this->MobileRepairLogs->find('all',array(
										//'fields' => array('COUNT(MobileRepairLog.repair_status) as phonesDispatchedTechToday'),
										'conditions' => $conditions
												)
										 );
				
				$dispatchedToTechnician_query
											->select(['phonesDispatchedTechToday' => $dispatchedToTechnician_query->func()->count('MobileRepairLogs.repair_status')]);
				
				$dispatchedToTechnician_query = $dispatchedToTechnician_query->hydrate(false);
				if(!empty($dispatchedToTechnician_query)){
					$dispatchedToTechnician = $dispatchedToTechnician_query->toArray();
				}else{
					$dispatchedToTechnician = array();
				}
				
				$phonesDispatchedTechToday = $dispatchedToTechnician[0]['phonesDispatchedTechToday'];
				$todayUserPhoneDispatched_query = $this->MobileRepairLogs->find('all',array(
												//'fields' => array('count(id) as total_dispatched', 'user_id'),
												'conditions' => array('Date(MobileRepairLogs.created)' => date('Y-m-d')),
												'group' => 'user_id'
														)
												);
				
				$todayUserPhoneDispatched_query
											->select(['total_dispatched' => $todayUserPhoneDispatched_query->func()->count('id')])
											->select('user_id');		
				
				$todayUserPhoneDispatched_query = $todayUserPhoneDispatched_query->hydrate(false);
				if(!empty($todayUserPhoneDispatched_query)){
					$todayUserPhoneDispatched = $todayUserPhoneDispatched_query->toArray();
				}else{
					$todayUserPhoneDispatched = array();
				}
				$userTodayPhoneDispatched = array();
				//pr($todayUserPhoneDispatched);die;
				foreach($todayUserPhoneDispatched as $sngDispatch){
					$userTodayPhoneDispatched[$sngDispatch['user_id']] = $sngDispatch['total_dispatched'];
				}
				//pr($userTodayPhoneDispatched);
				$conditions1 = array('MobileRepairLogs.repair_status' => DISPATCHED_TO_TECHNICIAN,'Date(MobileRepairLogs.created)' => date('Y-m-d', strtotime(' -1 day')));
						//, 'MobileRepairLog.kiosk_id'=>$kiosk_id
				$dispatchedToTechYesterday_query = $this->MobileRepairLogs->find('all',array(
												//'fields' => array('COUNT(MobileRepairLog.repair_status) as phonesDispatchedTechYesterday'),
												'conditions' => $conditions1
														)
												);
				
				$dispatchedToTechYesterday_query
											->select(['phonesDispatchedTechYesterday' => $dispatchedToTechYesterday_query->func()->count('MobileRepairLogs.repair_status')]);
				
				$dispatchedToTechYesterday_query = $dispatchedToTechYesterday_query->hydrate(false);
				if(!empty($dispatchedToTechYesterday_query)){
					$dispatchedToTechYesterday = $dispatchedToTechYesterday_query->toArray();
				}else{
					$dispatchedToTechYesterday = array();
				}
				$conditions2 = array('MobileRepairLogs.repair_status' => DISPATCHED_TO_TECHNICIAN, 'Date(MobileRepairLogs.created) >= ' => date('Y-m-d', strtotime(' -30 day')));
				$dispatchedToTechMonth_query = $this->MobileRepairLogs->find('all',array(
												//'fields' => array('COUNT(MobileRepairLog.repair_status) as phonesDispatchedTechMonth'),
												'conditions' => $conditions2
														)
												);
				
				$dispatchedToTechMonth_query
											->select(['phonesDispatchedTechMonth' => $dispatchedToTechMonth_query->func()->count('MobileRepairLogs.repair_status')]);
											
				$dispatchedToTechMonth_query = $dispatchedToTechMonth_query->hydrate(false);
				if(!empty($dispatchedToTechMonth_query)){
					$dispatchedToTechMonth = $dispatchedToTechMonth_query->toArray();
				}else{
					$dispatchedToTechMonth = array();
				}
				$phonesDispatchedTechYesterday = $dispatchedToTechYesterday[0]['phonesDispatchedTechYesterday'];
				$phonesDispatchedTechMonth = $dispatchedToTechMonth[0]['phonesDispatchedTechMonth'];
				$this->set(compact('phonesDispatchedTechToday','phonesDispatchedTechYesterday', 'phonesDispatchedTechMonth'));
    }
	
	 private function dispatched_repaired(){
	    $kiosk_id = $this->request->Session()->read('kiosk_id');
	    $conditions = array('MobileRepairLogs.repair_status' => DISPATCHED_2_KIOSK_REPAIRED,'Date(MobileRepairLogs.created)' => date('Y-m-d'));
	    //, 'MobileRepairLog.kiosk_id'=>$kiosk_id
	    $phonesDispatchedRepaired_query = $this->MobileRepairLogs->find('all',array(
									   // 'fields' => array('COUNT(MobileRepairLog.repair_status) as dispatchedRepairedToday'),
									    'conditions' => $conditions
										)
								     );
		$phonesDispatchedRepaired_query
											->select(['dispatchedRepairedToday' => $phonesDispatchedRepaired_query->func()->count('MobileRepairLogs.repair_status')]);
		
		$phonesDispatchedRepaired_query = $phonesDispatchedRepaired_query->hydrate(false);
		if(!empty($phonesDispatchedRepaired_query)){
			$phonesDispatchedRepaired = $phonesDispatchedRepaired_query->first();
		}else{
			$phonesDispatchedRepaired = array();
		}
	    $dispatchedRepairedToday = $phonesDispatchedRepaired['dispatchedRepairedToday'];
		//-------------------------------start----------------------
		$todayPhoneRepaired_query = $this->MobileRepairLogs->find('all',array(
									//'fields' => array('count(id) as total_repaired', 'user_id'),
									'conditions' => $conditions,
									'group' => 'user_id'
									    )
								   );
		
		$todayPhoneRepaired_query
											->select(['total_repaired' => $todayPhoneRepaired_query->func()->count('id')])
											->select('user_id');		
		
		$todayPhoneRepaired_query = $todayPhoneRepaired_query->hydrate(false);
		if(!empty($todayPhoneRepaired_query)){
			$todayPhoneRepaired = $todayPhoneRepaired_query->toArray();
		}else{
			$todayPhoneRepaired = array();
		}
		
		
		$userTodayPhoneRepaired = array();
		foreach($todayPhoneRepaired as $sngDispatch){
			$userTodayPhoneRepaired[$sngDispatch['user_id']] = $sngDispatch['total_repaired'];
		}
		//-------------------------------end------------------------
	    
	    $conditions1 = array('MobileRepairLogs.repair_status'=>DISPATCHED_2_KIOSK_REPAIRED,'Date(MobileRepairLogs.created)'=>date('Y-m-d', strtotime(' -1 day')));//, 'MobileRepairLog.kiosk_id'=>$kiosk_id
	    $phonesDispatchedRepairedYesterday_query = $this->MobileRepairLogs->find('all',array(
									   // 'fields' => array('COUNT(MobileRepairLog.repair_status) as dispatchedRepairedYesterday'),
									    'conditions' => $conditions1
										)
								     );
		
		$phonesDispatchedRepairedYesterday_query
											->select(['dispatchedRepairedYesterday' => $phonesDispatchedRepairedYesterday_query->func()->count('MobileRepairLogs.repair_status')]);
		
		$phonesDispatchedRepairedYesterday_query = $phonesDispatchedRepairedYesterday_query->hydrate(false);
		if(!empty($phonesDispatchedRepairedYesterday_query)){
			$phonesDispatchedRepairedYesterday = $phonesDispatchedRepairedYesterday_query->first();
		}else{
			$phonesDispatchedRepairedYesterday = array();
		}
		//-------------------------------start---------------
		$yesterdayPhoneRepaired_query = $this->MobileRepairLogs->find('all',array(
									//'fields' => array('count(id) as total_repaired', 'user_id'),
									'conditions' => $conditions1,
									'group' => 'user_id'
									    )
								   );
		
		$yesterdayPhoneRepaired_query
											->select(['total_repaired' => $yesterdayPhoneRepaired_query->func()->count('id')])
											->select('user_id');		
		
		$yesterdayPhoneRepaired_query = $yesterdayPhoneRepaired_query->hydrate(false);
		if(!empty($yesterdayPhoneRepaired_query)){
			$yesterdayPhoneRepaired = $yesterdayPhoneRepaired_query->toArray();
		}else{
			$yesterdayPhoneRepaired = array();
		}
		$userYesterdayPhoneRepaired = array();
		foreach($yesterdayPhoneRepaired as $sngDispatch){
			$userYesterdayPhoneRepaired[$sngDispatch['user_id']] = $sngDispatch['total_repaired'];
		}
		//-------------------------------end---------------
		$conditions2 = array('MobileRepairLogs.repair_status' => DISPATCHED_2_KIOSK_REPAIRED,'Date(MobileRepairLogs.created) >= '=>date('Y-m-d', strtotime(' -30 day')));
	    $phonesDispatchedRepairedMonth_query = $this->MobileRepairLogs->find('all',array(
									   // 'fields' => array('COUNT(MobileRepairLog.repair_status) as dispatchedRepairedMonth'),
									    'conditions' => $conditions2
										)
								     );
		
		$phonesDispatchedRepairedMonth_query
											->select(['dispatchedRepairedMonth' => $phonesDispatchedRepairedMonth_query->func()->count('MobileRepairLogs.repair_status')]);
		
		$phonesDispatchedRepairedMonth_query = $phonesDispatchedRepairedMonth_query->hydrate(false);
		if(!empty($phonesDispatchedRepairedMonth_query)){
			$phonesDispatchedRepairedMonth = $phonesDispatchedRepairedMonth_query->first();
		}else{
			$phonesDispatchedRepairedMonth = array();
		}
		
		//-------------------------------start---------------
		$monthPhoneRepaired_query = $this->MobileRepairLogs->find('all',array(
									//'fields' => array('count(id) as total_repaired', 'user_id'),
									'conditions' => $conditions2,
									'group' => 'user_id'
									    )
								   );
		
		$monthPhoneRepaired_query
											->select(['total_repaired' => $monthPhoneRepaired_query->func()->count('id')])
											->select('user_id');		
		
		$monthPhoneRepaired_query = $monthPhoneRepaired_query->hydrate(false);
		if(!empty($monthPhoneRepaired_query)){
			$monthPhoneRepaired = $monthPhoneRepaired_query->toArray();
		}else{
			$monthPhoneRepaired = array();
		}
		$userMonthPhoneRepaired = array();
		foreach($monthPhoneRepaired as $sngDispatch){
			$userMonthPhoneRepaired[$sngDispatch['user_id']] = $sngDispatch['total_repaired'];
		}
		//-------------------------------end---------------
		
	    $dispatchedRepairedYesterday = $phonesDispatchedRepairedYesterday['dispatchedRepairedYesterday'];
		$dispatchedRepairedMonth = $phonesDispatchedRepairedMonth['dispatchedRepairedMonth'];
	    $this->set(compact('dispatchedRepairedToday','dispatchedRepairedYesterday', 'dispatchedRepairedMonth','userTodayPhoneRepaired','userYesterdayPhoneRepaired', 'userMonthPhoneRepaired'));
    }
	 private function dispatched_unrepaired(){
		$kiosk_id = $this->request->Session()->read('kiosk_id');
	    $conditions = array('MobileRepairLogs.repair_status' => DISPATCHED_2_KIOSK_UNREPAIRED,'Date(MobileRepairLogs.created)' => date('Y-m-d'));
		//, 'MobileRepairLog.kiosk_id'=>$kiosk_id
	    $phonesDispatchedUnrepaired_query = $this->MobileRepairLogs->find('all',array(
										//'fields' => array('COUNT(MobileRepairLog.repair_status) as dispatchedUnrepairedToday'),
										'conditions' => $conditions
									    )
								       );
		
		$phonesDispatchedUnrepaired_query
											->select(['dispatchedUnrepairedToday' => $phonesDispatchedUnrepaired_query->func()->count('MobileRepairLogs.repair_status')]);
		
		$phonesDispatchedUnrepaired_query = $phonesDispatchedUnrepaired_query->hydrate(false);
		if(!empty($phonesDispatchedUnrepaired_query)){
			$phonesDispatchedUnrepaired = $phonesDispatchedUnrepaired_query->first();
		}else{
			$phonesDispatchedUnrepaired = array();
		}
	    $dispatchedUnrepairedToday = $phonesDispatchedUnrepaired['dispatchedUnrepairedToday'];
		
		//-------------------------------start----------------------
		$todayPhoneUnrepaired_query = $this->MobileRepairLogs->find('all',array(
									//'fields' => array('count(id) as total_unrepaired', 'user_id'),
									'conditions' => $conditions,
									'group' => 'user_id'
									    )
								   );
		
		$todayPhoneUnrepaired_query
											->select(['total_unrepaired' => $todayPhoneUnrepaired_query->func()->count('id')])
											->select('user_id');
											
		$todayPhoneUnrepaired_query = $todayPhoneUnrepaired_query->hydrate(false);
		if(!empty($todayPhoneUnrepaired_query)){
			$todayPhoneUnrepaired = $todayPhoneUnrepaired_query->toArray();
		}else{
			$todayPhoneUnrepaired = array();
		}
		$userTodayPhoneUnrepaired = array();
		foreach($todayPhoneUnrepaired as $sngDispatch){
			$userTodayPhoneUnrepaired[$sngDispatch['user_id']] = $sngDispatch['total_unrepaired'];
		}
		//-------------------------------end------------------------
	    
	    $conditions1 = array('MobileRepairLogs.repair_status'=>DISPATCHED_2_KIOSK_UNREPAIRED,'Date(MobileRepairLogs.created)'=>date('Y-m-d', strtotime(' -1 day')));//, 'MobileRepairLog.kiosk_id'=>$kiosk_id
	    $phonesDispatchedUnrepairedYesterday_query = $this->MobileRepairLogs->find('all',array(
										//'fields' => array('COUNT(MobileRepairLog.repair_status) as dispatchedUnrepairedYesterday'),
										'conditions' => $conditions1
									    )
								       );
		
		$phonesDispatchedUnrepairedYesterday_query
											->select(['dispatchedUnrepairedYesterday' => $phonesDispatchedUnrepairedYesterday_query->func()->count('MobileRepairLogs.repair_status')]);
											
		$phonesDispatchedUnrepairedYesterday_query = $phonesDispatchedUnrepairedYesterday_query->hydrate(false);
		if(!empty($phonesDispatchedUnrepairedYesterday_query)){
			$phonesDispatchedUnrepairedYesterday = $phonesDispatchedUnrepairedYesterday_query->first();
		}else{
			$phonesDispatchedUnrepairedYesterday = array();
		}
		
		//-------------------------------start----------------------
		$yesterdayPhoneUnrepaired_query = $this->MobileRepairLogs->find('all',array(
									//'fields' => array('count(id) as total_unrepaired', 'user_id'),
									'conditions' => $conditions1,
									'group' => 'user_id'
									    )
								   );
		
		$yesterdayPhoneUnrepaired_query
											->select(['total_unrepaired' => $yesterdayPhoneUnrepaired_query->func()->count('id')])
											->select('user_id');
		
		$yesterdayPhoneUnrepaired_query = $yesterdayPhoneUnrepaired_query->hydrate(false);
		if(!empty($yesterdayPhoneUnrepaired_query)){
			$yesterdayPhoneUnrepaired = $yesterdayPhoneUnrepaired_query->toArray();
		}else{
			$yesterdayPhoneUnrepaired = array();
		}
		
		$userYesterdayPhoneUnrepaired = array();
		foreach($yesterdayPhoneUnrepaired as $sngDispatch){
			$userYesterdayPhoneUnrepaired[$sngDispatch['user_id']] = $sngDispatch['total_unrepaired'];
		}
		//-------------------------------end------------------------
		
		$conditions2 = array('MobileRepairLogs.repair_status' => DISPATCHED_2_KIOSK_UNREPAIRED, 'Date(MobileRepairLogs.created) >= ' => date('Y-m-d', strtotime(' -30 day')));
	    $phonesDispatchedUnrepairedMonth_query = $this->MobileRepairLogs->find('all',array(
										//'fields' => array('COUNT(MobileRepairLog.repair_status) as dispatchedUnrepairedMonth'),
										'conditions' => $conditions2
									    )
								       );
		
		$phonesDispatchedUnrepairedMonth_query
											->select(['dispatchedUnrepairedMonth' => $phonesDispatchedUnrepairedMonth_query->func()->count('MobileRepairLogs.repair_status')]);
		
		$phonesDispatchedUnrepairedMonth_query = $phonesDispatchedUnrepairedMonth_query->hydrate(false);
		if(!empty($phonesDispatchedUnrepairedMonth_query)){
			$phonesDispatchedUnrepairedMonth = $phonesDispatchedUnrepairedMonth_query->first();
		}else{
			$phonesDispatchedUnrepairedMonth = array();
		}
		
		//-------------------------------start----------------------
		$monthPhoneUnrepaired_query = $this->MobileRepairLogs->find('all',array(
									//'fields' => array('count(id) as total_unrepaired', 'user_id'),
									'conditions' => $conditions2,
									'group' => 'user_id'
									    )
								   );
		
		$monthPhoneUnrepaired_query
											->select(['total_unrepaired' => $monthPhoneUnrepaired_query->func()->count('id')])
											->select('user_id');
		
		$monthPhoneUnrepaired_query = $monthPhoneUnrepaired_query->hydrate(false);
		if(!empty($monthPhoneUnrepaired_query)){
			$monthPhoneUnrepaired = $monthPhoneUnrepaired_query->toArray();
		}else{
			$monthPhoneUnrepaired = array();
		}
		
		$userMonthPhoneUnrepaired = array();
		foreach($monthPhoneUnrepaired as $sngDispatch){
			$userMonthPhoneUnrepaired[$sngDispatch['user_id']] = $sngDispatch['total_unrepaired'];
		}
		//-------------------------------end------------------------
		
	    $dispatchedUnrepairedYesterday = $phonesDispatchedUnrepairedYesterday['dispatchedUnrepairedYesterday'];
		$dispatchedUnrepairedMonth = $phonesDispatchedUnrepairedMonth['dispatchedUnrepairedMonth'];
	    $this->set(compact('dispatchedUnrepairedToday', 'dispatchedUnrepairedYesterday', 'dispatchedUnrepairedMonth', 'userTodayPhoneUnrepaired', 'userYesterdayPhoneUnrepaired', 'userMonthPhoneUnrepaired'));
    }
    
    public function comments4ServiceCenter(){
        $users_query = $this->Users->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'username',
                                                'conditions' => ['Users.group_id IN' => [KIOSK_USERS, ADMINISTRATORS, MANAGERS]]
                                            ]
                                            );
        $users_query = $users_query->hydrate(false);
        if(!empty($users_query)){
            $users = $users_query->toArray();
        }else{
            $users = array();
        }
        $kiosks_query = $this->Kiosks->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'name',
                                                'conditions' => ['kiosk_type' => 1],
                                                'order' => ['Kiosks.name asc']
                                             ]
                                      );
        $kiosks_query = $kiosks_query->hydrate(false);
        if(!empty($kiosks_query)){
            $kiosks = $kiosks_query->toArray();
        }else{
            $kiosks = array();
        }
        $this->paginate = [
                            'conditions' => [
                                    'CommentMobileRepairs.user_id IN' => array_keys($users),
                                    ['DATE(CommentMobileRepairs.created) >= DATE_ADD(CURDATE(),INTERVAL -20 DAY)']
                                ],
                            'contain' => ['MobileRepairs','Users'],
                            'order' => ['CommentMobileRepairs.id desc'],
                            'limit' => 50//as directed
                        ];
        $comments = $this->paginate('CommentMobileRepairs');
        $this->set(compact('comments','kiosks'));
    }
	
	public function home(){
		//function for all users
    }
	
	
	 public function searchComments4ServiceCenter(){
	$users_query = $this->Users->find('list',array('keyField' => 'id',
											 'valueField' => 'username',
											  'conditions' => array('Users.group_id IN' => array(KIOSK_USERS, ADMINISTRATORS, MANAGERS))));
	$users_query = $users_query->hydrate(false);
	if(!empty($users_query)){
		$users = $users_query->toArray();
	}else{
		$users = array();
	}
	$kiosks_query = $this->Kiosks->find('list',array(
													 'keyField' => 'id',
													 'valueField' => 'name',
													  'conditions' => array('kiosk_type' => 1), 'order' => 'Kiosks.name asc'));
	
	$kiosks_query = $kiosks_query->hydrate(false);
	if(!empty($kiosks_query)){
		$kiosks = $kiosks_query->toArray();
	}else{
		$kiosks = array();
	}
	
	$conditionArr = array();
	$id = $this->request->query['id'];
	if(!empty($id)){
	    $conditionArr[] = array('CommentMobileRepairs.mobile_repair_id' => $id);
	}
	$start_date = $this->request->query['start_date'];
	if(!empty($start_date)){
	    $conditionArr[] = array("DATE(CommentMobileRepairs.created) >=" => date('Y-m-d',strtotime($start_date)));
	}
	$end_date = $this->request->query['end_date'];
	if(!empty($end_date)){
	    $conditionArr[] = array("DATE(CommentMobileRepairs.created) <=" => date('Y-m-d',strtotime($end_date)));
	}
	
	if(!array_key_exists('DATE(CommentMobileRepairs.created) >=',array_values($conditionArr))){
	    //if no date is chosen will show data starting from 20 days from current date as per the original page
	    $conditionArr[] = array('DATE(CommentMobileRepairs.created) >= DATE_ADD(CURDATE(),INTERVAL -20 DAY)');
	}
	
	$kiosk = '';
	if(array_key_exists('kiosk',$this->request->query)){
	    $kiosk = $this->request->query['kiosk'];
	}
	
	if(!empty($kiosk) &&//only unlock technician
	   ($this->request->session()->read('Auth.User.group_id') == REPAIR_TECHNICIANS)){
	    $conditionArr[] = array('MobileRepairs.kiosk_id' => $kiosk);
	}
	if(count($conditionArr)){
	    $this->paginate = array(
				    'conditions' => array(
						'CommentMobileRepairs.user_id IN' => array_keys($users),
						$conditionArr),
				    'order' => ['CommentMobileRepairs.id desc'],
					    'limit' => 100,//as directed
						'contain' => ['MobileRepairs']
			    );
	}else{
	    $this->paginate = array(
					'conditions' => array(
						    'CommentMobileRepairs.user_id IN' => array_keys($users),
						    array('DATE(CommentMobileRepairs.created) >= DATE_ADD(CURDATE(),INTERVAL -20 DAY)')
						    ),
					'order' => ['CommentMobileRepairs.id desc'],
						'limit' => 100,//as directed
						'contain' => ['MobileRepairs']
				);
	}
	
	$comments = $this->paginate('CommentMobileRepairs');
	$this->set(compact('comments','kiosks'));
	$this->render('comments4_service_center');
    }
	
	public function prv_credit_to_card($reciptTable,$paymentDetailTable){
		$credit_to_other_changed  = array(
											'credit' => 0,
											'cash' => 0,
											'bank_transfer' => 0,
											'cheque' => 0,
											'card' => 0,
											'misc' => 0								  
										  );
		if(true){  //$kioskId == 0
			$result_query =  $paymentDetailTable->find('all',array('conditions' => array('date(created)' => date('Y-m-d', strtotime(' -1 day'))),
												   )
									   );
			$result_query = $result_query->hydrate(false);
			if(!empty($result_query)){
				$result = $result_query->toArray();
			}else{
				$result = array();
			}
			$recipt_ids = array();
            foreach($result as $key_s => $value_s){
                $recipt_ids[] = $value_s['product_receipt_id'];    
            }
            if(empty($recipt_ids)){
                $recipt_ids = array(0=>null);
            }
            $recipt_res_query = $reciptTable->find("list",array(
                                                                         'keyField' => 'id',
                                                                         'valueField' => 'created',
                                                                         'conditions' => array("id IN" => $recipt_ids)
                                                                         )
                                                             );
            $recipt_res_query = $recipt_res_query->hydrate(false);
			if(!empty($recipt_res_query)){
				$recipt_res = $recipt_res_query->toArray();
			}else{
				$recipt_res = array();
			}
			
			
			$onCreditAmt = $cashAmt = $cardAmt = $bnkTrnAmt = $chkAmt = $miscAmt = 0;
			foreach($result as $s => $val){
				$pay_time = strtotime(date("Y-m-d",strtotime($val['created'])));
				if($val['product_receipt_id'] == 0){
					//pr($paymentDetailTable);
					//pr($result);die;
				}
				if(array_key_exists($val['product_receipt_id'],$recipt_res)){
					$recit_time = strtotime(date("Y-m-d",strtotime($recipt_res[$val['product_receipt_id']])));//strtotime(date("Y-m-d",strtotime($val['created'])));	
				}else{
					$recit_time = '';
				}
				
				if($pay_time != $recit_time){
					$pmtAmt1 = $val['amount'];
					$pay_method = $val['payment_method'];
					switch($pay_method){
						case 'On Credit':
							$onCreditAmt += $pmtAmt1;
							break;
						case 'Cash':
							$cashAmt += $pmtAmt1;
							break;
						case 'Bank Transfer':
							$bnkTrnAmt += $pmtAmt1;
							break;
						case 'Cheque':
							$chkAmt += $pmtAmt1;
							break;
						case 'Card':
							$cardAmt += $pmtAmt1;
							break;
						case 'Misc':
							$miscAmt += $pmtAmt1;
							break;
					}
				}
			}
			return $credit_to_other_changed = array('credit' => $onCreditAmt,'cash' => $cashAmt, 'bank_transfer' => $bnkTrnAmt, 'cheque' => $chkAmt, 'card' => $cardAmt, 'misc' => $miscAmt);
		}
	}
	
	public function t_prv_credit_to_card($kioskId){
		
		$saleSource = "t_kiosk_product_sales";
		$productReceiptSource = "t_product_receipts";
		$paymentDetailSource = "t_payment_details";
		
		 $reciptTable = TableRegistry::get($productReceiptSource,[
                                                                            'table' => $productReceiptSource,
                                                                        ]);
		//$this->KioskProductSales->setSource($saleSource);
        $KioskProductSalesTable = TableRegistry::get($saleSource,[
                                                                    'table' => $saleSource,
                                                                ]);
		
		$PaymentDetailsTable = TableRegistry::get($paymentDetailSource,[
                                                                    'table' => $paymentDetailSource,
                                                                ]);
		$credit_to_other_changed  = array(
											'credit' => 0,
											'cash' => 0,
											'bank_transfer' => 0,
											'cheque' => 0,
											'card' => 0,
											'misc' => 0								  
										  );
		if(true){  //$kioskId == 0
			$result_query =  $PaymentDetailsTable->find('all',array('conditions' => array('kiosk_id' =>$kioskId ,'date(created)' => date('Y-m-d', strtotime(' -1 day'))),
												   )
									   );
			$result_query = $result_query->hydrate(false);
			if(!empty($result_query)){
				$result = $result_query->toArray();
			}else{
				$result = array();
			}
			
			$recipt_ids = array();
            foreach($result as $key_s => $value_s){
                $recipt_ids[] = $value_s['product_receipt_id'];    
            }
            if(empty($recipt_ids)){
                $recipt_ids = array(0=>null);
            }
            $recipt_res_query = $reciptTable->find("list",array(
                                                                         'keyField' => 'id',
                                                                         'valueField' => 'created',
                                                                         'conditions' => array("id IN" => $recipt_ids)
                                                                         )
                                                             );
            $recipt_res_query = $recipt_res_query->hydrate(false);
			if(!empty($recipt_res_query)){
				$recipt_res = $recipt_res_query->toArray();
			}else{
				$recipt_res = array();
			}
			
			$onCreditAmt = $cashAmt = $cardAmt = $bnkTrnAmt = $chkAmt = $miscAmt = 0;
			foreach($result as $s => $val){
				$pay_time = strtotime(date("Y-m-d",strtotime($val['created'])));
				if(array_key_exists($val['product_receipt_id'],$recipt_res)){
					$recit_time = strtotime(date("Y-m-d",strtotime($recipt_res[$val['product_receipt_id']])));	
				}else{
					$recit_time = "";
				}
				
				//$recit_time = strtotime(date("Y-m-d",strtotime($val['created'])));
				if($pay_time != $recit_time){
					$pmtAmt1 = $val['amount'];
					$pay_method = $val['payment_method'];
					switch($pay_method){
						case 'On Credit':
							$onCreditAmt += $pmtAmt1;
							break;
						case 'Cash':
							$cashAmt += $pmtAmt1;
							break;
						case 'Bank Transfer':
							$bnkTrnAmt += $pmtAmt1;
							break;
						case 'Cheque':
							$chkAmt += $pmtAmt1;
							break;
						case 'Card':
							$cardAmt += $pmtAmt1;
							break;
						case 'Misc':
							$miscAmt += $pmtAmt1;
							break;
					}
				}
			}
			return $credit_to_other_changed = array('credit' => $onCreditAmt,'cash' => $cashAmt, 'bank_transfer' => $bnkTrnAmt, 'cheque' => $chkAmt, 'card' => $cardAmt, 'misc' => $miscAmt);
		}
	}
	
	public function custom_month_credit_to_card($start,$end,$kioskId,$source = 1){
		if($source == 1){
			if(empty($kioskId)){
				$saleSource = "kiosk_product_sales";
				$productReceiptSource = "product_receipts";
				$PaymentDetails_source = "payment_details";
			}else{
				$saleSource = "kiosk_{$kioskId}_product_sales";
				$productReceiptSource = "kiosk_{$kioskId}_product_receipts";
				$PaymentDetails_source = "kiosk_{$kioskId}_payment_details";
			}	
		}else{
			if((int)$kioskId == 0){
				$saleSource = "t_kiosk_product_sales";
				$productReceiptSource = "t_product_receipts";
				$PaymentDetails_source = "t_payment_details";
				$invoiceOrderSource = "invoice_orders";
				$todSale = 0;
			}else{
				$saleSource = "t_kiosk_product_sales";
				$productReceiptSource = "t_product_receipts";
				$PaymentDetails_source = "t_payment_details";
				$invoiceOrderSource = "kiosk_{$kioskId}_invoice_orders";
			}	
		}
		
        
        
         $PaymentDetailsTable = TableRegistry::get($PaymentDetails_source,[
                                                                    'table' => $PaymentDetails_source,
                                                                ]);
         $reciptTable = TableRegistry::get($productReceiptSource,[
                                                                    'table' => $productReceiptSource,
                                                                ]);
		$credit_to_other_changed  = array(
											'credit' => 0,
											'cash' => 0,
											'bank_transfer' => 0,
											'cheque' => 0,
											'card' => 0,
											'misc' => 0								  
										  );
		if(true){  //$kioskId == 0
			if($source == 1){
				$result_query =  $PaymentDetailsTable->find('all',array('conditions' => array("(date(created) BETWEEN '$start' AND '$end')",'product_receipt_id >' => 0),
																		 )
									   );
			}else{
				$result_query =  $PaymentDetailsTable->find('all',array('conditions' => array('kiosk_id' => $kioskId,"(date(created) BETWEEN '$start' AND '$end')",'product_receipt_id >' => 0),
																		 )
									   );
			}
			
												  
			$result_query = $result_query->hydrate(false);
			if(!empty($result_query)){
				$result = $result_query->toArray();
			}else{
				$result = array();
			}
            $recipt_ids = array();
            foreach($result as $key_s => $value_s){
                $recipt_ids[] = $value_s['product_receipt_id'];    
            }
            if(empty($recipt_ids)){
                $recipt_ids = array(0=>null);
            }
            $recipt_res_query = $reciptTable->find("list",array(
                                                                         'keyField' => 'id',
                                                                         'valueField' => 'created',
                                                                         'conditions' => array("id IN" => $recipt_ids)
                                                                         )
                                                             );
            $recipt_res_query = $recipt_res_query->hydrate(false);
			if(!empty($recipt_res_query)){
				$recipt_res = $recipt_res_query->toArray();
			}else{
				$recipt_res = array();
			}
            //pr($recipt_res);die; 
			$onCreditAmt = $cashAmt = $cardAmt = $bnkTrnAmt = $chkAmt = $miscAmt = 0;
			foreach($result as $s => $val){
				$pay_time = strtotime(date("Y-m-d",strtotime($val['created'])));
				if(array_key_exists($val['product_receipt_id'],$recipt_res)){
					$recit_time = strtotime(date("Y-m-d",strtotime($recipt_res[$val['product_receipt_id']])));	
				}else{
					$recit_time = strtotime(date("Y-m-d"));
				}
				
				if($pay_time != $recit_time){
					$pmtAmt1 = $val['amount'];
					$pay_method = $val['payment_method'];
					switch($pay_method){
						case 'On Credit':
							$onCreditAmt += $pmtAmt1;
							break;
						case 'Cash':
							$cashAmt += $pmtAmt1;
							break;
						case 'Bank Transfer':
							$bnkTrnAmt += $pmtAmt1;
							break;
						case 'Cheque':
							$chkAmt += $pmtAmt1;
							break;
						case 'Card':
							$cardAmt += $pmtAmt1;
							break;
						case 'Misc':
							$miscAmt += $pmtAmt1;
							break;
					}
				}
			}
			return $credit_to_other_changed = array('credit' => $onCreditAmt,'cash' => $cashAmt, 'bank_transfer' => $bnkTrnAmt, 'cheque' => $chkAmt, 'card' => $cardAmt, 'misc' => $miscAmt);
		}
	}
	
	private function get_t_credit_refund($kskId = '', $dte = ''){
		//echo'hi';die;
		$pmtTypes = Configure::read('payment_type');
		if($kskId){
			//for monthly_kiosk_sale page
			$kioskId = $kskId;
		}else{
			$kiosk_id = $this->request->Session()->read('kiosk_id');
			if(!empty($this->request->params['pass']) && array_key_exists(0,$this->request->params['pass'])){
				//for admin kiosk_total_sale
				$kioskId = $this->request->params['pass'][0];
			}elseif((int)$kiosk_id){
				$kioskId = $kiosk_id;
			}else{
				$kioskId = 0;
			}
		}
		
		$date = date('Y-m-d');		
		if($dte){$date = $dte;}
		
		if((int)$kioskId == 0){
			$creditNotePmtSource = "t_credit_payment_details";
			$creditNoteReceiptSource = "t_credit_receipts";
		}else{
			$creditNotePmtSource = "t_credit_payment_details";
			$creditNoteReceiptSource = "t_credit_receipts";
		}
				
		//$this->CreditPaymentDetail->setSource($creditNotePmtSource);
		//$this->CreditReceipt->setSource($creditNoteReceiptSource);
		
		//get product sale
		
			$creditNoteRawQry = "SELECT  `CreditPaymentDetail`.id,`CreditPaymentDetail`.credit_receipt_id,
									`CreditPaymentDetail`.payment_method,`CreditPaymentDetail`.description,
									`CreditPaymentDetail`.amount,`CreditPaymentDetail`.payment_status,
									`CreditPaymentDetail`.status,`CreditPaymentDetail`.created as payCreated, `CreditReceipt`.*
										  FROM
										  `$creditNotePmtSource` AS `CreditPaymentDetail`
										  LEFT JOIN
										  `$creditNoteReceiptSource` AS `CreditReceipt`
										  ON (`CreditPaymentDetail`.`credit_receipt_id` = `CreditReceipt`.`id`)
										  WHERE (
													%s AND %s
												)
										  ORDER BY `CreditPaymentDetail`.`id`";
		
		
			$creditNoteQry = sprintf($creditNoteRawQry, "(DATE(`CreditPaymentDetail`.`created`) = '$date')","(`CreditReceipt`.`kiosk_id` = '$kioskId')");	
		
		//echo $creditNoteQry;
		//capturing product receipts in an array for today
		$conn = ConnectionManager::get('default');
        $stmt = $conn->execute($creditNoteQry);
        $todayCreditNote = $stmt->fetchAll('assoc');
		
		//$todayCreditNote = $this->CreditPaymentDetail->query($creditNoteQry);
		list($t_todaysCNSale,$t_today_credit_to_other_changes_CN) = $this->getCreditNoteSale($todayCreditNote);
		//pr($t_todaysCNSale);die;
		//yesterday credits
		$yesterday = date('Y-m-d', strtotime(' -1 day'));
		//$creditNoteQry = sprintf($creditNoteRawQry, "(DATE(`CreditPaymentDetail`.`created`) = '$yesterday')");
		
		$creditNoteQry = sprintf($creditNoteRawQry, "(DATE(`CreditPaymentDetail`.`created`) = '$yesterday')","(`CreditReceipt`.`kiosk_id` = '$kioskId')");
		
		
		$conn = ConnectionManager::get('default');
        $stmt = $conn->execute($creditNoteQry);
        $yesterdayCreditNote = $stmt->fetchAll('assoc');
		
		//$yesterdayCreditNote = $this->CreditPaymentDetail->query($creditNoteQry);
		list($t_yesterdayCNSale,$t_y_credit_to_other_changes_CN) = $this->getCreditNoteSale($yesterdayCreditNote);
        //pr($t_yesterdayCNSale);die;
		//-----------------------------------------------------
		$currentDate = date('Y-m-d',strtotime(' +1 day'));
		//adding an extra day to get correct result of current month
		$monthStart = date('Y-m-1');
		$month_ini = new \DateTime("first day of last month"); //using datetime class of PHP
		$month_end = new \DateTime("last day of last month");
		$previousMonthStart = $month_ini->format('Y-m-d');
		$previousMonthEnd = $month_end->format('Y-m-d');
		$previousMonthEndPlus = date('Y-m-d',strtotime($previousMonthEnd.' +1 day'));
		//adding one more day to last month to get correct data from db for last month
		//-----------------------------------------------------
		//current month credits
		
		
			$creditNoteQry = sprintf($creditNoteRawQry, "`CreditPaymentDetail`.`created` BETWEEN '$monthStart' AND '$currentDate'","(`CreditReceipt`.`kiosk_id` = '$kioskId')");
		
		
		$conn = ConnectionManager::get('default');
        $stmt = $conn->execute($creditNoteQry);
        $currMonthCreditNote = $stmt->fetchAll('assoc');
		
		//$currMonthCreditNote = $this->CreditPaymentDetail->query($creditNoteQry);
		list($t_currMonthCNSale,$t_cm_credit_to_other_changes_CN) = $this->getCreditNoteSale($currMonthCreditNote);
		//current month credits
		
		
			$creditNoteQry = sprintf($creditNoteRawQry, "`CreditPaymentDetail`.`created` BETWEEN '$previousMonthStart' AND '$previousMonthEndPlus'","(`CreditReceipt`.`kiosk_id` = '$kioskId')");
		
		
		$conn = ConnectionManager::get('default');
        $stmt = $conn->execute($creditNoteQry);
        $prevMonthCreditNote = $stmt->fetchAll('assoc');
		
		//$prevMonthCreditNote = $this->CreditPaymentDetail->query($creditNoteQry);
		list($t_prevMonthCNSale,$t_pm_credit_to_other_changes_CN) = $this->getCreditNoteSale($prevMonthCreditNote);
		
		//pr($t_yesterdayCNSale);
		$this->set(compact('t_todaysCNSale', 't_yesterdayCNSale', 't_currMonthCNSale', 't_prevMonthCNSale',
						   't_today_credit_to_other_changes_CN','t_y_credit_to_other_changes_CN','t_cm_credit_to_other_changes_CN',
						   't_pm_credit_to_other_changes_CN'
						   ));
    }
    
    public function bulkCredit($customerID = 0){
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
		$this->set(compact('agents'));
		
		
		//pr($this->request);
        $vat = isset($this->setting['vat']) && !empty($this->setting['vat']) ? $this->setting['vat'] : 0;
		$customer_query = $this->Customers->find('all', array(
														 'conditions' => array('id' => (int)$customerID),
														 'recursive' => -1,
														 )
										  );
        $customer_query = $customer_query->hydrate(false);
        if(!empty($customer_query)){
            $customer = $customer_query->First();
        }
      
		if(count($customer) < 1){
           $flashMessage = "Invalid customer id";
            $this->Flash->success(__($flashMessage));
			$this->redirect(array('controller' => 'customers','action' => "index"));
		}
		if(!empty($customer)){
            $this->request->Session()->write('cr_customer',$customer ); 
		}
		//-----------------------------------------
         $Category_query = $this->Categories->find('all',array(
								'fields' => array('id', 'category','id_name_path'),
								'conditions' => array('Categories.status' => 1),
								'order' => 'Categories.category asc' 
								));
        if(!empty($Category_query)){
              $categories = $Category_query->toArray();
        }
		 
		$categories = $this->CustomOptions->category_options($categories,true);
		$this->set(compact('categories','customer','vat'));
	}
    private function cr_reorder_cart($quick_cart=array(), $prodErrArr = array()){
		$items = $sortedItems = $posArr = array();
       if(array_key_exists("new_sale_basket",$quick_cart)){
            $items = $quick_cart['new_sale_basket'];
       }
      // pr($items); 
		if(!is_array($items)){
			$items = array();
		}else{
            
			foreach($items as $item){ $posArr[] = $item['position']; }
			rsort($posArr);
		 
			foreach($posArr as $pos){
				foreach($items as $key => $item){
					if($item['position'] == $pos){
						$sortedItems[$key] = $item;
						unset($items[$key]);
						break;
					}
				}
			}
			$quick_cart['new_sale_basket'] = $sortedItems;
			$this->request->Session()->write('cr_quick_cart',$quick_cart);
		}
		
		//pr($_SESSION);die;
		//$quick_cart['new_sale_basket'] = array($sortedItems);
		$cart = $quick_cart;
		if(count($prodErrArr)){
			$quick_cart['prodError'] = $prodErrArr['error'];
			echo $quickCart = json_encode($quick_cart);
		}else{
			echo $quickCart = json_encode($quick_cart);
		}
		$serilized_data = serialize($cart);
      //  pr($serilized_data);die;
		//--------------------------------
		if(count($sortedItems) >= 1){
			$kiosk_id = $this->request->Session()->read('kiosk_id');
			if($kiosk_id == ""){
				$kiosk_id = 10000;
			}
			$sessionRS_query = $this->SessionBackups->find('all', array('conditions' => array(
																				'controller' => 'Home',
																				'action' => 'cr_reorder_cart',
																				'kiosk_id' => $kiosk_id,
																				'user_id' => $this->request->session()->read('Auth.User.id'),
																			)));
             $sessionRS_query->hydrate(false);
             if(!empty($sessionRS_query)){
                $sessionRS = $sessionRS_query->first();
             }else{
                $sessionRS = array();
             }
        
            
			if(count($sessionRS) >= 1){
				//update record
                //pr($sessionRS);die;
				$SessionBackupsid = $sessionRS['id']; 
				$data = array('session_detail' => $serilized_data);
                $sessionRSEntity = $this->SessionBackups->get($SessionBackupsid);
				$data = $this->SessionBackups->patchEntity($sessionRSEntity, $data);
				$this->SessionBackups->save($data);
			}else{
				//add record
				$kiosk_id = $this->request->Session()->read('kiosk_id');
				if($kiosk_id == ""){
					$kiosk_id = 10000;
				}
				$sessionBackupData = array(
										'controller' => 'Home',
										'action' => 'cr_reorder_cart',
										'session_key' => 'any_key',
										'session_detail' => $serilized_data,
										'user_id' => $this->request->session()->read('Auth.User.id'),
										'kiosk_id' => $kiosk_id
										);
				$SessionBackups = $this->SessionBackups->newEntity();
				$SessionBackups = $this->SessionBackups->patchEntity($SessionBackups, $sessionBackupData);
				$this->SessionBackups->save($SessionBackups);
			}
		}
		//--------------------------------
        $this->viewBuilder()->layout(false);
		die;
	}
    
    function crDeleteFromCart(){
		extract($this->request->query);
		$quick_cart = $this->request->session()->read('cr_quick_cart');
      // pr($quick_cart);die;
		if(array_key_exists($prod_id,$quick_cart['new_sale_basket'])){
			unset($_SESSION['cr_quick_cart']['new_sale_basket'][$prod_id]);
		}
		$quick_cart['new_sale_basket'] = $_SESSION['cr_quick_cart']['new_sale_basket'];
		$this->cr_reorder_cart($quick_cart);
		$this->viewBuilder()->layout(false);
		die;
	}
    
    public function crUpdateCart(){
      // pr($this->request->query);
      extract($this->request->query);
		if(empty($bulk)){$bulk = "";}
         $quick_cart = $this->request->session()->read('cr_quick_cart');
		 
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		if($kiosk_id < 10000 && $kiosk_id != 0 && !empty($kiosk_id)){
            $productsTable = TableRegistry::get("kiosk_{$kiosk_id}_products",[
																		'table' => "kiosk_{$kiosk_id}_products",
																	]);
		}else{
            $productsTable = TableRegistry::get("products",[
																		'table' => "products",
																	]);
        }
        $prodRow_query = $productsTable->find('all',array('conditions' => array('id' => $prod_id),'recursive' => -1));
        $prodRow_query->hydrate(false);
        if(!empty($prodRow_query)){
            $prodRow = $prodRow_query->first();
        }else{
            $prodRow = array();
        }
        $databse_qantity = $prodRow['quantity'];
		if(is_array($quick_cart) && count($quick_cart) > 0){
			if($qty > $databse_qantity){
				$allowed_qtity = $qty;
				$qantity_short = 0;
			}else{
				$allowed_qtity = $qty;
				$qantity_short = 0;
			}
			if(!array_key_exists('position', $quick_cart)){
				$quick_cart['position'] = 1;
			}else{
				$quick_cart['position'] += 1; //updating position
			}
			$quick_cart['new_sale_bulk_discount'] = $bulk;
			$items = $quick_cart['new_sale_basket'];
			$items[$prod_id]['quantity'] = $allowed_qtity;
			$items[$prod_id]['position'] = $quick_cart['position'];
			$items[$prod_id]['selling_price'] = $sp;
			$items[$prod_id]['qantity_short'] = $qantity_short;
			$items[$prod_id]['type'] = $type;
			$quick_cart['new_sale_basket'] = $items;
			$quick_cart['receipt_required'] = $recit_req;
			$quick_cart['special_invoice'] = $special_invoice;
		}
		//pr($quick_cart);die;
		$this->cr_reorder_cart($quick_cart);
		die;
        
        
		 
	}
     
    public function add_2_cart_short(){
       // pr($this->request->query);die;
		extract($this->request->query);
		$quick_cart = $this->request->Session()->read('quick_cart');
		$prodErrArr = $itemArr = array();
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		if($kiosk_id < 10000 && $kiosk_id != 0 && !empty($kiosk_id)){
            
			$this->Product->setSource("kiosk_{$kiosk_id}_products");
		}
		$prodRow = $this->Product->find('first', array('conditions' => array('product_code' => $product_code),'recursive' => -1));
		//--------price without vat and discounted value---------
		$vat = $this->VAT;
		if(count($prodRow) >= 1){
			$numerator = $prodRow['Product']['selling_price']*100;
			$denominator = $vat+100;
			$priceWithoutVat = $numerator/$denominator;
			$priceWithoutVat = round($priceWithoutVat,2);
			if($prodRow['Product']['discount_status'] == 1){
				$disValue = $priceWithoutVat * $prodRow['Product']['discount']/100;
				$netVal = $priceWithoutVat - $disValue;
			}else{
				$netVal = 0;
			}
		}else{
			$prodErrArr['error'] = "Product either out of stock or invalid product code!";
		}
		
		if(is_array($quick_cart) && count($quick_cart) > 0){
			//pr($quick_cart);
			$items = $quick_cart['new_sale_basket'];
			if(count($prodRow) >= 1 && $prodRow['Product']['quantity']){
				//quanity should not be 0
				$prod_id = $prodRow['Product']['id'];
				//pr($quick_cart);echo "prod id : $prod_id";pr($items);
				if(array_key_exists($prod_id, $items)){
					//echo "updating quantity for added item";
					//updating only quantity
					$totalQty = $items[$prod_id]['quantity'] + $quantity;
					if($totalQty > $prodRow['Product']['quantity']){
						$availQty = $prodRow['Product']['quantity'];
						$items[$prod_id]['quantity'] = $availQty; //updating qty
						$qantity_short = 1;
						$prodErrArr['error'] = "Product Quantity adjusted to $availQty due to limited stock";
					}else{
						$items[$prod_id]['quantity'] = $totalQty; //updating qty
						$qantity_short = 0;
					}
					$quick_cart['position'] += 1; //updating position
					$items[$prod_id]['position'] = $quick_cart['position']; //updating position
					$items[$prod_id]['qantity_short'] = $qantity_short;
					$quick_cart['new_sale_basket'] = $items;
				}else{
					//add new item to existing cart
					//echo "adding item to existing cart";
					$qantity_short = 0;
					if(array_key_exists('position',$quick_cart)){
						$quick_cart['position'] += 1; //updating position
					}else{
						$quick_cart['position'] = 1; //updating position
					}
					
					if($quantity > $prodRow['Product']['quantity']){
						$availQty = $prodRow['Product']['quantity'];
						$quantity = $availQty; //updating qty
						$qantity_short = 1;
						$prodErrArr['error'] = "Product Quantity adjusted to $availQty due to limited stock!";
					}
					$items[$prod_id] = array(
											'product' => $prodRow['Product']['product'],
											'quantity' => $quantity, //coming from box adjacent to google suggest
											'discount_status' => $prodRow['Product']['discount_status'],
											'net_amount' => $prodRow['Product']['selling_price'],
											'selling_price' => $priceWithoutVat,//$prodRow['Product']['selling_price'],
											'discount' => '',
											'price_without_vat' => $priceWithoutVat,
											'product_code' => $prodRow['Product']['product_code'],
											'position' => $quick_cart['position'],
											'available_qantity' => $prodRow['Product']['quantity'],
											'discounted_value' => $netVal, //check
											'minimum_selling_price' => $netVal, //check
											//'price_without_vat' => $priceWithoutVat, //check
											'cost_price' => $prodRow['Product']['cost_price'],
										);
					$items[$prod_id]['qantity_short'] = $qantity_short;
					$quick_cart['new_sale_basket'] = $items;
				}
			}else{
				$prodErrArr['error'] = "Product either out of stock or invalid product code!";
			}
		}else{
			//create new cart
			 echo "create new cart";
			if(count($prodRow) >= 1 && $prodRow['Product']['quantity']){
				//quanity should not be 0
				$quick_cart['position'] = 1;
				$prod_id = $prodRow['Product']['id'];
				$qantity_short = 0;
				if($quantity > $prodRow['Product']['quantity']){
					$availQty = $prodRow['Product']['quantity'];
					$quantity = $availQty; //updating qty
					$qantity_short = 1;
					$prodErrArr['error'] = "Product Quantity adjusted to $availQty due to limited stock!";
				}
				$itemArr[$prod_id] = array(
											'product' => $prodRow['Product']['product'],
											'quantity' => $quantity,
											'discount_status' => $prodRow['Product']['discount_status'],
											'net_amount' => $prodRow['Product']['selling_price'],
											'selling_price' => $priceWithoutVat,//$prodRow['Product']['selling_price'],
											'discount' => '',
											'price_without_vat' => $priceWithoutVat,
											'product_code' => $prodRow['Product']['product_code'],
											'position' => $quick_cart['position'],
											'available_qantity' => $prodRow['Product']['quantity'],
											'discounted_value' => $netVal, //check
											'minimum_selling_price' => $netVal, //check
											'price_without_vat' => $priceWithoutVat, //check
											'cost_price' => $prodRow['Product']['cost_price'],
										);
				$items[$prod_id]['qantity_short'] = $qantity_short;
				$quick_cart['new_sale_basket'] = $itemArr;
			}else{
				$prodErrArr['error'] = "Product either out of stock or invalid product code!";
				$quick_cart = array('new_sale_basket' => array());
				$this->reorder_cart($quick_cart, $prodErrArr);
				//echo json_encode(array('msg' => 'Nothing to restore'));
				//$this->layout = false;
				//die;
			}
		}
		$quick_cart['new_sale_bulk_discount'] = $bulk_discount;
		$quick_cart['receipt_required'] = $recept_req;
		$quick_cart['special_invoice'] = $special_invoice;
		$this->reorder_cart($quick_cart, $prodErrArr);
	}
    function crAdd2CartFull(){
		extract($this->request->query);
		$quick_cart = $this->request->Session()->read('cr_quick_cart');
		$itemArr = array();
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		if($kiosk_id < 10000 && $kiosk_id != 0 && !empty($kiosk_id)){
            $productSource = "kiosk_{$kiosk_id}_products";
        }else{
            $productSource = "products";
        }
        $productTable = TableRegistry::get($productSource,[
                                                            'table' => $productSource,
                                                        ]);
        $prodRow_query = $productTable->find('all', array('conditions' => array('id' => $prod_id),'recursive' => -1));
        $prodRow_query = $prodRow_query->hydrate(false);
        if(!empty($prodRow_query)){
            $prodRow = $prodRow_query->First();
        }else{
            $prodRow = array();
        }
		 
		//Case: validation needs to be added for quantity check
		if(is_array($quick_cart) && count($quick_cart) > 0){
			// echo "hidgf";die;
			$items = $quick_cart['new_sale_basket'];
			if(count($prodRow) >= 1){
				if(array_key_exists($prod_id, $items)){
					$cart_qty = $items[$prod_id]['quantity'];
					$orig_qty = $cart_qty + $qty;
					$databse_qantity = $prodRow['quantity'];
					if($orig_qty > $databse_qantity){
						$allowed_qtity = $orig_qty;
						$qantity_short = 0;
					}else{
						$qantity_short = 0;
						$allowed_qtity = $orig_qty;
					}
					//updating only quantity
					$quick_cart['position'] += 1; //updating position
					$items[$prod_id]['quantity'] = $allowed_qtity;
					$items[$prod_id]['position'] = $quick_cart['position']; //updating position
					$items[$prod_id]['qantity_short'] = $qantity_short; // qantites are short
					$quick_cart['new_sale_basket'] = $items;
				}else{
					$databse_qantity = $prodRow['quantity'];
					if($qty > $databse_qantity){
						$allowed_qtity = $qty;
						$qantity_short = 0;
					}else{
						$allowed_qtity = $qty;
						$qantity_short = 0;
					}
					//add new item to existing cart
					$quick_cart['position'] += 1; //updating position
					$items[$prod_id] = array(
											'product' => $prodRow['product'],
											'quantity' => $allowed_qtity,
											'discount_status' => $prodRow['discount_status'],
											'net_amount' => $prodRow['selling_price'],
											'selling_price' => $sp,//$prodRow['Product']['selling_price'],
											'discount' => '',
											'price_without_vat' => '',
											'product_code' => $prodRow['product_code'],
											'position' => $quick_cart['position'],
											'minimum_selling_price' => $min_dis,
											'available_qantity' => $prodRow['quantity'],
											'cost_price' => $prodRow['cost_price'],
											'type' => 'normal'
										);
					$items[$prod_id]['qantity_short'] = $qantity_short;
					$quick_cart['new_sale_basket'] = $items;
					$quick_cart['position'] = $quick_cart['position'];
				}
			}
		}else{
			// echo "hi";die;
			//create new cart
			if(count($prodRow) >= 1){
				$databse_qantity = $prodRow['quantity'];
				if($qty > $databse_qantity){
					$allowed_qtity = $qty;
					$qantity_short = 0;
				}else{
					$allowed_qtity = $qty;
					$qantity_short = 0;
				}
				$quick_cart['position'] = 1;
				$itemArr[$prod_id] = array(
											'product' => $prodRow['product'],
											'quantity' => $allowed_qtity,
											'discount_status' => $prodRow['discount_status'],
											'net_amount' => $prodRow['selling_price'],
											'selling_price' => $sp,//$prodRow['Product']['selling_price'],
											'discount' => '',
											'price_without_vat' => '',
											'product_code' => $prodRow['product_code'],
											'position' => $quick_cart['position'],
											'minimum_selling_price' => $min_dis,
											'available_qantity' => $prodRow['quantity'],
											'cost_price' => $prodRow['cost_price'],
											'type' => 'normal'
										);
				$items[$prod_id]['qantity_short'] = $qantity_short;
				$quick_cart['new_sale_basket'] = $itemArr;
			}else{
				echo json_encode(array('msg' => 'Nothing to restore'));
			}
		}
		if(isset($bulk_discount))$quick_cart['new_sale_bulk_discount'] = $bulk_discount;
		if(isset($recept_req))$quick_cart['receipt_required'] = $recept_req;
		if(isset($special_invoice))$quick_cart['special_invoice'] = $special_invoice;
		$this->cr_reorder_cart($quick_cart); 
 
	}
    
    public function get_products_json_by_title(){
		$siteBaseURL = Configure::read('SITE_BASE_URL');
		$search = "";
       /// pr($this->request->query);die;
		if(array_key_exists('search_kw',$this->request->query)){
			$search = strtolower($this->request->query['search_kw']);
		}
		$vat = $this->VAT;
		$categories = $categoryIDs = $prodArr = array();
		$fieldArr = array(
						  'id',
						  'product_code',
						  'product',
						  'color',
						  'category_id',
						  'image_dir',
						  'image',
						  'quantity',
						  'cost_price',
						  'selling_price',
						  'discount',
						  'discount_status',
						  );
		/*$this->Product->bindModel(array('hasOne' => array('Category' => array('className' => 'Category'))));*/
		if(!empty($search)){
			$kiosk_id = $this->request->Session()->read('kiosk_id');
			if($kiosk_id < 10000 && $kiosk_id != 0 && !empty($kiosk_id)){
                $Product_Source = "kiosk_{$kiosk_id}_products";
				 
			}
			
			$prodRecs = $this->Product->find('all',array(
										 //'conditions' => array("LOWER(`Product`.`product`) like '%".str_replace(" ","%",$search)."%'"),
										 'conditions' => array("LOWER(`Product`.`product`) like '%$search%' OR LOWER(Product.product_code) like '%$search%'"),
										 'recursive' => -1,
										 'fields' => $fieldArr,
										));
			
			//$dbo = $this->Product->getDatasource();$logData = $dbo->getLog();$getLog = end($logData['log']);echo $getLog['query'];
			$categoryIDs = array();
			foreach($prodRecs as $prodRec){
				$categoryIDs[] = $prodRec['Product']['category_id'];
				//$prodArr[] = 
			}
		}
		if(count($categoryIDs)){
			$categories = $this->Category->find('list',array(
															 'fields' => array('id', 'category'),
															 'conditions' => array('id' => array_unique($categoryIDs))
											));
		}
		
		
		foreach($prodRecs as $prodRec){
			if($prodRec['Product']['quantity'] == 0){
				continue;
			}
			$imageDir = WWW_ROOT."files".DS.'product'.DS.'image'.DS.$prodRec['Product']['id'].DS;
			$catID = $prodRec['Product']['category_id'];
			$categoryTitle = $categories[$catID];
			$prodRec['Product']['category_title'] =  $categoryTitle;
			//----------Image code------------------
			$imageName = 'thumb_'.$prodRec['Product']['image'];
			$largeImageName = 'vga_'.$prodRec['Product']['image'];
			$imageURL = "/thumb_no-image.png";
			$largeImageURL = $imageURL;
			$absoluteImagePath = $imageDir.$imageName;
			if(file_exists($absoluteImagePath)){
				$imageURL = "{$siteBaseURL}/files/product/image/".$prodRec['Product']['id']."/$imageName"; //rasu
				$largeImageURL = "{$siteBaseURL}/files/product/image/".$prodRec['Product']['id']."/$largeImageName"; //rasu
			}
			$prodRec['Product']['image_url'] = $imageURL;
			//----------Image code------------------
			
			//--------price without vat and discounted value---------
			$numerator = $prodRec['Product']['selling_price']*100;
			$denominator = $vat+100;
			$priceWithoutVat = $numerator/$denominator;
			$priceWithoutVat = round($priceWithoutVat,2);
			if($prodRec['Product']['discount_status'] == 1){
				$disValue = $priceWithoutVat*$prodRec['Product']['discount']/100;
				$netVal = $priceWithoutVat - $disValue;
			}else{
				$netVal = 0;
			}
			
			$prodRec['Product']['discounted_value'] = $netVal;
			$prodRec['Product']['price_without_vat'] = $priceWithoutVat;
			//--------price without vat and discounted value---------
			
			$id = $prodRec['Product']['id'];
			$prodArr[$id] = $prodRec['Product'];
		}
		echo json_encode($prodArr);
		$this->layout = false;
		die;
	}
    
   
    
	public function cr_save_receipt($id = array(),$amountToPay = ""){
		
		//Step 2: here we are checking if vat is applicable to customer and saving customer information in product_receipts table
		//here we are grabbing cost of each product from session and multiplying that with quanity and calculating total cost and we are saving this cost in product_receipts table.
		//Here we are also fetching bulk_discount from session array and we saved that  bulk discount simply in product_receipts table.
		//Here we are saving amountToPay (which is including vat if applicable to customer)
		//Case: if we are missing customer information in session we are redirecting to customer screen.
		//We need to delete $id from payment table for above case
		
		$special_invoice = 0;
		if(array_key_exists("cr_quick_cart",$_SESSION)){
			$quick_cart = $this->request->Session()->read('cr_quick_cart');
			$special_invoice = $quick_cart['special_invoice'];
		}
		
		$kioskId = $this->request->Session()->read('kiosk_id');
		if(empty($kioskId)){
			$receiptSource = 'credit_receipts';
		}else{
			$receiptSource = "kiosk_{$kioskId}_credit_receipts";
		}
		
		if($special_invoice == 1){
			$receiptSource = "t_credit_receipts";
		}
		$CreditReceiptTable = TableRegistry::get($receiptSource,[
																'table' => $receiptSource,
															]);
		
		
		if(array_key_exists('cr_customer',$_SESSION) && (float)$amountToPay > 0){
			$cutomerInfo = $_SESSION['cr_customer'];
			$agent_id = $cutomerInfo['agent_id'];
			if(!empty($cutomerInfo)){
				$total_cost = $bulk_discount = 0;
				$vat = $this->VAT;
				if($cutomerInfo['country'] == "OTH"){
					$vat = 0;
				}
				
				if(array_key_exists('cr_quick_cart',$_SESSION)){
					if(array_key_exists('new_sale_bulk_discount',$_SESSION['cr_quick_cart'])){
						$bulk_discount = $_SESSION['cr_quick_cart']['new_sale_bulk_discount'];
					}
					$session_basket = $_SESSION['cr_quick_cart']['new_sale_basket'];
					foreach($session_basket as $pId => $value){
						$total_cost += $value['cost_price'] * $value['quantity'];
					}
				}
				if(empty($bulk_discount)){
					$bulk_discount = 0;
				}
				$receiptData = array(
										'customer_id' => $cutomerInfo['id'],
										'agent_id' => $agent_id,
										'address_1' => $cutomerInfo['address_1'],
										'address_2' => $cutomerInfo['address_2'],
										'city' => $cutomerInfo['city'],
										'state' => $cutomerInfo['state'],
										'zip' => $cutomerInfo['zip'],
										'vat' =>$vat,
										'bill_cost' => $total_cost,
										'bill_amount' => $amountToPay,
										'orig_bill_amount' => $amountToPay,
										'bulk_discount' => $bulk_discount,
										'processed_by' =>$this->Auth->user('id'),
										'fname' => $cutomerInfo['fname'],
										'lname' => $cutomerInfo['lname'],
										'mobile' => $cutomerInfo['mobile'],
										'email' => $cutomerInfo['email'],
										'credit_amount' => $amountToPay,
										'status'=> 0,
									);
				//pr($receiptData);die;
				if($special_invoice == 1){
					if(empty($kioskId)){
						$receiptData['kiosk_id'] = 0;
					}else{
						$receiptData['kiosk_id'] = $kioskId;
					}
				}
				$CreditReceiptTable->behaviors()->load('Timestamp');
				$newEntity = $CreditReceiptTable->newEntity();
				$patchEntity = $CreditReceiptTable->patchEntity($newEntity,$receiptData);
				if($CreditReceiptTable->save($patchEntity)){
					$receipt_id = $patchEntity->id;
					//echo $receipt_id;die;
					//Step 3: here we are saving product entries which we are grabbing from session and saving reference of receipt id in payment details table.
					$this->cr_save_kiosk_sale($receipt_id,$id);
				}
			}
		}else{
			//Delete from payment table for variable $id. - Pending
			if(!empty($id)){
				//$kioskId = $this->Session->read('kiosk_id');
				if(empty($kioskId)){
					$paymentTable = "credit_payment_details";
				}else{
					$paymentTable = "kiosk_{$kioskId}_credit_payment_details";
				}
				if($special_invoice == 1){
					$paymentTable = "t_credit_payment_details";
				}
				foreach($id as $key => $value1){
					//$paymentTable = "payment_details";
					$conn = ConnectionManager::get('default');
					$stmt = $conn->execute("DELETE FROM `$paymentTable`  WHERE `$paymentTable`.`id` = '$value1'"); 
					$currentTimeInfo = $stmt ->fetchAll('assoc');
									}
			}
			$this->Flash->error("Failed to generate credit for the case either amount is 0 or customer information is missing");
			return $this->redirect(array('controller'=>'customers','action'=>'index'));
		}
	}
	
    public function cr_save_kiosk_sale($receipt_id,$id = array()){
		//pr($_SESSION);die;
		$special_invoice = 0;
		if(array_key_exists("cr_quick_cart",$_SESSION)){
			$quick_cart = $this->request->Session()->read('cr_quick_cart');
			$special_invoice = $quick_cart['special_invoice'];
		}
		$kioskId = $this->request->Session()->read('kiosk_id');
		if(empty($kioskId)){
			$kioskId = 0;
			$paymentSource = "credit_payment_details";
			$receiptSource = "credit_receipts";
			$productSaleSource = "credit_product_details";
			$product = 'products';
		}else{
			$paymentSource = "kiosk_{$kioskId}_credit_payment_details";
			$receiptSource = "kiosk_{$kioskId}_credit_receipts";
			$productSaleSource = "kiosk_{$kioskId}_credit_product_details";
			$product = "kiosk_{$kioskId}_products";
		}
		if($special_invoice == 1){
			$paymentSource = "t_credit_payment_details";
			$receiptSource = "t_credit_receipts";
			$productSaleSource = "t_credit_product_details";
		}
		
		$new_kiosk_id = $kioskId;
		if(empty($new_kiosk_id)){
			$new_kiosk_id = 10000;
		}
		$new_kiosk_data = $this->Kiosks->find("all",['conditions' => ['id' => $new_kiosk_id]]);
		$new_kiosk_data = $new_kiosk_data->hydrate(false);
		if(!empty($new_kiosk_data)){
			$new_kiosk_data = $new_kiosk_data->first();
		}else{
			$new_kiosk_data = array();
		}
		//echo $productSaleSource;die;
		$CreditProductDetailTable = TableRegistry::get($productSaleSource,[
																				'table' => $productSaleSource,
																			]);
		$CreditPaymentDetailTable = TableRegistry::get($paymentSource,[
																			'table' => $paymentSource,
																		]);
		$CreditReceiptTable = TableRegistry::get($receiptSource,[
																	'table' => $receiptSource,
																]);
		$ProductTable = TableRegistry::get($product,[
														'table' => $product,
													]);
		
		//Here after processing invoice we are deleting all related sessions
		$vat = $this->VAT;$counter = 0;
		if(array_key_exists('cr_quick_cart',$_SESSION)){
			if(array_key_exists('new_sale_basket',$_SESSION['cr_quick_cart'])){
				$session_basket = $_SESSION['cr_quick_cart']['new_sale_basket'];
			}
		}
		if(array_key_exists('cr_customer',$_SESSION)){
			$cutomerInfo = $_SESSION['cr_customer'];
			if(!empty($cutomerInfo)){
				$customer_id = $cutomerInfo['id'];
			}
		}
		$bulk_dis = 0;
		if(array_key_exists('cr_quick_cart',$_SESSION)){
			if(array_key_exists('new_sale_bulk_discount',$_SESSION['cr_quick_cart'])){
				$bulk_dis = $_SESSION['cr_quick_cart']['new_sale_bulk_discount'];
			}
		}
		$country = "";
		$vat = $this->VAT;
		if(array_key_exists('customer',$_SESSION)){
			$cutomerInfo = $_SESSION['customer'];
			if(!empty($cutomerInfo)){
				$country = $cutomerInfo['country'];
			}
		}
		//pr($session_basket);die;
		$p_kiosk_id = $this->request->Session()->read('kiosk_id');
			if($p_kiosk_id == 0 || $p_kiosk_id == ''){
				$p_kiosk_id = 10000;
			}
			//echo "<pre>";print_r($session_basket);die;
		if(!empty($session_basket)){
			foreach($session_basket as $key => $value){
				//In this loop we are saving each product purchase with quanity in kiosk_product_sales table for the data we grabbed from session and saving receipt id generated in step 2 and updating quantity as well.
				$quantity = $withVatValue = $numerator = $denominator = $priceWithoutVat = $sold_price = $firstVal = $discountPercentage = 0;
				$withVatValue = $value['net_amount'];
				$numerator = $withVatValue*100;
				$denominator = $vat+100;
				$priceWithoutVat = $numerator/$denominator;
				$priceWithoutVat = round($priceWithoutVat,2);
				$sold_price = $value['selling_price'];
				$firstVal = $priceWithoutVat - $sold_price;
				//echo "$firstVal/$priceWithoutVat*100";echo "</br>";
				$discountPercentage = $firstVal/$priceWithoutVat*100;
				if($discountPercentage > 0){
					$dis_status = 1;
				}else{
					$dis_status = 0;
				}
				$quantity = $value['quantity'];
				if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER || $this->request->session()->read('Auth.User.group_id') == MANAGERS  || $this->request->session()->read('Auth.User.group_id') == SALESMAN){
					$sale_type = 1;
				}else{
					if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS && $this->request->session()->read('Auth.User.user_type')=='wholesale'){
						$sale_type = 1;
					}else{
						$sale_type = 0;
					}
				}
				$refund_type = $value['type'];
				
				$kiosk_data = array(
										'kiosk_id' => $kioskId,
										'product_id' => $key,
										'customer_id' => $customer_id,
										'quantity' => $value['quantity'],
										'cost_price' => $value['cost_price'],//Undefined index: cost_price
										'sale_price' =>$withVatValue,
										'credit_price' => $withVatValue,
										'discount' => $discountPercentage,
										'credit_by' =>$this->Auth->user('id'),
										'discount_status' => $dis_status,
										'credit_receipt_id' =>$receipt_id,
										'sale_type' => $sale_type,
										'type' => $value['type'],
									);
				//pr($kiosk_data);die;
				//Start: newly added by rajiv on Oct 10, 2016
				$kiosk_id = $this->request->Session()->read('kiosk_id');
				if($kiosk_id < 10000 && $kiosk_id != 0 && !empty($kiosk_id)){
					if($special_invoice == 1){
						$CreditProductDetail_source = "t_credit_product_details";
					}else{
						$CreditProductDetail_source = "kiosk_{$kiosk_id}_credit_product_details";
					}
					$CreditProductDetailTable = TableRegistry::get($CreditProductDetail_source,[
																				'table' => $CreditProductDetail_source,
																			]);
				}
				//end: newly added by rajiv on Oct 10, 2016
				$CreditProductDetailTable->behaviors()->load('Timestamp');
				$newEntity = $CreditProductDetailTable->newEntity();
				$patchEntity = $CreditProductDetailTable->patchEntity($newEntity,$kiosk_data);
				//echo "hi";
				//echo "<pre>";print_r($kiosk_data);die;
				if($CreditProductDetailTable->save($patchEntity)){
					$product_code_query = $ProductTable->find('list',[
																'conditions' => array('id' => $key),
																'keyField' => 'id',
																'valueField' => 'product_code'
																]
														);
					$product_code_query = $product_code_query->hydrate(false);
					if(!empty($product_code_query)){
						$product_code = $product_code_query->toArray();
					}else{
						$product_code = array();
					}
                    
					if($bulk_dis > 0){
						$bulk_value =  $value['selling_price'] * ($bulk_dis/100);
						$after_bulk_value = $value['selling_price'] - $bulk_value;
						$selling_price_without_vat = $after_bulk_value * $value['quantity'];
					}else{
						$after_bulk_value =  $value['selling_price'];
						$selling_price_without_vat = $value['selling_price'] * $value['quantity'];
					}
					if($country != 'OTH'){
						$vat_value = $after_bulk_value * ($vat/100);
						$total_vat = $vat_value * $value['quantity'];
					}else{
						$total_vat = 0;
					}
					
					 $data = array(
									'quantity' => $value['quantity'],
									'product_code' => $product_code[$key],
									'selling_price_withot_vat' => $selling_price_without_vat,
									'vat' => $total_vat
					   );
					  if($special_invoice == 1){
						$is_special = 1;
					 }else{
						$is_special = 0;
					 }
					 
					$this->insert_to_ProductSellStats($key,$data,$p_kiosk_id,$operations = '-',$is_special);
					//Start: newly added by rajiv on Oct 10, 2016
					if($kiosk_id < 10000 && $kiosk_id != 0 && !empty($kiosk_id)){
						$productTable = "kiosk_{$kiosk_id}_products";
					}else{
						$productTable = "products";
					}
					
					if($refund_type == "normal"){
						$conn = ConnectionManager::get('default');
						$stmt = $conn->execute("UPDATE `$productTable` SET `quantity` = `quantity` + $quantity WHERE `$productTable`.`id` = '$key'"); 
						$counter++;
					}else{
						$this->loadModel('FaultyProductDetails');
						$this->loadModel('DefectiveKioskProducts');
						$faultyProductData = array(
								'kiosk_id' => $kiosk_id,
								'credit_receipt_id' => $receipt_id,
								'credit_by' => $this->Auth->user('id'),
								'product_id' => $key,
								'customer_id' => $customer_id,
								'quantity' => $value['quantity'],
								'sale_price' => $withVatValue,
								'discount' => $discountPercentage
									   );
						
						$new_entity = $this->FaultyProductDetails->newEntity();
						$patch_entity = $this->FaultyProductDetails->patchEntity($new_entity,$faultyProductData);
						$this->FaultyProductDetails->save($patch_entity);
						
						if(empty($kiosk_id) && ($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER || $this->request->session()->read('Auth.User.group_id') == MANAGERS)){
							$kskId = 10000;
						}else{
							$kskId = $kiosk_id;//$this->request->data['KioskProductSale']['kiosk_id'];
						}
						$defectiveProductData = array(
								'product_id' => $key,
								'quantity' => $value['quantity'],
								'kiosk_id' => $kskId,
								'user_id' => $this->Auth->user('id'),
								'status' => 0,//not moved to central_faulty_products table
								'remarks' => 1//reserved for faulty refund to customer
									      );
						//not adjusting the kiosk quantity after moving to faulty as discussed by client
						$new_Entity = $this->DefectiveKioskProducts->newEntity();
						$patch_Entity = $this->DefectiveKioskProducts->patchEntity($new_Entity,$defectiveProductData);
						$this->DefectiveKioskProducts->save($patch_Entity);
						$counter++;
					}
					
					//end: newly added by rajiv on Oct 10, 2016
					
				}else{
					echo "</pre>";print_r($this->CreditProductDetail->validationErrors);die;
				}
			}
			$count = 0;
			if($counter > 0){
				//newly added by rajiv on Oct 10, 2016
				$kiosk_id = $this->request->Session()->read('kiosk_id');
				if($kiosk_id < 10000 && $kiosk_id != 0 && !empty($kiosk_id)){
					$Product_source = "kiosk_{$kiosk_id}_products";
					$ProductTable = TableRegistry::get($Product_source,[
                                                                                    'table' => $Product_source,
                                                                                ]);
					if($special_invoice == 1){
						$CreditReceipt_source = "t_credit_receipts";
						$CreditPaymentDetail_source = "t_credit_payment_details";
					}else{
						$CreditReceipt_source = "kiosk_{$kiosk_id}_credit_receipts";
						$CreditPaymentDetail_source = "kiosk_{$kiosk_id}_credit_payment_details";
					}
					$CreditReceiptTable = TableRegistry::get($CreditReceipt_source,[
                                                                                    'table' => $CreditReceipt_source,
                                                                                ]);
					$CreditPaymentDetailTable = TableRegistry::get($CreditPaymentDetail_source,[
                                                                                    'table' => $CreditPaymentDetail_source,
                                                                                ]);
				}
				if($special_invoice == 1){
					$CreditReceipt_source = "t_credit_receipts";
					$CreditReceiptTable = TableRegistry::get($CreditReceipt_source,[
                                                                                    'table' => $CreditReceipt_source,
                                                                                ]);
					$CreditPaymentDetail_source = "t_credit_payment_details";
					$CreditPaymentDetailTable = TableRegistry::get($CreditPaymentDetail_source,[
                                                                                    'table' => $CreditPaymentDetail_source,
                                                                                ]);
				}
				//newly added by rajiv
				//echo "hi";die;
                //pr($id);die;
				if(!empty($id)){
					//echo'hi';die;
					foreach($id as $key => $value1){
						//here we are updating receipt_id generated in step 2 for payment detail table.
						$dataArr = array('credit_receipt_id'=>$receipt_id);
						$getId = $CreditPaymentDetailTable->get($value1);
						$entity_patch = $CreditPaymentDetailTable->patchEntity($getId,$dataArr);
						$CreditPaymentDetailTable->save($entity_patch);
						$count++;
					}
					if($count > 0){
                        //echo'hi';die;
						$options = array(
								'conditions' => array('id' => $receipt_id)
							);
						$productReceipt_query = $CreditReceiptTable->find('all', $options);
						$productReceipt_query  = $productReceipt_query->hydrate(false);
						if(!empty($productReceipt_query)){
							$productReceipt = $productReceipt_query->first();
						}else{
							$productReceipt = array();
						}
                        
                        $customer_id = $productReceipt['customer_id'];
                        $cust_query = $this->Customers->find("all",array('conditions' => array('id' => $customer_id)));
                        $cust_query = $cust_query->hydrate(false);
                        if(!empty($cust_query)){
                         $cust_data = $cust_query->first();   
                        }else{
                            $cust_data = array();
                        }
                        
						$processed_by = $productReceipt['processed_by'];
						
						$CreditProductDetail_query = $CreditProductDetailTable->find('all',[
															'conditions' => ['credit_receipt_id' => $receipt_id]
															]);
						$CreditProductDetail_query = $CreditProductDetail_query->hydrate(false);
						if(!empty($CreditProductDetail_query)){
							$CreditProductDetail_data = $CreditProductDetail_query->toArray();
						}else{
							$CreditProductDetail_data = array();
						}
						
                        
                        
						$userName_query = $this->Users->find('all',array(
																	'conditions' => array('Users.id' => $processed_by),
																	'fields' => array('username'),
																	)
													  );
						$userName_query = $userName_query->hydrate(false);
						if(!empty($userName_query)){
							$userName = $userName_query->first();
						}else{
							$userName = array();
						}
						$user_name = $userName['username'];
						foreach($CreditProductDetail_data as $key => $productDetail){
							$productIdArr[] = $productDetail['product_id'];
						}
                        $product_detail = array();
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
						
						
						$paymentDetails_query = $CreditPaymentDetailTable->find('all',array('conditions' => array('credit_receipt_id' => $receipt_id)));
						$paymentDetails_query = $paymentDetails_query->hydrate(false);
						if(!empty($paymentDetails_query)){
							$paymentDetails = $paymentDetails_query->toArray();
						}else{
							$paymentDetails = array();
						}
						$payment_method = array();
						$settingArr = $this->setting;
						$CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
						foreach($paymentDetails as $key=>$paymentDetail){
							//pr($paymentDetail);
							$payment_method[] = $paymentDetail['payment_method']." ".$CURRENCY_TYPE.$paymentDetail['amount'];
						}
						
						
						$fullAddress = $countryOptions = $kioskContact = $kioskTable = "";
						
						$countryOptions = Configure::read('uk_non_uk');
						if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS &&
							$this->request->session()->read('Auth.User.user_type')=='wholesale'){
							$kiosk_id = $this->request->Session()->read('kiosk_id');
							$kioskDetails_query = $this->Kiosks->find('all',array('conditions'=>array('Kiosks.id'=>$kiosk_id),'recursive'=>-1,'fields'=>array('id','name','address_1','address_2','city','state','zip','contact','country')));
							$kioskDetails_result = $kioskDetails_query->hydrate(false);
							if(!empty($kioskDetails_result)){
								$kioskDetails = $kioskDetails_result->first();
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
						
						$receiptRequired = $_SESSION['cr_quick_cart']['receipt_required'];
                        if($special_invoice != 1){
							$send_by_email = Configure::read('send_by_email');
							$emailSender = Configure::read('EMAIL_SENDER');
                            if($receiptRequired == 1){
                                $Email = new Email();
                                $Email->config('default');
                                $Email->viewVars(array
                                                    ('productReceipt' => $productReceipt,
                                                     'payment_method' => $payment_method,
                                                     'vat' => $vat,
                                                     'settingArr' =>$settingArr,
                                                     'user_name'=>$user_name,
                                                     'productName'=>$productName,
                                                     'productCode'=>$productCode,
                                                     'kioskTable'=>$kioskTable,
                                                     'kioskContact'=>$kioskContact,
                                                     'countryOptions'=>$countryOptions,
                                                     'customer' => $cust_data,
                                                     'creditProductDetailsData' => $CreditProductDetail_data,
													 'NewkioskDetails' => $new_kiosk_data
                                                    ));
                                //$Email->config(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
                                //$Email = new CakeEmail(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
                                $emailTo = $customerData['email'];;
                                $Email->template('credit_receipt');
                                $Email->emailFormat('both');
                                $Email->to($emailTo);
                                $Email->transport(TRANSPORT);
								$Email->from([$send_by_email => $emailSender]);
                                $Email->subject('Order Receipt');
                                $Email->send();
                            }
                        }
						
						unset($_SESSION['cr_quick_cart']);
						unset($_SESSION['cr_customer']);
						$kiosk_id = $this->request->Session()->read('kiosk_id');
						if($kiosk_id == ""){
							$kiosk_id = 10000;
						}
						$this->SessionRestore->delete_from_session_backup_table('Home', 'cr_reorder_cart', 'any_key', $kiosk_id);
						$this->Flash->success("Credit Note created");
						if($special_invoice == 1){
							return $this->redirect(array('controller'=>'credit-product-details','action'=>'t-view',$receipt_id));
						}else{
							return $this->redirect(array('controller'=>'credit-product-details','action'=>'view',$receipt_id));
						}
					}
				}
			}
		}else{
			//There was issues on Manchester in 3rd step/final step when we were about to update payment in kiosk prouct sale and session timed out. (and we were having entries in Product Receipts and Payment Details w.r.t kiosk id. we coded code in this block on 26th May 2017)
			//This will fix bugs for admin and kiosk user of  type wholesale
			if(!empty($id)){
				//$kioskId = $this->Session->read('kiosk_id');
				if(empty($kioskId)){
					$paymentTable = "credit_payment_details";
				}else{
					$paymentTable = "kiosk_{$kioskId}_credit_payment_details";
				}
				if($special_invoice == 1){
					$paymentTable = "t_credit_payment_details";
				}
				foreach($id as $key => $value1){
					//$paymentTable = "payment_details";
					$updateQry = "DELETE FROM `$paymentTable`  WHERE `$paymentTable`.`id` = '$value1'";
					$this->CreditPaymentDetail->query($updateQry);
				}
			}
			if(!empty($receipt_id)){
				if(empty($kioskId)){
					$reciptTable = "credit_receipts";
				}else{
					$reciptTable = "kiosk_{$kioskId}_credit_receipts";
				}
				if($special_invoice == 1){
					$reciptTable = "t_credit_receipts";
				}
				$alter_id = $receipt_id-1;
				$updateQry = "DELETE FROM `$reciptTable`  WHERE `$reciptTable`.`id` = '$receipt_id'";
				$this->CreditReceipt->query($updateQry);
				$alterQuery = "ALTER TABLE `$receiptTable` AUTO_INCREMENT = $alter_id";
				$this->CreditReceipt->query($alterQuery);
			}
			$this->Session->setFlash("Failed to generate credit for the case either amount is 0 or customer information is missing");
			return $this->redirect(array('controller'=>'customers','action'=>'index'));
		}
	}
	
	
	public function crClearCart(){
		$this->request->Session()->delete('cr_quick_cart');
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		if($kiosk_id == ""){
			$kiosk_id = 10000;
		}
		$this->SessionRestore->delete_from_session_backup_table('Home', 'cr_reorder_cart', 'any_key', $kiosk_id);
		echo json_encode(array('msg' => 'Cart Cleared'));
		$this->viewBuilder()->layout(false);
		die;
	}
    public function pf_clear_cart(){
		$this->request->Session()->delete('pf_quick_cart');
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		if($kiosk_id == ""){
			$kiosk_id = 10000;
		}
		$this->SessionRestore->delete_from_session_backup_table('Home', 'pf_reorder_cart', 'any_key', $kiosk_id);
		echo json_encode(array('msg' => 'Cart Cleared'));
		$this->viewBuilder()->layout(false);
		die;
	}
    public function crRestoreCart (){
		$quick_cart = $this->request->session()->read('cr_quick_cart');
		if(is_array($quick_cart) && count($quick_cart) > 0){
			//all good
			$this->cr_reorder_cart($quick_cart);
		}else{
			echo json_encode(array('msg' => 'Nothing to restore'));
		}
		$this->viewBuilder()->layout(false);
		die;
	}
	
	public function pfRestoreCart (){
		$quick_cart = $this->request->session()->read('pf_quick_cart');
		if(is_array($quick_cart) && count($quick_cart) > 0){
			//all good
			$this->pf_reorder_cart($quick_cart);
		}else{
			echo json_encode(array('msg' => 'Nothing to restore'));
		}
		$this->viewBuilder()->layout(false);
		die;
	}
    public function crRestoreSessionDb(){
		$user_id = $this->request->session()->read('Auth.User.id');
		$kiosk_id = $this->request->Session()->read('kiosk_id');
				if($kiosk_id == ""){
					$kiosk_id = 10000;	
				}
		
        $sessionRS_query = $this->SessionBackups->find('all', array('conditions' => array(
																				'controller' => 'Home',
																				'action' => 'cr_reorder_cart',
																				'kiosk_id' => $kiosk_id,
																				'user_id' => $this->request->session()->read('Auth.User.id'),
																			)));
        $sessionRS_query = $sessionRS_query->hydrate(false);
        $sessionRS = array();
        if(!empty($sessionRS_query)){
            $sessionRS = $sessionRS_query->first();
        }else{
            $sessionRS = array();
        }
		if(!empty($sessionRS)){
			$data = $sessionRS['session_detail'];
			$unserilizesd_data = unserialize($data);
			//echo json_encode($unserilizesd_data);
			$this->cr_reorder_cart($unserilizesd_data);
		}else{
			$msg = array("msg" => "empty  cart");
			echo json_encode($msg);
		}
		$this->viewBuilder()->layout(false);
		die;
	}
	
	public function pfRestoreSessionDb(){
		
		$user_id = $this->request->session()->read('Auth.User.id');
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		if($kiosk_id == ""){
			$kiosk_id = 10000;
		}
         $sessionRS_query = $this->SessionBackups->find('all', array('conditions' => array(
																				'controller' => 'Home',
																				'action' => 'pf_reorder_cart',
																				'kiosk_id' => $kiosk_id,
																				'user_id' => $this->request->session()->read('Auth.User.id'),
																			)));
        if(!empty($sessionRS_query)){
            $sessionRS = $sessionRS_query->first();
        }else{
            $sessionRS = array();
        }
		
		if(!empty($sessionRS)){
			$data = $sessionRS['session_detail'];
			$unserilizesd_data = unserialize($data);
			//echo json_encode($unserilizesd_data);
			$this->pf_reorder_cart($unserilizesd_data);
		}else{
			$msg = array("msg" => "cart empty");
			echo json_encode($msg);die;
		}
		$this->viewBuilder()->layout(false);
		die;
	}
    public function bulkPerforma($customerID = 0){
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
		$this->set(compact('agents'));
		
		//pr($this->request);
        $vat = isset($this->setting['vat']) && !empty($this->setting['vat']) ? $this->setting['vat'] : 0;
        $customer_query = $this->Customers->find('all', array(
														 'conditions' => array('id' => (int)$customerID),
														 'recursive' => -1,
														 )
										  );
        $customer_query = $customer_query->hydrate(false);
        if(!empty($customer_query)){
            $customer = $customer_query->First();
        }
		 
		if(count($customer) < 1){
			 $flashMessage = "Invalid customer id";
            $this->request->Session()->setFlash($flashMessage);
			$this->redirect(array('controller' => 'customers','action' => "index"));
		}
		if(!empty($customer)){
			$this->request->Session()->write('pf_customer',$customer);
		}
		//-----------------------------------------
        $categories_query = $this->Categories->find('all',array(
								'fields' => array('id', 'category','id_name_path'),
                                                                'conditions' => array('Categories.status' => 1),
								'order' => 'Categories.category asc',
								'recursive' => -1
								));
        $categories_query->hydrate(false);
        $categories = $categories_query->toArray();
		$categories = $this->CustomOptions->category_options($categories,true);
		 
		$this->set(compact('categories','customer','vat'));
	}
    
    public function saveCredit(){	
		//echo "hi";die("--");
		$special_invoice = 0;
		if(array_key_exists("cr_quick_cart",$_SESSION)){
			$quick_cart = $this->request->Session()->read('cr_quick_cart');
			$special_invoice = $quick_cart['special_invoice'];
		}
		if(array_key_exists('cr_customer',$_SESSION)){
			$cr_customer = $_SESSION['cr_customer'];
			$agent_id = $cr_customer['agent_id'];
		}else{
			$agent_id = 0;
		}
		$product_id_qan_array = $product_id_array = $error = array();
		if(array_key_exists('cr_quick_cart',$_SESSION)){
			if(array_key_exists('new_sale_basket',$_SESSION['cr_quick_cart'])){
				$session_basket = $_SESSION['cr_quick_cart']['new_sale_basket'];
				foreach($session_basket as $s_key => $s_value){
					$product_id_array[$s_key] = $s_key;
					$product_id_qan_array[$s_key] = $s_value['quantity'];
					if(!array_key_exists('net_amount',$session_basket[$s_key])){
						$error[] = "error";
					}
					if(!array_key_exists('selling_price',$session_basket[$s_key])){
						$error[] = "error";
					}
					if(!array_key_exists('quantity',$session_basket[$s_key])){
						$error[] = "error";
					}
					if(!array_key_exists('cost_price',$session_basket[$s_key])){
						$error[] = "error";
					}
					
				}
			}
		}
		
		if(!empty($error)){
			$this->Flash->error("credit could not be saved(some keys are missing)");
			return $this->redirect(array('controller'=>'customers','action'=>'index'));die;
		}
		
		
		$kioskId = $this->request->Session()->read('kiosk_id');
		//echo $kioskId;die;
		if(empty($kioskId)){
			$paymentSource = 'credit_payment_details';
			$productTable = "products";
		}else{
			$paymentSource = "kiosk_{$kioskId}_credit_payment_details";
			$productTable = "kiosk_{$kioskId}_products";
		}
		//echo $paymentSource;die;
		$ProductTable = TableRegistry::get($productTable,[
															'table' => $productTable,
														]);
		$CreditPaymentDetailTable = TableRegistry::get($paymentSource,[
															'table' => $paymentSource,
														]);
		$res_query = $ProductTable->find('all',array(
												'fields' => array('id','quantity','product'),
												'conditions' => array(
															   'id IN' => $product_id_array
															   )
												));
		$res_query = $res_query->hydrate(false);
		if(!empty($res_query)){
			$res = $res_query->toArray();
		}else{
			$res = array();
		}

		if($special_invoice == 1){
			//$this->PaymentDetail->setSource('t_payment_details');
			$paymentSource = "t_credit_payment_details";
		}
		$CreditPaymentDetailTable = TableRegistry::get($paymentSource,[
															'table' => $paymentSource,
														]);
		
		//Step 1: after payment submission from ajax screen
		//In this function we saved payment details without generating receipt according to payment method choosen.
		if(array_key_exists("cr_quick_cart",$_SESSION)){
			$quick_cart = $this->request->Session()->read('cr_quick_cart');
			$quick_cart['new_sale_bulk_discount'] = $this->request->data['bulk_discount_input'];
			$this->request->Session()->write('cr_quick_cart',$quick_cart);
		}
		//pr($_SESSION);die;
		$counter = $amountToPay = 0;
		$totalPaymentAmount = 0;
		if(array_key_exists('final_amount',$this->request->data)){
			$amountToPay = $this->request->data['final_amount'];
		}
		//echo $amountToPay;die;
		//pr($this->request);die;
		foreach($this->request['data']['Payment']['Amount'] as $key => $paymentAmount){
			$totalPaymentAmount += floatval($paymentAmount);
		}
		$totalPaymentAmount = round($totalPaymentAmount,2);
		$amountToPay = round($amountToPay,2);
		foreach($this->request['data']['Payment']['Payment_Method'] as $key => $paymentMethod){
			if(
				$totalPaymentAmount < $amountToPay &&
				($paymentMethod == "Cheque" || $paymentMethod == "Cash" || $paymentMethod == "Bank Transfer" || $paymentMethod == "Card")){
				$error[] = "Amount must be equivalent to &#163; {$amountToPay}. Please try again";
				break;
			}elseif($totalPaymentAmount > $amountToPay &&
				($paymentMethod == "Cheque" ||
				$paymentMethod == "Cash" ||
				$paymentMethod == "Bank Transfer" ||
				$paymentMethod == "Card")){
				$error[] = "Amount must be equivalent to &#163; {$amountToPay}. Please try again";
				break;
			}elseif($totalPaymentAmount < $amountToPay && $paymentMethod == "On Credit"){
				$error[] = "Amount must be equivalent to &#163; {$amountToPay}. Please try again";
				break;
			}
		}
		
		if(!empty($error)){
				$errorStr = implode("<br/>",$error);
				$this->Flash->error("$errorStr");
				return $this->redirect(array('controller'=>'customers','action'=>'index'));
		}
		
		$id = array();
		if($this->request['data']['Payment']['Payment_Method'][0] == "On Credit"){
			$paymentDetailData = array(
										//'product_receipt_id' => $id,//by rajiv
										'agent_id' =>$agent_id,
										'payment_method' => $this->request['data']['Payment']['Payment_Method'][0],
										//'description' => 'On Credit',//$this->request['data']['Payment']['Description'][0],
										'amount' => $amountToPay,
										'payment_status' => 0,
										'status' => 1,
									);
			if($special_invoice == 1){
				//$this->PaymentDetail->setSource('t_payment_details');
				if(!empty($kioskId)){
					$paymentDetailData['kiosk_id'] = $kioskId;
				}else{
					$paymentDetailData['kiosk_id'] = 0;
				}
				
			}
			$CreditPaymentDetailTable->behaviors()->load('Timestamp');
			$newEntity = $CreditPaymentDetailTable->newEntity();
			$patchEntity = $CreditPaymentDetailTable->patchEntity($newEntity,$paymentDetailData);
			if($CreditPaymentDetailTable->save($patchEntity)){
				$id[] = $patchEntity->id;
				$counter++;
			}
			//This needs to be refined. If first method is on credit others can be by cheque, by cash or any other = pending
		}else{
			foreach($this->request['data']['Payment']['Amount'] as $key => $paymentAmount){
				$paymentMethod = $this->request['data']['Payment']['Payment_Method'][$key];
				if($paymentMethod == "On Credit"){
					$payment_status = 0;
				}else{
					$payment_status = 1;
				}
				
				if(!empty($paymentAmount)){
					$paymentDetailData = array(
												'agent_id' =>$agent_id,
												'payment_method' => $paymentMethod,
												'amount' => $paymentAmount,
												'payment_status' => $payment_status,
												'status' => 1,
											);
					if($special_invoice == 1){
						//$this->PaymentDetail->setSource('t_payment_details');
						if(!empty($kioskId)){
							$paymentDetailData['kiosk_id'] = $kioskId;
						}else{
							$paymentDetailData['kiosk_id'] = 0;
						}
						
					}
					$CreditPaymentDetailTable->behaviors()->load('Timestamp');
					$new_entity = $CreditPaymentDetailTable->newEntity();
					$patch_entity = $CreditPaymentDetailTable->patchEntity($new_entity,$paymentDetailData,['validate'=>false]);
					//pr($patch_entity);die;
					if($CreditPaymentDetailTable->save($patch_entity)){
						$id[] = $patch_entity->id;
						$counter++;
					}
				}
			}
		}
		//echo "hi";die;
		//Call for step 2
		//pr($id);die;
		$this->cr_save_receipt($id,$amountToPay);
	}
	
    public function crUpdateBulk(){
		extract($this->request->query);
		$quick_cart = $this->request->Session()->read('cr_quick_cart');
		//pr($quick_cart);
		$this->request->Session()->read('cr_quick_cart');
		//$special_invoice = $quick_cart['special_invoice'];
		
		if(!empty($quick_cart)){
			$quick_cart['new_sale_bulk_discount'] = $bulk;
			$quick_cart['special_invoice'] = $special_invoice;
			//pr($quick_cart);die;
			$this->request->Session()->write('cr_quick_cart',$quick_cart);
		}else{
			$quick_cart['new_sale_bulk_discount'] = $bulk;
			$quick_cart['special_invoice'] = $special_invoice;
			$this->request->Session()->write('cr_quick_cart',$quick_cart);
		}
		$this->cr_reorder_cart($quick_cart);
		$this->viewBuilder()->layout(false);
		die;
	}
	
	public function pfUpdateBulk (){
		extract($this->request->query);
		$quick_cart = $this->request->Session()->read('pf_quick_cart');
		if(!empty($quick_cart)){
			$quick_cart['new_sale_bulk_discount'] = $bulk;
			$this->request->Session()->write('pf_quick_cart',$quick_cart);
		}else{
			$quick_cart['new_sale_bulk_discount'] = $bulk;
			$this->request->Session()->write('pf_quick_cart',$quick_cart);
		}
		$this->pf_reorder_cart($quick_cart);
		$this->viewBuilder()->layout(false);
		die;
	}
    
    function pfAdd2CartShort(){
		extract($this->request->query);
		$quick_cart = $this->request->Session()->read('pf_quick_cart');
		$prodErrArr = $itemArr = array();
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		if($kiosk_id < 10000 && $kiosk_id != 0 && !empty($kiosk_id)){
            $productsTable = TableRegistry::get("kiosk_{$kiosk_id}_products",[
																		'table' => "kiosk_{$kiosk_id}_products",
																	]);
			//$this->Product->setSource("kiosk_{$kiosk_id}_products");
		}else{
            $productsTable = TableRegistry::get("products",[
																		'table' => "products",
																	]);
        }
        $prodRow_query = $productsTable->find('all', array('conditions' => array('product_code' => $product_code),'recursive' => -1));
        $prodRow_query->hydrate(false);
        $prodRow = $prodRow_query->first();
		 
		//--------price without vat and discounted value---------
		$vat = $this->VAT;
		if(count($prodRow) >= 1){
			$numerator = $prodRow['selling_price']*100;
			$denominator = $vat+100;
			$priceWithoutVat = $numerator/$denominator;
			$priceWithoutVat = round($priceWithoutVat,2);
			if($prodRow['discount_status'] == 1){
				$disValue = $priceWithoutVat * $prodRow['discount']/100;
				$netVal = $priceWithoutVat - $disValue;
			}else{
				$netVal = 0;
			}
		}else{
			$prodErrArr['error'] = "Product either out of stock or invalid product code!";
		}
		
		if(is_array($quick_cart) && count($quick_cart) > 0){
			//pr($quick_cart);
			$items = $quick_cart['new_sale_basket'];
			if(count($prodRow) >= 1 && $prodRow['quantity']){
				//quanity should not be 0
				$prod_id = $prodRow['id'];
				//pr($quick_cart);echo "prod id : $prod_id";pr($items);
				if(array_key_exists($prod_id, $items)){
					//echo "updating quantity for added item";
					//updating only quantity
					$totalQty = $items[$prod_id]['quantity'] + $quantity;
					if($totalQty > $prodRow['quantity']){
						$availQty = $prodRow['quantity'];
						$items[$prod_id]['quantity'] = $availQty; //updating qty
						$qantity_short = 1;
						$prodErrArr['error'] = "Product Quantity adjusted to $availQty due to limited stock";
					}else{
						$items[$prod_id]['quantity'] = $totalQty; //updating qty
						$qantity_short = 0;
					}
					$quick_cart['position'] += 1; //updating position
					$items[$prod_id]['position'] = $quick_cart['position']; //updating position
					$items[$prod_id]['qantity_short'] = $qantity_short;
					$quick_cart['new_sale_basket'] = $items;
				}else{
					//add new item to existing cart
					//echo "adding item to existing cart";
					$qantity_short = 0;
					if(array_key_exists('position',$quick_cart)){
						$quick_cart['position'] += 1; //updating position
					}else{
						$quick_cart['position'] = 1; //updating position
					}
					
					if($quantity > $prodRow['quantity']){
						$availQty = $prodRow['quantity'];
						$quantity = $availQty; //updating qty
						$qantity_short = 1;
						$prodErrArr['error'] = "Product Quantity adjusted to $availQty due to limited stock!";
					}
					$items[$prod_id] = array(
											'product' => $prodRow['product'],
											'quantity' => $quantity, //coming from box adjacent to google suggest
											'discount_status' => $prodRow['discount_status'],
											'net_amount' => $prodRow['selling_price'],
											'selling_price' => $priceWithoutVat,//$prodRow['Product']['selling_price'],
											'discount' => '',
											'price_without_vat' => $priceWithoutVat,
											'product_code' => $prodRow['product_code'],
											'position' => $quick_cart['position'],
											'available_qantity' => $prodRow['quantity'],
											'discounted_value' => $netVal, //check
											'minimum_selling_price' => $netVal, //check
											//'price_without_vat' => $priceWithoutVat, //check
											'cost_price' => $prodRow['cost_price'],
										);
					$items[$prod_id]['qantity_short'] = $qantity_short;
					$quick_cart['new_sale_basket'] = $items;
				}
			}else{
				$prodErrArr['error'] = "Product either out of stock or invalid product code!";
			}
		}else{
			//create new cart
			//echo "create new cart";
			if(count($prodRow) >= 1 && $prodRow['quantity']){
				//quanity should not be 0
				$quick_cart['position'] = 1;
				$prod_id = $prodRow['id'];
				$qantity_short = 0;
				if($quantity > $prodRow['quantity']){
					$availQty = $prodRow['quantity'];
					$quantity = $availQty; //updating qty
					$qantity_short = 1;
					$prodErrArr['error'] = "Product Quantity adjusted to $availQty due to limited stock!";
				}
				$itemArr[$prod_id] = array(
											'product' => $prodRow['product'],
											'quantity' => $quantity,
											'discount_status' => $prodRow['discount_status'],
											'net_amount' => $prodRow['selling_price'],
											'selling_price' => $priceWithoutVat,//$prodRow['Product']['selling_price'],
											'discount' => '',
											'price_without_vat' => $priceWithoutVat,
											'product_code' => $prodRow['product_code'],
											'position' => $quick_cart['position'],
											'available_qantity' => $prodRow['quantity'],
											'discounted_value' => $netVal, //check
											'minimum_selling_price' => $netVal, //check
											'price_without_vat' => $priceWithoutVat, //check
											'cost_price' => $prodRow['cost_price'],
										);
				$items[$prod_id]['qantity_short'] = $qantity_short;
				$quick_cart['new_sale_basket'] = $itemArr;
			}else{
				$prodErrArr['error'] = "Product either out of stock or invalid product code!";
				$quick_cart = array('new_sale_basket' => array());
				$this->pf_reorder_cart($quick_cart, $prodErrArr);
				//echo json_encode(array('msg' => 'Nothing to restore'));
				//$this->layout = false;
				//die;
			}
		}
		$quick_cart['new_sale_bulk_discount'] = $bulk_discount;
		$quick_cart['receipt_required'] = $recept_req;
		$quick_cart['special_invoice'] = $special_invoice;
		$this->pf_reorder_cart($quick_cart, $prodErrArr);
	}
    private function pf_reorder_cart($quick_cart, $prodErrArr = array()){
		$items = $sortedItems = $posArr = array();
        if(array_key_exists("new_sale_basket",$quick_cart)){
            $items = $quick_cart['new_sale_basket'];    
        }
		
		if(!is_array($items)){
			$items = array();
		}else{
			foreach($items as $item){ $posArr[] = $item['position']; }
			rsort($posArr);
		
			foreach($posArr as $pos){
				foreach($items as $key => $item){
					if($item['position'] == $pos){
						$sortedItems[$key] = $item;
						unset($items[$key]);
						break;
					}
				}
			}
			$quick_cart['new_sale_basket'] = $sortedItems;
			$this->request->Session()->write('pf_quick_cart',$quick_cart);
		}
		
		//pr($_SESSION);die;
		//$quick_cart['new_sale_basket'] = array($sortedItems);
		$cart = $quick_cart;
		if(count($prodErrArr)){
			$quick_cart['prodError'] = $prodErrArr['error'];
			echo $quickCart = json_encode($quick_cart);
		}else{
			echo $quickCart = json_encode($quick_cart);
		}
		$serilized_data = serialize($cart);
		//--------------------------------
		if(count($sortedItems) >= 1){
            $kiosk_id = $this->request->Session()->read('kiosk_id');
			if($kiosk_id == ""){
				$kiosk_id = 10000;
			}
            $sessionRS_query = $this->SessionBackups->find('all', array('conditions' => array(
																				'controller' => 'Home',
																				'action' => 'pf_reorder_cart',
																				'kiosk_id' => $kiosk_id,
																				'user_id' => $this->request->session()->read('Auth.User.id'),
																			)));
            $sessionRS = $sessionRS_query->first();
			if(count($sessionRS) >= 1){
				//update record
				$this->SessionBackups->id = $sessionRS->id;
				$data = array('session_detail' => $serilized_data);
				$data = $this->SessionBackups->patchEntity($sessionRS, $data);
				$this->SessionBackups->save($data);
			}else{
				//add record
				 $kiosk_id = $this->request->Session()->read('kiosk_id');
					if($kiosk_id == ""){
						$kiosk_id = 10000;
					}
				$sessionBackupData = array(
										'controller' => 'Home',
										'action' => 'pf_reorder_cart',
										'session_key' => 'any_key',
										'session_detail' => $serilized_data,
										'user_id' => $this->Auth->user('id'),
										'kiosk_id' => $kiosk_id
										);
				$SessionBackups = $this->SessionBackups->newEntity();
				$SessionBackups = $this->SessionBackups->patchEntity($SessionBackups, $sessionBackupData);
				$this->SessionBackups->save($SessionBackups);
			}
		}
		//--------------------------------
		//$this->layout = false;
		$this->viewBuilder()->layout(false);
		die;
		 
	}
    
    public function pfUpdateCart(){
		extract($this->request->query);
		if(empty($bulk)){$bulk = "";}
		$quick_cart = $this->request->Session()->read('pf_quick_cart');
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		if($kiosk_id < 10000 && $kiosk_id != 0 && !empty($kiosk_id)){
            $productsTable = TableRegistry::get("kiosk_{$kiosk_id}_products",[
																		'table' => "kiosk_{$kiosk_id}_products",
																	]);
			//$this->Product->setSource("kiosk_{$kiosk_id}_products");
		}else{
            $productsTable = TableRegistry::get("products",[
																		'table' => "products",
																	]);
        }
        $prodRow_query = $productsTable->find('all', array('conditions' => array('id' => $prod_id),'recursive' => -1));
         $prodRow_query->hydrate(false);
        $prodRow = $prodRow_query->first();
		 
		//pr($prodRow);die;
		$databse_qantity = $prodRow['quantity'];
		if(is_array($quick_cart) && count($quick_cart) > 0){
			if($qty > $databse_qantity){
				$allowed_qtity = $databse_qantity;
				$qantity_short = 1;
			}else{
				$allowed_qtity = $qty;
				$qantity_short = 0;
			}
			if(!array_key_exists('position', $quick_cart)){
				$quick_cart['position'] = 1;
			}else{
				$quick_cart['position'] += 1; //updating position
			}
			$quick_cart['new_sale_bulk_discount'] = $bulk;
			$items = $quick_cart['new_sale_basket'];
			$items[$prod_id]['quantity'] = $allowed_qtity;
			$items[$prod_id]['position'] = $quick_cart['position'];
			$items[$prod_id]['selling_price'] = $sp;
			$items[$prod_id]['qantity_short'] = $qantity_short;
			//$items[$prod_id]['type'] = $type;
			$quick_cart['new_sale_basket'] = $items;
			$quick_cart['receipt_required'] = $recit_req;
			$quick_cart['special_invoice'] = $special_invoice;
		}
		//pr($quick_cart);die;
		$this->pf_reorder_cart($quick_cart);
		die;
	}
    function pfDeleteFromCart(){
		extract($this->request->query);
		$quick_cart = $this->request->Session()->read('pf_quick_cart');
		if(array_key_exists($prod_id,$quick_cart['new_sale_basket'])){
			unset($_SESSION['pf_quick_cart']['new_sale_basket'][$prod_id]);
		}
		$quick_cart['new_sale_basket'] = $_SESSION['pf_quick_cart']['new_sale_basket'];
		$this->pf_reorder_cart($quick_cart);
		 $this->viewBuilder()->layout(false);
		die;
	}
    
    function pfAdd2CartFull(){
		//$this->Session->delete('quick_cart');
		extract($this->request->query);
		$quick_cart = $this->request->Session()->read('pf_quick_cart');
		$itemArr = array();
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		if($kiosk_id < 10000 && $kiosk_id != 0 && !empty($kiosk_id)){
            $productsTable = TableRegistry::get("kiosk_{$kiosk_id}_products",[
																		'table' => "kiosk_{$kiosk_id}_products",
																	]);
			//$this->Product->setSource("kiosk_{$kiosk_id}_products");
		}else{
            $productsTable = TableRegistry::get("products",[
																		'table' => "products",
																	]);
        }
        $prodRow_query = $productsTable->find('all', array('conditions' => array('id' => $prod_id),'recursive' => -1));
         $prodRow_query->hydrate(false);
        $prodRow = $prodRow_query->first();
		//$prodRow = $this->Product->find('first', array('conditions' => array('id' => $prod_id),'recursive' => -1));
		//pr($prodRow);die;
		//Case: validation needs to be added for quantity check
		if(is_array($quick_cart) && count($quick_cart) > 0){
			$items = $quick_cart['new_sale_basket'];
			if(count($prodRow) >= 1){
				if(array_key_exists($prod_id, $items)){
					$cart_qty = $items[$prod_id]['quantity'];
					$orig_qty = $cart_qty + $qty;
					$databse_qantity = $prodRow['quantity'];
					if($orig_qty > $databse_qantity){
						$allowed_qtity = $databse_qantity;
						$qantity_short = 1;
					}else{
						$qantity_short = 0;
						$allowed_qtity = $orig_qty;
					}
					//updating only quantity
					$quick_cart['position'] += 1; //updating position
					$items[$prod_id]['quantity'] = $allowed_qtity;
					$items[$prod_id]['position'] = $quick_cart['position']; //updating position
					$items[$prod_id]['qantity_short'] = $qantity_short; // qantites are short
					$quick_cart['new_sale_basket'] = $items;
				}else{
					$databse_qantity = $prodRow['quantity'];
					if($qty > $databse_qantity){
						$allowed_qtity = $databse_qantity;
						$qantity_short = 1;
					}else{
						$allowed_qtity = $qty;
						$qantity_short = 0;
					}
					//add new item to existing cart
					$quick_cart['position'] += 1; //updating position
					$items[$prod_id] = array(
											'product' => $prodRow['product'],
											'quantity' => $allowed_qtity,
											'discount_status' => $prodRow['discount_status'],
											'net_amount' => $prodRow['selling_price'],
											'selling_price' => $sp,//$prodRow['Product']['selling_price'],
											'discount' => '',
											'price_without_vat' => '',
											'product_code' => $prodRow['product_code'],
											'position' => $quick_cart['position'],
											'minimum_selling_price' => $min_dis,
											'available_qantity' => $prodRow['quantity'],
											'cost_price' => $prodRow['cost_price'],
										);
					$items[$prod_id]['qantity_short'] = $qantity_short;
					$quick_cart['new_sale_basket'] = $items;
					$quick_cart['position'] = $quick_cart['position'];
				}
			}
		}else{
			//create new cart
			if(count($prodRow) >= 1){
				$databse_qantity = $prodRow['quantity'];
				if($qty > $databse_qantity){
					$allowed_qtity = $databse_qantity;
					$qantity_short = 1;
				}else{
					$allowed_qtity = $qty;
					$qantity_short = 0;
				}
				$quick_cart['position'] = 1;
				$itemArr[$prod_id] = array(
											'product' => $prodRow['product'],
											'quantity' => $allowed_qtity,
											'discount_status' => $prodRow['discount_status'],
											'net_amount' => $prodRow['selling_price'],
											'selling_price' => $sp,//$prodRow['Product']['selling_price'],
											'discount' => '',
											'price_without_vat' => '',
											'product_code' => $prodRow['product_code'],
											'position' => $quick_cart['position'],
											'minimum_selling_price' => $min_dis,
											'available_qantity' => $prodRow['quantity'],
											'cost_price' => $prodRow['cost_price'],
										);
				$items[$prod_id]['qantity_short'] = $qantity_short;
				$quick_cart['new_sale_basket'] = $itemArr;
			}else{
				echo json_encode(array('msg' => 'Nothing to restore'));
			}
		}
		if(isset($bulk_discount))$quick_cart['new_sale_bulk_discount'] = $bulk_discount;
		if(isset($recept_req))$quick_cart['receipt_required'] = $recept_req;
		if(isset($special_invoice))$quick_cart['special_invoice'] = $special_invoice;
		$this->pf_reorder_cart($quick_cart);
	}
    
    public function savePerforma(){
		// echo "<pre>";print_r($_SESSION);die;
		if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
			$kiosk_id = 0; // kiosk users are currently not considered
		}else{
			$kiosk_id = $this->request->Session()->read('kiosk_id');
		}
		
		
		
		if(!empty($kiosk_id)){
			$this->loadModel("InvoiceOrder");
			$this->loadModel("InvoiceOrderDetail");
			$InvoiceOrder_source = "kiosk_{$kiosk_id}_invoice_orders";
			$InvoiceOrderDetail_source = "kiosk_{$kiosk_id}_invoice_order_details";
		}else{
			$InvoiceOrder_source = "invoice_orders";
			$InvoiceOrderDetail_source = "invoice_order_details";
		}
		$InvoiceOrderTable = TableRegistry::get($InvoiceOrder_source,[
																		'table' => $InvoiceOrder_source,
																	]);
		$InvoiceOrderDetailTable = TableRegistry::get($InvoiceOrderDetail_source,[
																		'table' => $InvoiceOrderDetail_source,
																	]);
        
		$new_kiosk_id = $kiosk_id;
		if($new_kiosk_id == 0){
			$new_kiosk_id = 10000;
		}
		$new_kiosk_data = $this->Kiosks->find("all",['conditions' => ['id' => $new_kiosk_id]])->toArray();
		
		$user_id = $this->Auth->user('id');
		$bulk_dis = 0;
		if(array_key_exists("pf_quick_cart",$_SESSION)){
			$quick_cart = $this->request->Session()->read('pf_quick_cart');
			$quick_cart_serilize = serialize($quick_cart);
			//pr($quick_cart);die;
			$special_invoice = $quick_cart['special_invoice'];
			$bulk_dis = $quick_cart['new_sale_bulk_discount'];
		}else{
			$quick_cart_serilize = "";
			$this->Flash->error("no basket found");
			return $this->redirect(array('controller'=>'customers','action'=>'index'));die;
		}
		//pr($_SESSION);die;
		if(array_key_exists("pf_customer",$_SESSION)){
			$cust_info = $_SESSION['pf_customer'];
		}else{
			$this->Flash->error("no customer found");
			return $this->redirect(array('controller'=>'customers','action'=>'index'));die;
		}
		$invoiceOrdersData = array(
						'session_cart' => $quick_cart_serilize,
						'kiosk_id' => $kiosk_id,
						'user_id' => $user_id,
						'customer_id' => $cust_info['id'],
						'fname' => $cust_info['fname'],
						'lname' => $cust_info['lname'],
						'email' => $cust_info['email'],
						'mobile' => $cust_info['mobile'],
						'bulk_discount' => $bulk_dis,
						'del_city' => $cust_info['del_city'],
						'del_state' => $cust_info['del_state'],
						'del_zip' => $cust_info['del_zip'],
						'del_address_1' => $cust_info['del_address_1'],
						'del_address_2' => $cust_info['del_address_2'],
						'invoice_status' => 0//for the time being sending 0
					     );
		$InvoiceOrderTable->behaviors()->load('Timestamp');
		$newEntity = $InvoiceOrderTable->newEntity();
		$patchEntity = $InvoiceOrderTable->patchEntity($newEntity,$invoiceOrdersData);
		//pr($patchEntity);die;
		$res = $InvoiceOrderTable->save($patchEntity);
		$invoiceOrderId = $res->id;
		$basket = $quick_cart['new_sale_basket'];
		$finalAmount = $count = $total_amount = 0;
		$vat = $this->VAT;
		//pr($basket);die;
		foreach($basket as $key => $value){
			$minimum_price = $value['minimum_selling_price'];
			$quantity = $withVatValue = $numerator = $denominator = $priceWithoutVat = $sold_price = $firstVal = $discountPercentage = 0;
			$product_id = $key;
			$quantity = $value['quantity'];
				$withVatValue = $value['net_amount'];
				$numerator = $withVatValue*100;
				$denominator = $vat+100;
				$priceWithoutVat = $numerator/$denominator;
				$priceWithoutVat = round($priceWithoutVat,2);
				
				$sold_price = $value['selling_price'];
				$firstVal = $priceWithoutVat - $sold_price;
				
				
				//echo "$firstVal/$priceWithoutVat*100";echo "</br>";
				$discountPercentage = $firstVal/$priceWithoutVat*100;
				
				if($discountPercentage > 0){
					$dis_status = 1;
				}else{
					//$discountPercentage = 0;
					$dis_status = 0;
				}
				if($sold_price > $priceWithoutVat){
					if($discountPercentage < 0){
						$selling_price = $sold_price; 
					}else{
						$selling_price = $sold_price + $sold_price*($vat/100);	
					}
				}else{
					//$withVatValue = $withVatValue+($withVatValue*$vat/100);
					$selling_price = $withVatValue;
				}
				//echo $selling_price;die;
				$orderDetailData = array(
							'kiosk_id' => $kiosk_id,
							'invoice_order_id' => $invoiceOrderId,
							'price' => $selling_price,
							'quantity' => $quantity,
							'product_id' => $product_id,
							'discount' => (float)$discountPercentage,
							'discount_status' => $dis_status,
						);
				//echo $sold_price;die;
				//echo $sold_price; echo  "</br>";
				$finalAmount += $sold_price*$quantity;
				//pr($InvoiceOrderDetailTable);die;
				$InvoiceOrderDetailTable->behaviors()->load('Timestamp');
				$new_entity = $InvoiceOrderDetailTable->newEntity();
				$patch_entity = $InvoiceOrderDetailTable->patchEntity($new_entity,$orderDetailData,['validate' => false]);
				if($InvoiceOrderDetailTable->save($patch_entity)){
					$count++;
				}else{
					pr($patch_entity->errors());die;
				}
				
		}
		// echo $finalAmount;die;
		if($bulk_dis > 0){
			$bulk_dis_value = $finalAmount*$bulk_dis/100;
			$finalAmount = $finalAmount - $bulk_dis_value;
		}
		// echo $finalAmount;die;
		if($cust_info['country'] != "OTH"){
			$finalAmount = $finalAmount + ($finalAmount*$vat/100);
		}
		if($count >0 ){
			$dataArr = array('amount'=>number_format($finalAmount,2),'bulk_discount'=>$bulk_dis);
        //    pr($dataArr);die;
			$getId = $InvoiceOrderTable->get($invoiceOrderId);
			$patch_E = $InvoiceOrderTable->patchEntity($getId,$dataArr);
				$InvoiceOrderTable->save($patch_E);
				$this->request->Session()->delete('pf_quick_cart');
				$this->request->Session()->delete('pf_customer');
				$recipt_req = $quick_cart['receipt_required'];
				if($recipt_req == 1){
					$invoiceOrder_query = $InvoiceOrderTable->find('all',array(
										'conditions' => array('id'=>$invoiceOrderId)
											)
									  );
					$invoiceOrder_query = $invoiceOrder_query->hydrate(false);
					if(!empty($invoiceOrder_query)){
						$invoiceOrder = $invoiceOrder_query->first();
					}else{
						$invoiceOrder = array();
					}
                    
                    
                    $cust_id = $invoiceOrder['customer_id'];
                    $user_id = $invoiceOrder['user_id'];
                    $user_query = $this->Users->find('all',array('conditions' => array('id' => $user_id)));
                    $user_query = $user_query->hydrate(false);
                    if(!empty($user_query)){
                        $user_data = $user_query->first();
                    }else{
                        $user_data = array();
                    }
                    
                    $InvoiceOrderDetail_query = $InvoiceOrderDetailTable->find("all",array('conditions' => array('invoice_order_id' => $invoiceOrderId)));
                    //pr($InvoiceOrderDetail_query);die;
                    $InvoiceOrderDetail_query = $InvoiceOrderDetail_query->hydrate(false);
                    if(!empty($InvoiceOrderDetail_query)){
                        $InvoiceOrderDetail_data = $InvoiceOrderDetail_query->toArray();
                    }else{
                        $InvoiceOrderDetail_data = array();
                    }
                    
					$productIDs = $productName = array();
					foreach($InvoiceOrderDetail_data as $key =>$sngData){
						$productIDs[] = $sngData['product_id'];
					}
					$userName = ucfirst($user_data['username']);
					$products_query = $this->Products->find('all',array(
									 'fields'=> array('id','product','product_code'),
									 'conditions' => array('Products.id IN' => $productIDs)
									 ));
					$products_query = $products_query->hydrate(false);
					if(!empty($products_query)){
						$products = $products_query->toArray();
					}else{
						$products = array();
					}
			
					foreach($products as $product){			
						$productName[$product['id']] = array($product['product_code'],
												$product['product']);
					}
					
					$customerData_query = $this->Customers->find('all',array(
										    'conditions' => array('id' => $cust_id)
										    ));
					$customerData_query = $customerData_query->hydrate(false);
					if(!empty($customerData_query)){
						$customerData = $customerData_query->first();
					}else{
						$customerData = array();
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
					
					$send_by_email = Configure::read('send_by_email');
					$emailSender = Configure::read('EMAIL_SENDER');
					
					$Email = new Email();
					$Email->config('default');
					$Email->viewVars(array('invoiceOrder' => $invoiceOrder,'productName' => $productName,'vat' => $this->VAT, 'settingArr' => $this->setting, 'customerData' => $customerData,'userName' => $userName,'kioskContact'=>$kioskContact,'kioskTable'=>$kioskTable,'countryOptions'=>$countryOptions,'InvoiceOrderDetail_data'=>$InvoiceOrderDetail_data,'new_kiosk_data' => $new_kiosk_data));
					//$Email->config(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
					//$Email = new CakeEmail(array('from' => 'me@example.org', 'transport' => 'MyCustom'));
					$emailTo = $invoiceOrder['email'];
					$Email->template('performa');
					$Email->emailFormat('both');
					$Email->to($emailTo);
					$Email->transport(TRANSPORT);
					$Email->from([$send_by_email => $emailSender]);
					$Email->subject('Performa Details');
					$Email->send();
				}
				
				 $kiosk_id = $this->request->Session()->read('kiosk_id');
				 if($kiosk_id == ""){
					$kiosk_id = 10000;
				 }
				
                $this->SessionRestore->delete_from_session_backup_table('Home', 'pf_reorder_cart', 'any_key', $kiosk_id);
				$this->Flash->success("Performa Created");
				return $this->redirect(array('controller'=>'invoice-orders','action'=>'view',$invoiceOrderId)); //'controller'=>'customers','action'=>'index'
		}else{
			$this->Flash->error("Failed to generate Performa");
			return $this->redirect(array('controller'=>'customers','action'=>'index'));
		}
		
	}
    
    public function pfClearCart(){
        $this->request->Session()->delete('pf_quick_cart');
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		if($kiosk_id == ""){
			$kiosk_id = 10000;
		}
		$this->SessionRestore->delete_from_session_backup_table('Home', 'pf_reorder_cart', 'any_key', $kiosk_id);
		echo json_encode(array('msg' => 'Cart Cleared'));
		//$this->layout = false;
		die;
    }
	
	public function crUpdateType(){
		$id = $_REQUEST['id'];
		$value = $_REQUEST['value'];
		$quick_cart = $this->request->Session()->read('cr_quick_cart');
		if(!empty($quick_cart)){
			if(array_key_exists('new_sale_basket',$quick_cart)){
				if(array_key_exists($id,$quick_cart['new_sale_basket'])){
					$quick_cart['new_sale_basket'][$id]['type'] = $value;
					$prodErrArr = array();
					$this->cr_reorder_cart($quick_cart, $prodErrArr);
				}else{
					$prodErrArr = array("no item found");
					$this->cr_reorder_cart($quick_cart, $prodErrArr);
				}
			}else{
					$prodErrArr = array("no item found");
					$this->cr_reorder_cart($quick_cart, $prodErrArr);
			}
		}else{
			$prodErrArr = array("no basket found");
					$this->cr_reorder_cart($quick_cart, $prodErrArr);
		}
	}
	
	public function index(){ 
	
    }
	
	
	public function freshArrival(){
        $vat = $this->setting['vat'];
		$CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
		$other_sites = Configure::read('sites');
        $siteBaseUrl = Configure::read('SITE_BASE_URL');
		$path = dirname(__FILE__);
		$ext_site = 0;
        if(!empty($other_sites)){
            foreach($other_sites as $site_id => $site_name){
                  $isboloRam = strpos($path,$site_name);
                  if($isboloRam != false){
                      $ext_site = 1;
                  }
            }
        }
		if($ext_site == 1){
			$conn = ConnectionManager::get('hpwaheguru');
			$this->Settings->connection($conn);
			$this->Products->connection($conn);
		}
		
		$settingsArr = $this->Settings->find("list",['keyField' => 'attribute_name',
									  'valueField' => 'attribute_value',
									 ]
							  )->toArray();
		$days = $settingsArr['new_arrival_days_limit'];
		$products_query = $this->Products->find('all',array('conditions'=>array(
					'Products.qty_update_status' => 1,
				    "DATE(Products.qty_update_time) >= DATE_ADD(CURDATE(), INTERVAL -$days DAY)"
							 ),
		 'fields' => array('id','product','image','selling_price', 'product_code', 'category_id','created', 'modified', 'quantity','qty_update_time'),
		 'recursive' => -1));
		$products_query = $products_query->hydrate(false);
		if(!empty($products_query)){
			$products = $products_query->toArray();
		}else{
			$products = array();
		}
		$productNofification = array();
		$categoryIds = array();
		//grabbing the category id of the newly entered products
		$product_ids = array();
		foreach($products as $key=>$product){
		    $categoryIds[$product['category_id']] = $product['category_id'];
			$product_ids[$product['id']] = $product['id'];
		}
		
		foreach($products as $key=>$product){
			$productNofification[$key]['id'] = $product['id'];
			$productNofification[$key]['image'] = $product['image'];
			$productNofification[$key]['Product'] = $product['product'];
			$productNofification[$key]['selling_price'] =  $product['selling_price'];
			$productNofification[$key]['product_code'] = $product['product_code'];
			$productNofification[$key]['category_id'] = $product['category_id'];
			$productNofification[$key]['created'] = $product['qty_update_time'];
			$productNofification[$key]['modified'] = $product['modified'];
			$productNofification[$key]['quantity'] = $product['quantity'];
		}
		
		$session_basket = $this->request->Session()->read('on_demand_basket_new_arrival');
		
		if(is_array($session_basket) && !empty($session_basket)){
			$val = array_keys($session_basket);
			if(empty($val)){
				$val = array(0 => null);
			}
			$productCodeArr_query = $this->Products->find('list',
															[
																'keyField' => 'product_code',
																'valueField' => 'id',
																'conditions'=>['product_code IN'=>$val],
															]
												  );
			$productCodeArr_query = $productCodeArr_query->hydrate(false);
			if(!empty($productCodeArr_query)){
				$productCodeArr = $productCodeArr_query->toArray();
			}else{
				$productCodeArr = array();
			}
			
			
			$costSellingData_query = $this->Products->find('all',array('fields'=>array('product_code','cost_price','selling_price','product','Quantity'),'conditions'=>array('product_code IN'=>$val),'recursive'=>-1));
			$costSellingData_query = $costSellingData_query->hydrate(false);
			$costSellingData = $costSellingData_query->toArray();
			//pr($costSellingData);die;
			$costArr = array();
			$sellingArr = array();
			$productNameArr = array();
			if($costSellingData){
				foreach($costSellingData as $key=>$costSellingInfo){
				$costArr[$costSellingInfo['product_code']] = $costSellingInfo['cost_price'];
				$sellingArr[trim($costSellingInfo['product_code'])] = $costSellingInfo['selling_price'];
				$productNameArr[trim($costSellingInfo['product_code'])] = $costSellingInfo['product'];
				$productQuantityArr[$costSellingInfo['product_code']] = $costSellingInfo['Quantity'];
				}
			}
			$basketStr = '';
			$counter=0;
			foreach($session_basket as $productCode=>$productData){
				$counter++;
			$basketStr.="<tr>
							<td>$counter</td>
							<td>{$productCode}</td>
							<td>".$productNameArr[$productCode]."</td>
							<td>".$CURRENCY_TYPE.number_format($sellingArr[$productCode],2)."</td>
							<td>".$productData['quantity']."</td></tr>";
							//<td>".$productData['difference']."</td>
			}
			
			if(!empty($basketStr)){
				$basketStr = "<table><tr><th>Sr</br>No.</th><th style='width: 125px;'>Product code</th><th>Product name</th><th style='width: 74px;'>SP</th><th style='width: 46px;'>Qty</th></tr>".$basketStr."</table>";
				$basketStr = trim(str_replace(array("\n", "\r", "\t"), '', $basketStr));
				
			}else{
				$basketStr = "";
			}
	    }else{
			$basketStr = "";
		}
		$conn = ConnectionManager::get('default');
			$this->Settings->connection($conn);
			$this->Products->connection($conn);
			if(empty($product_ids)){
				$product_ids = array(0 => null);
			}
		$selling_price_arr = $this->Products->find('list',['conditions' => ['id IN' =>  $product_ids],
									  'keyField' => 'id',
									  'valueField' => 'selling_price',
									  ])->toArray();
		//$price_arr = $this->Products->find('all',['conditions' => ['id IN' =>  $product_ids]
		//							  ])->toArray();
		//if(!empty($price_arr)){
			//foreach($price_arr as $key => $productData){
				//
			//}
		//}
		//$selling_price_arr = 
		$this->set(compact('currency','productNofification','basketStr','session_basket','selling_price_arr', 'siteBaseUrl', 'vat'));		
	}
	
	
	public function updateSessionAjax(){
		$CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');	
		$product_code = $this->request->query['prod_code'];
		$req_qty = $this->request->query['qty'];
		$product_id = $this->request->query['product_id'];
		$product_price = $this->request->query['product_price'];
		$kiosk_id = $this->request->query['kiosk_id'];
		
		$productArr[$product_code] = array(
										'quantity' => $req_qty,
										'selling_price' => $product_price,
										'id' => $product_id,
										);
		$session_basket = $this->request->Session()->read('on_demand_basket_new_arrival');
		if(count($session_basket) >= 1){
			//adding item to the the existing session
			$sum_total = $this->add_arrays(array($productArr,$session_basket));
			$this->request->Session()->write('on_demand_basket_new_arrival', $sum_total);
			$session_basket = $this->request->Session()->read('on_demand_basket_new_arrival');				
		}else{
			//adding item first time to session
			//pr($productArr);die;
			if(count($productArr))$this->request->Session()->write('on_demand_basket_new_arrival', $productArr);
		}
		
		$session_basket = $this->request->Session()->read('on_demand_basket_new_arrival');
		if(is_array($session_basket) && !empty($session_basket)){
			$val = array_keys($session_basket);
			if(empty($val)){
				$val = array(0 => null);
			}
			$productCodeArr_query = $this->Products->find('list',
															[
																'keyField' => 'product_code',
																'valueField' => 'id',
																'conditions'=>['product_code IN'=>$val],
															]
												  );
			$productCodeArr_query = $productCodeArr_query->hydrate(false);
			if(!empty($productCodeArr_query)){
				$productCodeArr = $productCodeArr_query->toArray();
			}else{
				$productCodeArr = array();
			}
			
			
			$costSellingData_query = $this->Products->find('all',array('fields'=>array('product_code','cost_price','selling_price','product','Quantity'),'conditions'=>array('product_code IN'=>$val),'recursive'=>-1));
			$costSellingData_query = $costSellingData_query->hydrate(false);
			$costSellingData = $costSellingData_query->toArray();
			//pr($costSellingData);die;
			$costArr = array();
			$sellingArr = array();
			$productNameArr = array();
			if($costSellingData){
				foreach($costSellingData as $key=>$costSellingInfo){
				$costArr[$costSellingInfo['product_code']] = $costSellingInfo['cost_price'];
				$sellingArr[trim($costSellingInfo['product_code'])] = $costSellingInfo['selling_price'];
				$productNameArr[trim($costSellingInfo['product_code'])] = $costSellingInfo['product'];
				$productQuantityArr[$costSellingInfo['product_code']] = $costSellingInfo['Quantity'];
				}
			}
			$basketStr = '';
			$counter=0;
			
			foreach($session_basket as $productCode=>$productData){
				$counter++;
			$basketStr.="<tr>
							<td>$counter</td>
							<td>{$productCode}</td>
							<td>".$productNameArr[$productCode]."</td>
							<td>".$CURRENCY_TYPE.number_format($sellingArr[$productCode],2)."</td>
							<td>".$productData['quantity']."</td></tr>";
							//<td>".$productData['difference']."</td>
			}
			
			if(!empty($basketStr)){
				$basketStr = "<table><tr><th>Sr</br>No.</th><th style='width: 125px;'>Product code</th><th>Product name</th><th style='width: 74px;'>SP</th><th style='width: 46px;'>Qty</th></tr>".$basketStr."</table>";
				$basketStr = trim(str_replace(array("\n", "\r", "\t"), '', $basketStr));
				echo json_encode(array("basket" => $basketStr));
			}else{
				echo json_encode(array('basket' => 'No Items in the basket'));
			}
	    }else{
			echo json_encode(array('basket' => 'No Items in the basket'));
		}
		die;
		
	}
	
	private function add_arrays($arrays = array()){
        $allValues = array();
        $arrays = array_reverse($arrays ,true);
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
    }//end of custom code by rasa
	
	
	public function unsetSessionAjax(){
		$CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
		$product_code = $this->request->query['prod_code'];
		$req_qty = $this->request->query['qty'];
		$product_id = $this->request->query['product_id'];
		$product_price = $this->request->query['product_price'];
		$session_basket = $this->request->Session()->read("on_demand_basket_new_arrival");
		if(!empty($session_basket)){
			if(array_key_exists($product_code,$session_basket)){
				unset($session_basket[$product_code]);
			}
		}
		
		$this->request->Session()->write('on_demand_basket_new_arrival', $session_basket);
		$session_basket = $this->request->Session()->read('on_demand_basket_new_arrival');
		
		if(is_array($session_basket) && !empty($session_basket)){
			$val = array_keys($session_basket);
			if(empty($val)){
				$val = array(0 => null);
			}
			$productCodeArr_query = $this->Products->find('list',
															[
																'keyField' => 'product_code',
																'valueField' => 'id',
																'conditions'=>['product_code IN'=>$val],
															]
												  );
			$productCodeArr_query = $productCodeArr_query->hydrate(false);
			if(!empty($productCodeArr_query)){
				$productCodeArr = $productCodeArr_query->toArray();
			}else{
				$productCodeArr = array();
			}
			
			
			$costSellingData_query = $this->Products->find('all',array('fields'=>array('product_code','cost_price','selling_price','product','Quantity'),'conditions'=>array('product_code IN'=>$val),'recursive'=>-1));
			$costSellingData_query = $costSellingData_query->hydrate(false);
			$costSellingData = $costSellingData_query->toArray();
			//pr($costSellingData);die;
			$costArr = array();
			$sellingArr = array();
			$productNameArr = array();
			if($costSellingData){
				foreach($costSellingData as $key=>$costSellingInfo){
				$costArr[$costSellingInfo['product_code']] = $costSellingInfo['cost_price'];
				$sellingArr[trim($costSellingInfo['product_code'])] = $costSellingInfo['selling_price'];
				$productNameArr[trim($costSellingInfo['product_code'])] = $costSellingInfo['product'];
				$productQuantityArr[$costSellingInfo['product_code']] = $costSellingInfo['Quantity'];
				}
			}
			$basketStr = '';
			$counter=0;
			foreach($session_basket as $productCode=>$productData){
				$counter++;
			$basketStr.="<tr>
							<td>$counter</td>
							<td>{$productCode}</td>
							<td>".$productNameArr[$productCode]."</td>
							<td>".$CURRENCY_TYPE.number_format($sellingArr[$productCode],2)."</td>
							<td>".$productData['quantity']."</td></tr>";
							//<td>".$productData['difference']."</td>
			}
			
			if(!empty($basketStr)){
				$basketStr = "<table><tr><th>Sr</br>No.</th><th style='width: 125px;'>Product code</th><th>Product name</th><th style='width: 74px;'>SP</th><th style='width: 46px;'>Qty</th></tr>".$basketStr."</table>";
				$basketStr = trim(str_replace(array("\n", "\r", "\t"), '', $basketStr));
				echo json_encode(array("basket" => $basketStr));
			}else{
				echo json_encode(array('basket' => 'No Items in the basket'));
			}
	    }else{
			echo json_encode(array('basket' => 'No Items in the basket'));
		}
		die;
	}
	
	public function clearCartOndemand(){
		$this->request->Session()->delete('on_demand_basket_new_arrival');	
		echo json_encode(array('basket' => 'No Items in the basket'));die;
	}
	
	public function createOrder(){
		$session_basket = $this->request->Session()->read('on_demand_basket_new_arrival');
		if(is_array($session_basket) && !empty($session_basket)){
			$cat_res = $this->Products->find('list',array(
														  'keyField' => 'id',
														  'valueField' => 'category_id',
														  ))->toArray();
			$qantity_res = $this->Products->find('list',array(
															  'keyField' =>'id',
															  'valueField' => 'quantity',
															  ))->toArray();
			foreach($session_basket as $s_key => $s_value){
					$product_id = $s_value['id'];
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
													   );
				}
			
				$user_id = $this->request->session()->read('Auth.User.id');
				$kiosk_id = $this->request->Session()->read('kiosk_id');
				$order_data = array(
									'kiosk_id' => $kiosk_id,
									'user_id' => $user_id
								);
				
				$OnDemandOrdersEntity = $this->OnDemandOrders->newEntity($order_data,['validate' => false]);
				$OnDemandOrdersEntity = $this->OnDemandOrders->patchEntity($OnDemandOrdersEntity,$order_data,['validate' => false]);
				if($this->OnDemandOrders->save($OnDemandOrdersEntity,['validate' => false])){
					$id = $OnDemandOrdersEntity->id;
					foreach($on_demand_products_detail as $n_key => $n_value){
						$on_demand_products_details = array(
														'kiosk_id' => $kiosk_id,//1,
														'kiosk_placed_order_id' => $id,
														'product_id' => $n_value['product_id'],
														'category_id' => $n_value['category_id'],
														'quantity' => $n_value['quantity'],
														'org_qty' => $n_value['quantity'],
														);
						
						$OnDemandProductsEntity = $this->OnDemandProducts->newEntity($on_demand_products_details,['validate' => false]);
						$OnDemandProductsEntity = $this->OnDemandProducts->patchEntity($OnDemandProductsEntity,$on_demand_products_details,['validate' => false]);
						$counter1 = 0;
						if($this->OnDemandProducts->save($OnDemandProductsEntity)){
							$counter1 ++;
						}else{
							 $errors = array();
							  //debug($OnDemandProductsEntity->errors());die;
							if(!empty($OnDemandProductsEntity->errors())){
								 foreach($OnDemandProductsEntity->errors() as $key){
									  foreach($key as $value){
										 $errors[] = $value;  
									  }
								 }
								 echo json_encode(array('basket' => implode("</br>",$errors)));	die;
							}
						}
					}
					
					
				}else{
					echo json_encode(array('basket' => 'Some Error'));	die;
				}
				if($counter1 > 0){
					$this->request->Session()->delete('on_demand_basket_new_arrival');	
					echo json_encode(array('basket' => 'Order Created'));	
				}else{
					echo json_encode(array('basket' => 'Some Error'));	
				}
		}else{
			echo json_encode(array('basket' => 'No Items in the basket'));
		}die;
	}
	
	
	public function backstock(){
        $vat = $this->setting['vat'];
        $siteBaseUrl = Configure::read('SITE_BASE_URL');
		$CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
		$other_sites = Configure::read('sites');
		$path = dirname(__FILE__);
		$ext_site = 0;
        if(!empty($other_sites)){
            foreach($other_sites as $site_id => $site_name){
                  $isboloRam = strpos($path,$site_name);
                  if($isboloRam != false){
                      $ext_site = 1;
                  }
            }
        }
        
		if($ext_site == 1){
			$conn = ConnectionManager::get('hpwaheguru');
			$this->Settings->connection($conn);
			$this->Products->connection($conn);
		}
		
		
		$settingsArr = $this->Settings->find("list",['keyField' => 'attribute_name',
									  'valueField' => 'attribute_value',
									 ]
							  )->toArray();
		$days = $settingsArr['bk_stk_in_notification_days'];
		$products_query = $this->Products->find('all',array('conditions'=>array(
					'Products.qty_update_status' => 2,
					'Products.back_stock_status' => 1,
				    "CURDATE() <= DATE_ADD(Products.back_stock_time, INTERVAL +$days DAY)"
							 ),
		 'fields' => array('id','product','image','selling_price', 'product_code', 'category_id','created', 'modified', 'quantity','qty_update_time','back_stock_time'),
		 'recursive' => -1));
		$products_query = $products_query->hydrate(false);
		if(!empty($products_query)){
			$products = $products_query->toArray();
		}else{
			$products = array();
		}
		$productNofification = array();
		$product_ids = $categoryIds = array();
		//grabbing the category id of the newly entered products
		foreach($products as $key=>$product){
		    $categoryIds[$product['category_id']] = $product['category_id'];
			$product_ids[$product['id']] = $product['id'];
		}
		
		foreach($products as $key=>$product){
			$productNofification[$key]['id'] = $product['id'];
			$productNofification[$key]['image'] = $product['image'];
			$productNofification[$key]['Product'] = $product['product'];
			$productNofification[$key]['selling_price'] =  $product['selling_price'];
			$productNofification[$key]['product_code'] = $product['product_code'];
			$productNofification[$key]['category_id'] = $product['category_id'];
			$productNofification[$key]['created'] = $product['qty_update_time'];
			$productNofification[$key]['modified'] = $product['modified'];
			$productNofification[$key]['quantity'] = $product['quantity'];
			$productNofification[$key]['back_stock_time'] = $product['back_stock_time'];
		}
		
		$session_basket = $this->request->Session()->read('on_demand_basket_back_stock');
		
		if(is_array($session_basket) && !empty($session_basket)){
			$val = array_keys($session_basket);
			if(empty($val)){
				$val = array(0 => null);
			}
			$productCodeArr_query = $this->Products->find('list',
															[
																'keyField' => 'product_code',
																'valueField' => 'id',
																'conditions'=>['product_code IN'=>$val],
															]
												  );
			$productCodeArr_query = $productCodeArr_query->hydrate(false);
			if(!empty($productCodeArr_query)){
				$productCodeArr = $productCodeArr_query->toArray();
			}else{
				$productCodeArr = array();
			}
			
			
			$costSellingData_query = $this->Products->find('all',array('fields'=>array('product_code','cost_price','selling_price','product','Quantity'),'conditions'=>array('product_code IN'=>$val),'recursive'=>-1));
			$costSellingData_query = $costSellingData_query->hydrate(false);
			$costSellingData = $costSellingData_query->toArray();
			//pr($costSellingData);die;
			$costArr = array();
			$sellingArr = array();
			$productNameArr = array();
			if($costSellingData){
				foreach($costSellingData as $key=>$costSellingInfo){
				$costArr[$costSellingInfo['product_code']] = $costSellingInfo['cost_price'];
				$sellingArr[trim($costSellingInfo['product_code'])] = $costSellingInfo['selling_price'];
				$productNameArr[trim($costSellingInfo['product_code'])] = $costSellingInfo['product'];
				$productQuantityArr[$costSellingInfo['product_code']] = $costSellingInfo['Quantity'];
				}
			}
			$basketStr = '';
			$counter=0;
			foreach($session_basket as $productCode=>$productData){
				$counter++;
                $basketStr.="<tr>
							<td>$counter</td>
							<td>{$productCode}</td>
							<td>".$productNameArr[$productCode]."</td>
							<td>".$CURRENCY_TYPE.number_format($sellingArr[$productCode],2)."</td>
							<td>".$productData['quantity']."</td></tr>";
							//<td>".$productData['difference']."</td>
			}
			
			if(!empty($basketStr)){
				$basketStr = "<table><tr><th>Sr</br>No.</th><th style='width: 125px;'>Product code</th><th>Product name</th><th style='width: 74px;'>SP</th><th style='width: 46px;'>Qty</th></tr>".$basketStr."</table>";
				$basketStr = trim(str_replace(array("\n", "\r", "\t"), '', $basketStr));
				
			}else{
				$basketStr = "";
			}
	    }else{
			$basketStr = "";
		}
		
		$conn = ConnectionManager::get('default');
			$this->Settings->connection($conn);
			$this->Products->connection($conn);
			if(empty($product_ids)){
				$product_ids = array(0 => null);
			}
		$selling_price_arr = $this->Products->find('list',['conditions' => ['id IN' =>  $product_ids],
									  'keyField' => 'id',
									  'valueField' => 'selling_price',
									  ])->toArray();
		//pr($selling_price_arr);
		$this->set(compact('currency','productNofification','basketStr','session_basket','selling_price_arr', 'siteBaseUrl', 'vat'));
	}
	
	
	public function createOrderBackstock(){
		$session_basket = $this->request->Session()->read('on_demand_basket_back_stock');
		if(is_array($session_basket) && !empty($session_basket)){
			$cat_res = $this->Products->find('list',array(
														  'keyField' => 'id',
														  'valueField' => 'category_id',
														  ))->toArray();
			$qantity_res = $this->Products->find('list',array(
															  'keyField' =>'id',
															  'valueField' => 'quantity',
															  ))->toArray();
			foreach($session_basket as $s_key => $s_value){
					$product_id = $s_value['id'];
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
													   );
				}
			
				$user_id = $this->request->session()->read('Auth.User.id');
				$kiosk_id = $this->request->Session()->read('kiosk_id');
				$order_data = array(
									'kiosk_id' => $kiosk_id,
									'user_id' => $user_id
								);
				
				$OnDemandOrdersEntity = $this->OnDemandOrders->newEntity($order_data,['validate' => false]);
				$OnDemandOrdersEntity = $this->OnDemandOrders->patchEntity($OnDemandOrdersEntity,$order_data,['validate' => false]);
				if($this->OnDemandOrders->save($OnDemandOrdersEntity,['validate' => false])){
					$id = $OnDemandOrdersEntity->id;
					foreach($on_demand_products_detail as $n_key => $n_value){
						$on_demand_products_details = array(
														'kiosk_id' => $kiosk_id,//1,
														'kiosk_placed_order_id' => $id,
														'product_id' => $n_value['product_id'],
														'category_id' => $n_value['category_id'],
														'quantity' => $n_value['quantity'],
														'org_qty' => $n_value['quantity'],
														);
						
						$OnDemandProductsEntity = $this->OnDemandProducts->newEntity($on_demand_products_details,['validate' => false]);
						$OnDemandProductsEntity = $this->OnDemandProducts->patchEntity($OnDemandProductsEntity,$on_demand_products_details,['validate' => false]);
						$counter1 = 0;
						if($this->OnDemandProducts->save($OnDemandProductsEntity)){
							$counter1 ++;
						}else{
							 $errors = array();
							  //debug($OnDemandProductsEntity->errors());die;
							if(!empty($OnDemandProductsEntity->errors())){
								 foreach($OnDemandProductsEntity->errors() as $key){
									  foreach($key as $value){
										 $errors[] = $value;  
									  }
								 }
								 echo json_encode(array('basket' => implode("</br>",$errors)));	die;
							}
						}
					}
					
					
				}else{
					echo json_encode(array('basket' => 'Some Error'));	die;
				}
				if($counter1 > 0){
					$this->request->Session()->delete('on_demand_basket_back_stock');	
					echo json_encode(array('basket' => 'Order Created'));	
				}else{
					echo json_encode(array('basket' => 'Some Error'));	
				}
		}else{
			echo json_encode(array('basket' => 'No Items in the basket'));
		}die;
	}
	
	
	public function clearCartBackstock(){
		$this->request->Session()->delete('on_demand_basket_back_stock');	
		echo json_encode(array('basket' => 'No Items in the basket'));die;
	}
	
	public function unsetSessionAjaxBackstock(){
		$CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
		$product_code = $this->request->query['prod_code'];
		$req_qty = $this->request->query['qty'];
		$product_id = $this->request->query['product_id'];
		$product_price = $this->request->query['product_price'];
		$session_basket = $this->request->Session()->read("on_demand_basket_back_stock");
		if(!empty($session_basket)){
			if(array_key_exists($product_code,$session_basket)){
				unset($session_basket[$product_code]);
			}
		}
		
		$this->request->Session()->write('on_demand_basket_back_stock', $session_basket);
		$session_basket = $this->request->Session()->read('on_demand_basket_back_stock');
		
		if(is_array($session_basket) && !empty($session_basket)){
			$val = array_keys($session_basket);
			if(empty($val)){
				$val = array(0 => null);
			}
			$productCodeArr_query = $this->Products->find('list',
															[
																'keyField' => 'product_code',
																'valueField' => 'id',
																'conditions'=>['product_code IN'=>$val],
															]
												  );
			$productCodeArr_query = $productCodeArr_query->hydrate(false);
			if(!empty($productCodeArr_query)){
				$productCodeArr = $productCodeArr_query->toArray();
			}else{
				$productCodeArr = array();
			}
			
			
			$costSellingData_query = $this->Products->find('all',array('fields'=>array('product_code','cost_price','selling_price','product','Quantity'),'conditions'=>array('product_code IN'=>$val),'recursive'=>-1));
			$costSellingData_query = $costSellingData_query->hydrate(false);
			$costSellingData = $costSellingData_query->toArray();
			//pr($costSellingData);die;
			$costArr = array();
			$sellingArr = array();
			$productNameArr = array();
			if($costSellingData){
				foreach($costSellingData as $key=>$costSellingInfo){
				$costArr[$costSellingInfo['product_code']] = $costSellingInfo['cost_price'];
				$sellingArr[trim($costSellingInfo['product_code'])] = $costSellingInfo['selling_price'];
				$productNameArr[trim($costSellingInfo['product_code'])] = $costSellingInfo['product'];
				$productQuantityArr[$costSellingInfo['product_code']] = $costSellingInfo['Quantity'];
				}
			}
			$basketStr = '';
			$counter=0;
			foreach($session_basket as $productCode=>$productData){
				$counter++;
			$basketStr.="<tr>
							<td>$counter</td>
							<td>{$productCode}</td>
							<td>".$productNameArr[$productCode]."</td>
							<td>".$CURRENCY_TYPE.number_format($sellingArr[$productCode],2)."</td>
							<td>".$productData['quantity']."</td></tr>";
							//<td>".$productData['difference']."</td>
			}
			
			if(!empty($basketStr)){
				$basketStr = "<table><tr><th>Sr</br>No.</th><th style='width: 125px;'>Product code</th><th>Product name</th><th style='width: 74px;'>SP</th><th style='width: 46px;'>Qty</th></tr>".$basketStr."</table>";
				$basketStr = trim(str_replace(array("\n", "\r", "\t"), '', $basketStr));
				echo json_encode(array("basket" => $basketStr));
			}else{
				echo json_encode(array('basket' => 'No Items in the basket'));
			}
	    }else{
			echo json_encode(array('basket' => 'No Items in the basket'));
		}
		die;
	}
	
	public function updateSessionAjaxBackstock(){
		$CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');	
		$product_code = $this->request->query['prod_code'];
		$req_qty = $this->request->query['qty'];
		$product_id = $this->request->query['product_id'];
		$product_price = $this->request->query['product_price'];
		$kiosk_id = $this->request->query['kiosk_id'];
		
		$productArr[$product_code] = array(
										'quantity' => $req_qty,
										'selling_price' => $product_price,
										'id' => $product_id,
										);
		$session_basket = $this->request->Session()->read('on_demand_basket_back_stock');
		if(count($session_basket) >= 1){
			//adding item to the the existing session
			$sum_total = $this->add_arrays(array($productArr,$session_basket));
			$this->request->Session()->write('on_demand_basket_back_stock', $sum_total);
			$session_basket = $this->request->Session()->read('on_demand_basket_back_stock');				
		}else{
			//adding item first time to session
			//pr($productArr);die;
			if(count($productArr))$this->request->Session()->write('on_demand_basket_back_stock', $productArr);
		}
		
		$session_basket = $this->request->Session()->read('on_demand_basket_back_stock');
		if(is_array($session_basket) && !empty($session_basket)){
			$val = array_keys($session_basket);
			if(empty($val)){
				$val = array(0 => null);
			}
			$productCodeArr_query = $this->Products->find('list',
															[
																'keyField' => 'product_code',
																'valueField' => 'id',
																'conditions'=>['product_code IN'=>$val],
															]
												  );
			$productCodeArr_query = $productCodeArr_query->hydrate(false);
			if(!empty($productCodeArr_query)){
				$productCodeArr = $productCodeArr_query->toArray();
			}else{
				$productCodeArr = array();
			}
			
			
			$costSellingData_query = $this->Products->find('all',array('fields'=>array('product_code','cost_price','selling_price','product','Quantity'),'conditions'=>array('product_code IN'=>$val),'recursive'=>-1));
			$costSellingData_query = $costSellingData_query->hydrate(false);
			$costSellingData = $costSellingData_query->toArray();
			//pr($costSellingData);die;
			$costArr = array();
			$sellingArr = array();
			$productNameArr = array();
			if($costSellingData){
				foreach($costSellingData as $key=>$costSellingInfo){
				$costArr[$costSellingInfo['product_code']] = $costSellingInfo['cost_price'];
				$sellingArr[trim($costSellingInfo['product_code'])] = $costSellingInfo['selling_price'];
				$productNameArr[trim($costSellingInfo['product_code'])] = $costSellingInfo['product'];
				$productQuantityArr[$costSellingInfo['product_code']] = $costSellingInfo['Quantity'];
				}
			}
			$basketStr = '';
			$counter=0;
			
			foreach($session_basket as $productCode=>$productData){
				$counter++;
			$basketStr.="<tr>
							<td>$counter</td>
							<td>{$productCode}</td>
							<td>".$productNameArr[$productCode]."</td>
							<td>".$CURRENCY_TYPE.number_format($sellingArr[$productCode],2)."</td>
							<td>".$productData['quantity']."</td></tr>";
							//<td>".$productData['difference']."</td>
			}
			
			if(!empty($basketStr)){
				$basketStr = "<table><tr><th>Sr</br>No.</th><th style='width: 125px;'>Product code</th><th>Product name</th><th style='width: 74px;'>SP</th><th style='width: 46px;'>Qty</th></tr>".$basketStr."</table>";
				$basketStr = trim(str_replace(array("\n", "\r", "\t"), '', $basketStr));
				echo json_encode(array("basket" => $basketStr));
			}else{
				echo json_encode(array('basket' => 'No Items in the basket'));
			}
	    }else{
			echo json_encode(array('basket' => 'No Items in the basket'));
		}
		die;
		
	}
	
	
	public function editBulkPerforma($performa_id,$customerID){
		
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
		$InvoiceOrderDetailTable = TableRegistry::get($InvoiceOrderDetail_source,[
																		'table' => $InvoiceOrderDetail_source,
																	]);
		$invoce_order_data = $InvoiceOrderTable->find("all",['conditions' => [
														 'id' => $performa_id,
														 ]])->first();
		if(!empty($invoce_order_data)){
			$serilze_cart = $invoce_order_data->session_cart;
			$cart_data = unserialize($serilze_cart);
			
			$old_session_data = $_SESSION;
			if(array_key_exists("pf_quick_cart",$old_session_data)){
				$_SESSION["pf_quick_cart"] = $cart_data;
			}else{
				$_SESSION["pf_quick_cart"] = $cart_data;
			}
			$_SESSION["performa_id"] = $performa_id;
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
		$this->set(compact('agents'));
		
		//pr($this->request);
        $vat = isset($this->setting['vat']) && !empty($this->setting['vat']) ? $this->setting['vat'] : 0;
        $customer_query = $this->Customers->find('all', array(
														 'conditions' => array('id' => (int)$customerID),
														 'recursive' => -1,
														 )
										  );
        $customer_query = $customer_query->hydrate(false);
        if(!empty($customer_query)){
            $customer = $customer_query->First();
        }
		 
		if(count($customer) < 1){
			 $flashMessage = "Invalid customer id";
            $this->Flash->error($flashMessage);
			$this->redirect(array('controller' => 'customers','action' => "index"));
		}
		if(!empty($customer)){
			$this->request->Session()->write('pf_customer',$customer);
		}
		//-----------------------------------------
        $categories_query = $this->Categories->find('all',array(
								'fields' => array('id', 'category','id_name_path'),
                                                                'conditions' => array('Categories.status' => 1),
								'order' => 'Categories.category asc',
								'recursive' => -1
								));
        $categories_query->hydrate(false);
        $categories = $categories_query->toArray();
		$categories = $this->CustomOptions->category_options($categories,true);
		 
		$this->set(compact('categories','customer','vat'));
	}
	
	
	public function updatePerforma(){
		if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
			$kiosk_id = 0; // kiosk users are currently not considered
		}else{
			$kiosk_id = $this->request->Session()->read('kiosk_id');
		}
		
		if(!empty($kiosk_id)){
			$this->loadModel("InvoiceOrder");
			$this->loadModel("InvoiceOrderDetail");
			$InvoiceOrder_source = "kiosk_{$kiosk_id}_invoice_orders";
			$InvoiceOrderDetail_source = "kiosk_{$kiosk_id}_invoice_order_details";
		}else{
			$InvoiceOrder_source = "invoice_orders";
			$InvoiceOrderDetail_source = "invoice_order_details";
		}
		$InvoiceOrderTable = TableRegistry::get($InvoiceOrder_source,[
																		'table' => $InvoiceOrder_source,
																	]);
		$InvoiceOrderDetailTable = TableRegistry::get($InvoiceOrderDetail_source,[
																		'table' => $InvoiceOrderDetail_source,
																	]);
		
		if(array_key_exists("performa_id",$_SESSION)){
			$invoiceOrderId = $_SESSION['performa_id'];
		}else{
			$quick_cart_serilize = "";
			$this->Flash->error("no id found");
			return $this->redirect(array('controller'=>'customers','action'=>'index'));die;
		}
		if(array_key_exists("pf_quick_cart",$_SESSION)){
			$quick_cart = $this->request->Session()->read('pf_quick_cart');
			$quick_cart_serilize = serialize($quick_cart);
			$special_invoice = $quick_cart['special_invoice'];
			$bulk_dis = $quick_cart['new_sale_bulk_discount'];
			
		}else{
			$quick_cart_serilize = "";
			$this->Flash->error("no basket found");
			return $this->redirect(array('controller'=>'customers','action'=>'index'));die;
		}
		
		
		if(array_key_exists("pf_customer",$_SESSION)){
			$cust_info = $_SESSION['pf_customer'];
		}else{
			$this->Flash->error("no customer found");
			return $this->redirect(array('controller'=>'customers','action'=>'index'));die;
		}
		
		
		$basket = $quick_cart['new_sale_basket'];
		$finalAmount = $count = $total_amount = 0;
		$vat = $this->VAT;
		
		
		$old_Alldetail = $InvoiceOrderDetailTable->find("list",[
														"conditions" => [
															"invoice_order_id" => $invoiceOrderId,
														],
														'keyField' => "product_id",
														"valueField" => "invoice_order_id"
													  ])->toArray();
		
		if(!empty($old_Alldetail)){
			foreach($old_Alldetail as $k => $value){
				if(!array_key_exists($k,$basket)){
					
					$del_old_detail = $InvoiceOrderDetailTable->find("all",[
														"conditions" => [
															"invoice_order_id" => $value,
															"product_id" => $k,
														]
													  ])->first();
					
					if(!empty($del_old_detail)){
						$del_id = $del_old_detail->id;
						$invoiceId_Entity = $InvoiceOrderDetailTable->get($del_id);
						if($InvoiceOrderDetailTable->delete($invoiceId_Entity)){
							
						}else{
							
						}
					}
				}
			}
		}
		
		foreach($basket as $key => $value){
			$minimum_price = $value['minimum_selling_price'];
			$quantity = $withVatValue = $numerator = $denominator = $priceWithoutVat = $sold_price = $firstVal = $discountPercentage = 0;
			$product_id = $key;
			$quantity = $value['quantity'];
				$withVatValue = $value['net_amount'];
				$numerator = $withVatValue*100;
				$denominator = $vat+100;
				$priceWithoutVat = $numerator/$denominator;
				$priceWithoutVat = round($priceWithoutVat,2);
				
				$sold_price = $value['selling_price'];
				$firstVal = $priceWithoutVat - $sold_price;
				
				
				//echo "$firstVal/$priceWithoutVat*100";echo "</br>";
				$discountPercentage = $firstVal/$priceWithoutVat*100;
				
				if($discountPercentage > 0){
					$dis_status = 1;
				}else{
					//$discountPercentage = 0;
					$dis_status = 0;
				}
				if($sold_price > $priceWithoutVat){
					if($discountPercentage < 0){
						$selling_price = $sold_price; 
					}else{
						$selling_price = $sold_price + $sold_price*($vat/100);	
					}
				}else{
					//$withVatValue = $withVatValue+($withVatValue*$vat/100);
					$selling_price = $withVatValue;
				}
				//echo $selling_price;die;
				$orderDetailData = array(
							'kiosk_id' => $kiosk_id,
							'invoice_order_id' => $invoiceOrderId,
							'price' => $selling_price,
							'quantity' => $quantity,
							'product_id' => $product_id,
							'discount' => (float)$discountPercentage,
							'discount_status' => $dis_status,
						);
				
				//echo $sold_price; echo  "</br>";
				$finalAmount += $sold_price*$quantity;
				
				//pr($InvoiceOrderDetailTable);die;
				
				$old_detail = $InvoiceOrderDetailTable->find("all",[
														"conditions" => [
															"invoice_order_id" => $invoiceOrderId,
															"product_id" => $product_id,
														]
													  ])->first();
				
				if(!empty($old_detail)){
					$old_id = $old_detail->id;
					$InvoiceOrderDetailTable->behaviors()->load('Timestamp');
					$new_entity = $InvoiceOrderDetailTable->get($old_id);	
				}else{
					$InvoiceOrderDetailTable->behaviors()->load('Timestamp');
					$new_entity = $InvoiceOrderDetailTable->newEntity();
				}
				
				$patch_entity = $InvoiceOrderDetailTable->patchEntity($new_entity,$orderDetailData,['validate' => false]);
				if($InvoiceOrderDetailTable->save($patch_entity)){
					$count++;
				}else{
					pr($patch_entity->errors());die;
				}
				
		}
		
		if($bulk_dis > 0){
			$bulk_dis_value = $finalAmount*$bulk_dis/100;
			$finalAmount = $finalAmount - $bulk_dis_value;
		}
		
		if($cust_info['country'] != "OTH"){
			$finalAmount = $finalAmount + ($finalAmount*$vat/100);
		}
		
		if($count >0 ){
			$dataArr = array('amount'=>number_format($finalAmount,2),'bulk_discount'=>$bulk_dis,'session_cart' => $quick_cart_serilize);
			$getId = $InvoiceOrderTable->get($invoiceOrderId);
			$patch_E = $InvoiceOrderTable->patchEntity($getId,$dataArr);
				if($InvoiceOrderTable->save($patch_E)){
					//pr($dataArr);die;
				}else{
					//die;
				}
				$this->request->Session()->delete('pf_quick_cart');
				$this->request->Session()->delete('pf_customer');
				unset($_SESSION["pf_quick_cart"]);
				unset($_SESSION["pf_customer"]);
				unset($_SESSION['performa_id']);
				 $kiosk_id = $this->request->Session()->read('kiosk_id');
				 if($kiosk_id == ""){
					$kiosk_id = 10000;
				 }
				
                $this->SessionRestore->delete_from_session_backup_table('Home', 'pf_reorder_cart', 'any_key', $kiosk_id);
				$this->Flash->success("Performa updated");
				return $this->redirect(array('controller'=>'invoice-orders','action'=>'view',$invoiceOrderId)); //'controller'=>'customers','action'=>'index'
		}else{
			$this->Flash->error("Failed to generate Performa");
			return $this->redirect(array('controller'=>'customers','action'=>'index'));
		}
		
		
		
	}
	
}