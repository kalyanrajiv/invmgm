<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link      http://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
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
//use Cake\Network\Email\Email;
/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @link http://book.cakephp.org/3.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller
{

    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * e.g. `$this->loadComponent('Security');`
     *
     * @return void
     */
	// public $components = [ 'Acl' => [ 'className' => 'Acl.Acl' ],'DebugKit.Toolbar' ];
  public $components = [ 'Acl' => [ 'className' => 'Acl.Acl' ]  ];
  public $helpers = ['CkEditor.Ck'];
    //  public $components = array('DebugKit.Toolbar');
	public static $AclActionsExclude = [
        'isAuthorized'
    ];
	
    public function initialize()
    {
        parent::initialize();
		
		//Time::setJsonEncodeFormat('yyyy-MM-dd HH:mm:ss');  // For any mutable DateTime
		//FrozenTime::setJsonEncodeFormat('yyyy-MM-dd HH:mm:ss');  // For any immutable DateTime
		//Date::setJsonEncodeFormat('yyyy-MM-dd HH:mm:ss');  // For any mutable Date
		//FrozenDate::setJsonEncodeFormat('yyyy-MM-dd HH:mm:ss');  // For any immutable Date

        $this->loadComponent('RequestHandler');
        $this->loadComponent('Flash');
       //  $this->loadComponent('Pusher');
        $this->loadComponent('Auth', [
           
            'authorize' => [
                'Acl.Actions' => ['actionPath' => 'controllers/']
            ],
            'loginAction' => [
                'plugin' => false,
                'controller' => 'Users',
                'action' => 'login'
            ],
            'loginRedirect' => [
                'plugin' => false,
                'controller' => 'Users',
                'action' => 'index'
            ],
            'logoutRedirect' => [
                'plugin' => false,
                'controller' => 'Users',
                'action' => 'login'
            ],
            'unauthorizedRedirect' => [
             
                'controller' => 'home',
                'action' => 'dashboard',
                'prefix' => false
            ],
            'authError' => 'You are not authorized to access that location.',
            'flash' => [
                'element' => 'error'
            ]
        ]);
        //$kiosk_id = $this->request->session()->write('kiosk_id',3);
 
//comment this line when acl update
		//Email::configTransport('hifiprofile', [
		//									'host' => 'mail.hifiprofile.net',
		//									'port' => 25,
		//									'username' => 'sales@hifiprofile.net',
		//									'password' => 'Proavid@2018',
		//									'className' => 'Mail',
		//									'ssl' => [
		//												'verify_peer' => false,
		//												'verify_peer_name' => false,
		//												   'allow_self_signed' => true
		//													   ]
		//								]);
		
		//Email::configTransport('gmail1', [
		//																						'host' => 'ssl://smtp.gmail.com',
		//									'port' => 465,
		//									'username' => 'uk.mobilebooth@gmail.com',
		//									'password' => 'proavid2017',
		//									'className' => 'Smtp',
		//									'ssl' => [
		//												'verify_peer' => false,
		//												'verify_peer_name' => false,
		//												   'allow_self_signed' => true
		//													   ]
		//								]);
		
        
        //comment this line when acl update
        
        
        
        defined('SITE_BASE_DOMAIN_NAME') or define('SITE_BASE_DOMAIN_NAME',Configure::read('SITE_BASE_DOMAIN_NAME'));
				defined('ADMIN_DOMAIN') or define('ADMIN_DOMAIN',Configure::read('ADMIN_DOMAIN')); 
        $path = dirname(__FILE__);
		$isboloRam = strpos($path, SITE_BASE_DOMAIN_NAME);
		if($isboloRam != false){
				  //mb code
			defined('TRANSPORT') or define('TRANSPORT',SITE_BASE_DOMAIN_NAME);
		}else{
				  //hp code
			defined('TRANSPORT') or define('TRANSPORT','default');
		}
        
        // defined('WEBSITE','worthguru.com') or define('WEBSITE','worthguru.com');
         defined('ADMINISTRATORS') or define('ADMINISTRATORS',1);
         defined('KIOSK_USERS') or define('KIOSK_USERS',3);
         defined('MANAGERS') or define('MANAGERS',2);
         defined('SALESMAN') or define('SALESMAN',5);
         defined('REPAIR_TECHNICIANS') or define('REPAIR_TECHNICIANS',7);
         defined('UNLOCK_TECHNICIANS') or define('UNLOCK_TECHNICIANS',8);
		 defined('FRANCHISE_OWNER') or define('FRANCHISE_OWNER',9);
         defined('KIOSK') or define('KIOSK',1);
         defined('SERVICE_CENTER') or define('SERVICE_CENTER',2);
         defined('UNLOCKING_CENTER') or define('UNLOCKING_CENTER',3);
         defined('inventory_manager') or define('inventory_manager',4);
        //options for both
         defined('BOOKED') or define('BOOKED',1);
        
        //options for repairs
         defined('REBOOKED') or define('REBOOKED',2);
         defined('DISPATCHED_TO_TECHNICIAN') or define('DISPATCHED_TO_TECHNICIAN',3);
         defined('RECEIVED_REPAIRED_FROM_TECHNICIAN') or define('RECEIVED_REPAIRED_FROM_TECHNICIAN',4);
         defined('RECEIVED_UNREPAIRED_FROM_TECHNICIAN') or define('RECEIVED_UNREPAIRED_FROM_TECHNICIAN',5);
         defined('DELIVERED_REPAIRED_BY_KIOSK') or define('DELIVERED_REPAIRED_BY_KIOSK',6);
         defined('DELIVERED_UNREPAIRED_BY_KIOSK') or define('DELIVERED_UNREPAIRED_BY_KIOSK',7);
         defined('DELIVERED_REPAIRED_BY_TECHNICIAN') or define('DELIVERED_REPAIRED_BY_TECHNICIAN',8);
         defined('DELIVERED_UNREPAIRED_BY_TECHNICIAN') or define('DELIVERED_UNREPAIRED_BY_TECHNICIAN',9);
         defined('RECEIVED_BY_TECHNICIAN') or define('RECEIVED_BY_TECHNICIAN',16);
         defined('REPAIR_UNDER_PROCESS') or define('REPAIR_UNDER_PROCESS',17);
         defined('DISPATCHED_2_KIOSK_REPAIRED') or define('DISPATCHED_2_KIOSK_REPAIRED',18);
         defined('DISPATCHED_2_KIOSK_UNREPAIRED') or define('DISPATCHED_2_KIOSK_UNREPAIRED',19);
         defined('WAITING_FOR_DISPATCH') or define('WAITING_FOR_DISPATCH',20);
    
        //optons for unlocks
         defined('VIRTUALLY_BOOKED') or define('VIRTUALLY_BOOKED',0);
         defined('UNLOCK_REQUEST_SENT') or define('UNLOCK_REQUEST_SENT',2);
         defined('DISPATCHED_2_CENTER') or define('DISPATCHED_2_CENTER',3);
         defined('UNLOCKED_CONFIRMATION_PASSED') or define('UNLOCKED_CONFIRMATION_PASSED',4);
         defined('UNLOCKING_FAILED_CONFIRMATION_PASSED') or define('UNLOCKING_FAILED_CONFIRMATION_PASSED',5);
         defined('RECEIVED_UNLOCKED_FROM_CENTER') or define('RECEIVED_UNLOCKED_FROM_CENTER',6);
         defined('RECEIVED_UNPROCESSED_FROM_CENTER') or define('RECEIVED_UNPROCESSED_FROM_CENTER',7);
         defined('REFUND_RAISED') or define('REFUND_RAISED',8);
         defined('DELIVERED_UNLOCKED_BY_CENTER') or define('DELIVERED_UNLOCKED_BY_CENTER',9);
         defined('DELIVERED_UNLOCKING_FAILED_AT_CENTER') or define('DELIVERED_UNLOCKING_FAILED_AT_CENTER',10);
         defined('DELIVERED_UNLOCKED_BY_KIOSK') or define('DELIVERED_UNLOCKED_BY_KIOSK',11);
         defined('DELIVERED_UNLOCKING_FAILED_AT_KIOSK') or define('DELIVERED_UNLOCKING_FAILED_AT_KIOSK',12);
         defined('REQUEST_RECEIVED_IN_PROCESS') or define('REQUEST_RECEIVED_IN_PROCESS',16);
         defined('PHONE_RECEIVED_BY_CENTER') or define('PHONE_RECEIVED_BY_CENTER',17);
         defined('UNLOCK_UNDER_PROCESS') or define('UNLOCK_UNDER_PROCESS',18);
         defined('WAITING_FOR_DISPATCH_UNLOCKED') or define('WAITING_FOR_DISPATCH_UNLOCKED',19);
         defined('UNLOCK_PROCESSED_CONFIRMATION_SENT_2_KIOSK') or define('UNLOCK_PROCESSED_CONFIRMATION_SENT_2_KIOSK',20);
         defined('DISPATCHED_2_KIOSK_UNLOCKED') or define('DISPATCHED_2_KIOSK_UNLOCKED',21);
         defined('DISPATCHED_2_KIOSK_UNPROCESSED') or define('DISPATCHED_2_KIOSK_UNPROCESSED',22);
         defined('UNLOCK_FAILED_CONFIRMATION_SENT_2_KIOSK') or define('UNLOCK_FAILED_CONFIRMATION_SENT_2_KIOSK',23);
         defined('QUOT_USER_PREFIX') or define('QUOT_USER_PREFIX',Configure::read('QUOT_USER_PREFIX'));
         defined('SPL_PRIVILEGE_USER') or define('SPL_PRIVILEGE_USER',Configure::read('SPL_PRIVILEGE_USER'));
				 defined('SPECIAL_USER') or define('SPECIAL_USER',Configure::read('SPECIAL_USER'));
          
        
        //number of rows for pagination
         defined('ROWS_PER_PAGE') or define('ROWS_PER_PAGE',50);
        /*
         * Enable the following components for recommended CakePHP security settings.
         * see http://book.cakephp.org/3.0/en/controllers/components/security.html
         */
        //$this->loadComponent('Security');
        $this->loadComponent('CustomOptions');
		// $this->Auth->allow();
    }

    /**
     * Before render callback.
     *
     * @param \Cake\Event\Event $event The beforeRender event.
     * @return \Cake\Network\Response|null|void
     */
    
     public function beforeFilter(Event $event){
    $this->loadModel('Settings');
    $this->loadModel('Products');
    $this->loadModel('Kiosks');
    $this->loadModel('ProductSellStats');
    $this->loadModel('ProductSaleStats');
    $this->loadModel('CommentMobileRepairs');
    $this->loadModel('MobileUnlockPrices');
    $this->loadmodel('MobileRepairPrices');
    $this->loadmodel('KioskOrders');
    $this->loadmodel('WarehouseStocks');
    $this->loadmodel('Messages');
    $this->loadmodel('Messages');
    $this->loadmodel('Users');
    $this->loadModel('CommentMobileUnlocks');
    $this->loadModel('WarehouseStock');
    $this->loadModel('CustomerProductPrice');
    
        parent::beforeFilter($event);
		//$this->Auth->allow();
        //$this->Auth->allow('add','logout','authorize');
        $siteBaseURL = Configure::read('SITE_BASE_URL');
		$this->set(compact('siteBaseURL'));
        $settingDataArr = array();
        $setting_query = $this->Settings->find('all');
       
        $setting_query->hydrate(false);
        $setting_data = $setting_query->toArray();
		
		
		$setting_query1 = $this->Settings->find('list',array(
															'keyField' => "attribute_name",
															'valueField' => "attribute_value", 
															));
       
        $setting_query1->hydrate(false);
        $setting_data1 = $setting_query1->toArray();
		
		if(!empty($setting_data1)){
			$header_value = $setting_data1['redirect_header'];
			if($header_value == 1){
				//header("Location: http://".ADMIN_DOMAIN);
				//die();
			}else{
				//header("Location: http://".ADMIN_DOMAIN);
				//die();
			}
		}
		
		
       //  pr($setting_data);die;
        foreach($setting_data as $key => $setting_info){
            $attributeName = $setting_info['attribute_name'];
            $attributeValue = $setting_info['attribute_value'];
            $settingDataArr[$attributeName] = $attributeValue;
        }
         
        if(array_key_exists('vat',$settingDataArr)){
            if((int)$settingDataArr['vat']){
            $this->VAT = $settingDataArr['vat'];
            }else{
				$this->VAT = 0;
			}
        }else{
            $this->VAT = 20;
        }
        //die;
        $this->setting = $settingDataArr;
        
        $parts = explode(".",$_SERVER['HTTP_HOST']);    //echo $_SERVER["REQUEST_URI"];
        $subDomain = "";
    //     pr($parts);
        if(count($parts) >= 2){
		  $subDomain = $parts[0];
            }   //pr($parts);echo "before".$subDomain;die;
			if(in_array('www',$parts)){
			  $subDomain = $parts[1];//die;
			}
			
        if($_SERVER['REMOTE_ADDR'] == '122.173.138.254'){
         // echo "<pre>";print_r($parts);
            //echo "</pre>";echo "before".$subDomain;
        }
     $sessionSubDomain = $this->request->session()->read('sessionSubDomain'); //die;
     $kiosk_id = $this->request->session()->read('kiosk_id');
        if($sessionSubDomain = $subDomain && !empty($subDomain)){
			 $this->loadModel('Kiosks');
			 //$users = $this->Kiosks->find('all');
			 //pr($users);die;
			  $subDomainArr_query = $this->Kiosks->find('all',array(
                                                              'conditions' => array('Kiosks.code' => $subDomain),
                                                             'fields' => array('name','id', 'kiosk_type'),
															//  'conditions' => array('code'=>"$subDomain"),
                                                             ) 
														);
			
             $subDomainArr_query = $subDomainArr_query->hydrate(false);
            if(!empty($subDomainArr_query)){
                $subDomainArr = $subDomainArr_query->toArray();
				 
            }else{
                $subDomainArr = array();
            }
			 //  pr($subDomainArr); die;
			  
            if(count($subDomainArr) >= 1){
                $kiosk_id = $subDomainArr['0']['id'];
                $kioskTitle = $subDomainArr['0']['name'];
                $kioskType = $subDomainArr['0']['kiosk_type'];
                $this->request->session()->write('kiosk_id',$kiosk_id);
				
                $this->request->session()->write('sessionSubDomain',$subDomain);
                $this->request->session()->write('kiosk_title',$kioskTitle);
                $this->request->session()->write('kiosk_type',$kioskType);
            }
           // echo "if";
        }else{
            echo "else";
         //    $kiosk_id = $this->request->session()->read('kiosk_id');
        }
        
        
         $this->request->session()->read('kiosk_id');
         $this->request->session()->read('kiosk_title');
         $this->Auth->loginAction = array(
          'controller' => 'users',
          'action' => 'login'
        );
        $this->Auth->logoutRedirect = array(
          'controller' => 'users',
          'action' => 'login'
        );
        //$this->Auth->loginRedirect = array(
        //  'controller' => '/posts',
        //  'action' => 'add'
        //);
        $this->Auth->loginRedirect = array(
          'controller' => '/home',
          'action' => 'dashboard'
        );
        $userID = $this->request->session()->read('Auth.User.id');
        if(!empty($userID)){
            $this->count_repair_notification(); 
        }
        $userID = $this->request->session()->read('Auth.User.id');
        if(!empty($userID)){
            $this->count_unlock_notification(); 
        }
        $userID = $this->request->session()->read('Auth.User.id');
        if(!empty($userID)){
            $this->count_new_order_notification(); 
        }
        $userID = $this->request->session()->read('Auth.User.id');
        if(!empty($userID)){
            $this->count_new_product_notification(); 
        }
        if(!empty($userID)){
            $this->count_product_pricechnge_notification(); 
        }
        if(!empty($userID)){
            $this->count_special_offer_notification(); 
        }
		if(!empty($userID)){
            $this->countNewArrival(); 
        }
		
		if(!empty($userID)){
            $this->countNewArrival();
			$this->countbackstock();
        }
		
         if(!empty($userID)){
            $this->uses_email();
             $this->count_comments();
        }
		 if(!empty($userID)){
            $this->managerLogin(); 
        }
        
    }
    public function beforeRender(Event $event)
    {
        if (!array_key_exists('_serialize', $this->viewVars) &&
            in_array($this->response->type(), ['application/json', 'application/xml'])
        ) {
            $this->set('_serialize', true);
        }
    }
    protected function insert_to_ProductSellStats($prduct_id = '',$data = array(),$kiosk_id = '',$operations = '+',$is_special=0,$bulk_invoice = 0){
         $conn = ConnectionManager::get('default');
        //pr($data);echo $prduct_id;echo $kiosk_id;die;
        //echo "hi";die;
        $loggedInUser = $this->request->session()->read('Auth.User.username');
        if (preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){//checking if the user has right for tempering
            $t_product_sell_statsTable = TableRegistry::get('product_sell_stats_new');
            $t_product_sale_statsTable = TableRegistry::get('product_sale_stats_new');
            //$this->ProductSellStat->setSource('t_product_sell_stats');
            //$this->ProductSaleStat->setSource('t_product_sale_stats');
        }else{
            $t_product_sell_statsTable = TableRegistry::get('product_sell_stats_new');
            $t_product_sale_statsTable = TableRegistry::get('product_sale_stats_new');
        }
       // echo "hi";die;
        // data array structure
        // data = array(
        //             'quantity' =>
        //             'product_code' =>
        //             'selling_price_withot_vat' =>
        //             'vat' => 
        //            );
        
        if(empty($kiosk_id)){ // for admin kiosk id will be 10000
            return false;
        }
        if(empty($prduct_id)){
            return false;
        }
        $vat = $quantity = $product_code = $cost_price = $selling_price = "";
        if(!empty($data)){
            if(!array_key_exists('quantity',$data)){
                return false;
            }else{
                $quantity = $data['quantity'];
            }
            if(!array_key_exists('product_code',$data)){
                return false;
            }else{
                $product_code = $data['product_code'];
            }
            if(!array_key_exists('selling_price_withot_vat',$data)){
                return false;
            }else{
                $selling_price = $data['selling_price_withot_vat'];
            }
            if(!array_key_exists('vat',$data)){
                return false;
            }else{
                $vat = $data['vat'];
            }
        }else{
            return false;
        }
        
        if(!empty($prduct_id) && !empty($kiosk_id)){
            if(!empty($quantity) && !empty($product_code) && !empty($selling_price)){
                //echo "$quantity, $product_code, $selling_price";die;
                $cat_query = $this->Products->find('list',array('conditions' => array('id' => $prduct_id),
                                                                'keyField' => 'id',
                                                    'valueField' => 'category_id',
                                                  //'fields' => array('id','category_id'),
                                                  )
                                     );
                $cat_id = $cat_query->toArray();
                
				$cost_price_query = $this->Products->find('list',array('conditions' => array('id' => $prduct_id),
                                                    'keyField' => 'id',
                                                    'valueField' => 'cost_price',
                                                  //'fields' => array('id','cost_price'),
                                                  )
                                     );
                $cost_price = $cost_price_query->toArray();
                
                $kiosk_name_query = $this->Kiosks->find('list',array('conditions' => array('id' => $kiosk_id),
                                                //'fields' => array('id','name')
                                                'keyField' => 'id',
                                                    'valueField' => 'name',
                                                )
                                   );
                $kiosk_name = $kiosk_name_query->toArray();
                
                
                if (preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
                    if($is_special==1){
                        $result_query = $t_product_sell_statsTable->find('all',array('conditions' => array('kiosk_id' => $kiosk_id,
                                                                                                  'product_id' => $prduct_id,
                                                                                        'DATE(created) = CURDATE()',
                                                                                         'status' => 1,
                                                                                        )));
                       
                       
                        if(!empty($result)){
                            $result = $result_query->first();
                        }
                       
                       
                        $saleRS_query = $t_product_sale_statsTable->find('all',array('conditions' => array(
                                                                                                   'product_id' => $prduct_id,
                                                                                                   'DATE(`created`) = CURDATE()',
                                                                                                   'status' => 1,
                                                                                                 )));
                       
                      
                       if(!empty($saleRS)){
                           $saleRS = $saleRS_query->first();
                       }
                    }else{
                         $result_query = $t_product_sell_statsTable->find('all',array('conditions' => array('kiosk_id' => $kiosk_id,
                                                                                                            'product_id' => $prduct_id,
                                                                                                  'DATE(created) = CURDATE()',
                                                                                                   'status' => 0,
                                                                                                  )));
                                 
                                 
                        if(!empty($result)){
                                  $result = $result_query->first();
                        }
                                 
                                 
                        $saleRS_query = $t_product_sale_statsTable->find('all',array('conditions' => array(
                                                                                                             'product_id' => $prduct_id,
                                                                                                             'DATE(`created`) = CURDATE()',
                                                                                                              'status' => 0,
                                                                                                           )));
                                 
                                
                        if(!empty($saleRS)){
                                     $saleRS = $saleRS_query->first();
                        }
                    }
                }else{
                            $result_query = $t_product_sell_statsTable->find('all',array('conditions' => array('kiosk_id' => $kiosk_id,
                                                                                                            'product_id' => $prduct_id,
                                                                                                  'DATE(created) = CURDATE()',
                                                                                                  'status' => 0,
                                                                                                  )));
                                 
                                 
                            if(!empty($result)){
                                  $result = $result_query->first();
                            }
                                 
                                 
                            $saleRS_query = $t_product_sale_statsTable->find('all',array('conditions' => array(
                                                                                                             'product_id' => $prduct_id,
                                                                                                             'DATE(`created`) = CURDATE()',
                                                                                                             'status' => 0,
                                                                                                           )));
                                 
                                
                            if(!empty($saleRS)){
                                     $saleRS = $saleRS_query->first();
                            }
                }
               
                
               // echo $cost_price[$prduct_id];die;
                $actual_cost_price = $cost_price[$prduct_id] * $quantity;
                $current_date = "";
                
                if(!empty($saleRS)){ // update
                    $id = $saleRS['id'];
                    if($operations == "+"){
                       if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser) && $is_special==1){
                            $update_query = "UPDATE `product_sale_stats_new` SET
																	`quantity` = `quantity` + $quantity,
																	`cost_price` = `cost_price` + $actual_cost_price,
																	`selling_price` = `selling_price` + $selling_price,
																	`status` = 1,
																	
																	WHERE `id` = $id AND `product_id` = $prduct_id";
                        }else{
                            $update_query = "UPDATE `product_sale_stats_new` SET
																	`quantity` = `quantity` + $quantity,
																	`cost_price` = `cost_price` + $actual_cost_price,
																	`selling_price` = `selling_price` + $selling_price,
																	`vat` = `vat` + $vat,
                                                                     `status` = 0,
																	 `bulk_invoice` = $bulk_invoice
																	WHERE `id` = $id AND `product_id` = $prduct_id";
                        }
					}elseif($operations == "-"){
                        if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser) && $is_special==1){
                            $update_query = "UPDATE `product_sale_stats_new` SET
																	`quantity` = `quantity` - $quantity,
																	`cost_price` = `cost_price` - $actual_cost_price,
																	`selling_price` = `selling_price` - $selling_price,
																	`status` = 1
																	WHERE `id` = $id AND `product_id` = $prduct_id";
                        }else{
                            $update_query = "UPDATE `product_sale_stats_new` SET
																	`quantity` = `quantity` - $quantity,
																	`cost_price` = `cost_price` - $actual_cost_price,
																	`selling_price` = `selling_price` - $selling_price,
																	`vat` = `vat` - $vat,
                                                                    `status` = 0,
																	`bulk_invoice` = $bulk_invoice
																	WHERE `id` = $id AND `product_id` = $prduct_id";
                        }
					}
                    $stmt = $conn->execute($update_query);
					//$this->ProductSaleStat->query($update_query);
                }else{ //Create New Record
                    $query = "SELECT NOW() as today_date";
                    $stmt = $conn->execute($query);
                    $date_result = $stmt ->fetchAll('assoc');
					if(!empty($date_result)){
						$current_date = $date_result[0]['today_date'];
					}
                    //Start: Insert
                    if($operations == "-"){
                        if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser) && $is_special==1){
                            $insert_query = "INSERT INTO `product_sale_stats_new` SET
                                                                        `product_id` = $prduct_id,
                                                                        `product_code` = '".str_replace("'","",$product_code)."',
                                                                        `cost_price` = -1*$actual_cost_price,
                                                                        `selling_price` = -1*$selling_price,
																		`quantity` = -1*$quantity,
                                                                        `category_id` = $cat_id[$prduct_id],
                                                                         `status` = 1,
                                                                        `created` = '$current_date'";
                        }else{
                            $insert_query = "INSERT INTO `product_sale_stats_new` SET
                                                                        `product_id` = $prduct_id,
                                                                        `product_code` = '".str_replace("'","",$product_code)."',
                                                                        `cost_price` = -1*$actual_cost_price,
                                                                        `selling_price` = -1*$selling_price,
																		`quantity` = -1*$quantity,
                                                                        `vat` = -1*$vat,
                                                                        `status` = 0,
                                                                        `category_id` = $cat_id[$prduct_id],
																		`bulk_invoice` = $bulk_invoice,
                                                                        `created` = '$current_date'";
                        }
					}elseif($operations == "+"){
                        if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser) && $is_special==1){
                            $insert_query = "INSERT INTO `product_sale_stats_new` SET
                                                                        `product_id` = $prduct_id,
                                                                        `product_code` = '".str_replace("'","",$product_code)."',
                                                                        `cost_price` = $actual_cost_price,
                                                                        `selling_price` = $selling_price,
																		`quantity` = $quantity,
                                                                        `status` = 1,
                                                                        `category_id` = $cat_id[$prduct_id],
                                                                        `created` = '$current_date'";
                        }else{
                            $insert_query = "INSERT INTO `product_sale_stats_new` SET
                                                                        `product_id` = $prduct_id,
                                                                        `product_code` = '".str_replace("'","",$product_code)."',
                                                                        `cost_price` = $actual_cost_price,
                                                                        `selling_price` = $selling_price,
																		`quantity` = $quantity,
                                                                        `vat` = $vat,
                                                                        `status` = 0,
                                                                        `category_id` = $cat_id[$prduct_id],
																		`bulk_invoice` = $bulk_invoice,
                                                                        `created` = '$current_date'";
                        }
					}
                    $stmt = $conn->execute($insert_query);
                    //$this->ProductSaleStat->query($insert_query);
                    //End: Insert
                }
                
                
                //$dbo = $this->ProductSaleStat->getDatasource();
                //$logData = $dbo->getLog();
                //$getLog = end($logData['log']);
                //echo "Log Query:".$getLog['query'];die;
                if(!empty($result)){ // update
                    $id = $result['id'];
					if($operations == "+"){
                        if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser) && $is_special==1){
                            $update_query = "UPDATE `product_sell_stats_new` SET
																	`quantity` = `quantity` + $quantity,
																	`cost_price` = `cost_price` + $actual_cost_price,
																	`selling_price` = `selling_price` + $selling_price,
																	`status` = 1
																	WHERE `id` = $id AND `product_id` = $prduct_id";					
                        }else{
                            $update_query = "UPDATE `product_sell_stats_new` SET
																	`quantity` = `quantity` + $quantity,
																	`cost_price` = `cost_price` + $actual_cost_price,
																	`selling_price` = `selling_price` + $selling_price,
																	`vat` = `vat` + $vat,
                                                                    `status` = 0,
																	`bulk_invoice` = $bulk_invoice,
																	WHERE `id` = $id AND `product_id` = $prduct_id";					
                        }
						
					}elseif($operations == "-"){
                        if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser) && $is_special==1){
                            $update_query = "UPDATE `product_sell_stats_new` SET
																	`quantity` = `quantity` - $quantity,
																	`cost_price` = `cost_price` - $actual_cost_price,
																	`selling_price` = `selling_price` - $selling_price,
																	`status` = 1
																	WHERE `id` = $id AND `product_id` = $prduct_id";					
                        }else{
                            $update_query = "UPDATE `product_sell_stats_new` SET
																	`quantity` = `quantity` - $quantity,
																	`cost_price` = `cost_price` - $actual_cost_price,
																	`selling_price` = `selling_price` - $selling_price,
																	`vat` = `vat` - $vat,
                                                                    `status` = 0,
																	`bulk_invoice` = $bulk_invoice,
																	WHERE `id` = $id AND `product_id` = $prduct_id";					
                        }
						
					}
                     $stmt = $conn->execute($update_query);
					//$this->ProductSellStat->query($update_query);
					return true;
                }else{ // insert
                    if(!isset($current_date) || empty($current_date)){
                        $query = "SELECT NOW() as today_date";
                        $stmt = $conn->execute($query);
                        $date_result = $stmt ->fetchAll('assoc');
                        $current_date = "";
                        if(!empty($date_result)){
                            $current_date = $date_result[0]['today_date'];
                        }
                    }
					if($operations == "-"){
						
                        if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser) && $is_special==1){
                            $insert_query = "INSERT INTO `product_sell_stats_new` SET
                                                                        `kiosk_id` = $kiosk_id,
                                                                        `kiosk_name` = '".str_replace("'","",$kiosk_name[$kiosk_id])."',
                                                                        `product_id` = $prduct_id,
                                                                        `product_code` = '".str_replace("'","",$product_code)."',
                                                                        `cost_price` = -1*$actual_cost_price,
                                                                        `selling_price` = -1*$selling_price,
																		`quantity` = -1*$quantity,
                                                                        
                                                                        `category_id` = $cat_id[$prduct_id],
                                                                        `status` = 1,
                                                                        `created` = '$current_date'
                                                                        
                                                                        ";	
                        }else{
                            $insert_query = "INSERT INTO `product_sell_stats_new` SET
                                                                        `kiosk_id` = $kiosk_id,
                                                                        `kiosk_name` = '".str_replace("'","",$kiosk_name[$kiosk_id])."',
                                                                        `product_id` = $prduct_id,
                                                                        `product_code` = '".str_replace("'","",$product_code)."',
                                                                        `cost_price` = -1*$actual_cost_price,
                                                                        `selling_price` = -1*$selling_price,
																		`quantity` = -1*$quantity,
                                                                        `vat` = -1*$vat,
                                                                        `category_id` = $cat_id[$prduct_id],
                                                                        `status` = 0,
                                                                        `created` = '$current_date',
																		`bulk_invoice` = $bulk_invoice,
                                                                        ";	
                        }
						 
					}elseif($operations == "+"){
						
                        if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser) && $is_special==1){
                            $insert_query = "INSERT INTO `product_sell_stats_new` SET
                                                                        `kiosk_id` = $kiosk_id,
                                                                        `kiosk_name` = '".str_replace("'","",$kiosk_name[$kiosk_id])."',
                                                                        `product_id` = $prduct_id,
                                                                        `product_code` = '".str_replace("'","",$product_code)."',
                                                                        `cost_price` = $actual_cost_price,
                                                                        `selling_price` = $selling_price,
																		`quantity` = $quantity,
                                                                        `status` = 1,
                                                                        `category_id` = $cat_id[$prduct_id],
                                                                        `created` = '$current_date'
                                                                        ";
                        }else{
                            $insert_query = "INSERT INTO `product_sell_stats_new` SET
                                                                        `kiosk_id` = $kiosk_id,
                                                                        `kiosk_name` = '".str_replace("'","",$kiosk_name[$kiosk_id])."',
                                                                        `product_id` = $prduct_id,
                                                                        `product_code` = '".str_replace("'","",$product_code)."',
                                                                        `cost_price` = $actual_cost_price,
                                                                        `selling_price` = $selling_price,
																		`quantity` = $quantity,
                                                                        `vat` = $vat,
                                                                        `status` = 0,
                                                                        `category_id` = $cat_id[$prduct_id],
                                                                        `created` = '$current_date',
																		`bulk_invoice` = $bulk_invoice
                                                                        ";
                        }
						
					}
                    $stmt = $conn->execute($insert_query);
                    //$this->ProductSellStat->query($insert_query);
                    return true;
                }
                
            }else{
				return false;
			}
        }else{
			return false;
		}
    }
     public function outputCsv($fileName, $assocDataArray){
        //echo "hi";pr($assocDataArray);die;
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment;filename=' . $fileName);    
        if(isset($assocDataArray) && !empty($assocDataArray)){
						$firstElment = reset($assocDataArray);
            $fp = fopen('php://output', 'w');
            fputcsv($fp, array_keys($firstElment));
            $count = 0;
            foreach($assocDataArray AS $values){
                $count ++;    
                fputcsv($fp, $values);
            }
            fclose($fp);die;
        }
    }
    public function pc_permute($items, $perms = array( )) {
        if (empty($items)) { 
            print join(' ', $perms) . "\n";
        }else{
            for ($i = count($items) - 1; $i >= 0; --$i) {
                $newitems = $items;
                $newperms = $perms;
                list($foo) = array_splice($newitems, $i, 1);
                array_unshift($newperms, $foo);
                $this->pc_permute($newitems, $newperms);
            }
        }
    }
    
    protected function increase_execution_time(){
        ini_set('max_execution_time', 300); //This is in seconds. Default is 30 seconds
		$memory_limit = ini_get('memory_limit'); //2G in current installation default is 16M
        $memory_limit = ini_get('max_execution_time'); //2G in current installation
        //Not allowing to increase above 5 mins.
    }
    public function count_repair_notification(){
        $count_Prices_query = $this->MobileRepairPrices->find('all',
                                                       ['conditions'=>['DATE(MobileRepairPrices.modified)>=DATE_ADD(CURDATE(), INTERVAL -3 DAY)']]);
        if(!empty($count_Prices_query)){
            $count_Prices = $count_Prices_query->count();
        }else{
            $count_Prices = '';
        }
        $this->set(compact('count_Prices'));
    }
    public function count_unlock_notification(){
         $count_unlock_Prices_query =  $this->MobileUnlockPrices->find('all',array(
                                                                                 'conditions'=>array('DATE(MobileUnlockPrices.modified)>=DATE_ADD(CURDATE(), INTERVAL -3 DAY)'),
                                                                                 'recursive'=>-1)
                                                                     );
        //$count_unlock_Prices = $count_unlock_Prices_query->count();
        if(!empty($count_unlock_Prices_query)){
           $count_unlock_Prices = $count_unlock_Prices_query->count();
        }else{
            $count_unlock_Prices = '';
        }
        $count_unlock_status_change_query =  $this->MobileUnlockPrices->find('all',array(
                                                                                         'conditions'=>array('DATE(MobileUnlockPrices.status_change_date)>=DATE_ADD(CURDATE(), INTERVAL -3 DAY)'),
                                                                                         'recursive'=>-1)
                                                                             );
         
           if(!empty($count_unlock_status_change_query)){
             $count_unlock_status_change = $count_unlock_status_change_query->count();
            }else{
                $count_unlock_status_change ='';
            }
        $this->set(compact('count_unlock_Prices','count_unlock_status_change'));
    }
     
     public function count_new_order_notification(){
         $kiosk_id = $this->request->session()->read('kiosk_id');
        //$count_new_Orders = $this->KioskOrder->find('count',array('conditions'=>array('KioskOrder.kiosk_id'=>$kiosk_id,'OR'=>array('DATE(KioskOrder.created)=CURDATE()','DATE(KioskOrder.created)=DATE_ADD(CURDATE(), INTERVAL -3 DAY)')),'recursive'=>-1,'fields'=>array('id','created')));
        $count_new_Orders_query = $this->KioskOrders->find('all',[
                                                            'conditions'=>[
                                                                           'KioskOrders.kiosk_id'=>$kiosk_id,
                                                                           'DATE(KioskOrders.created)>=DATE_ADD(CURDATE(),
                                                                           INTERVAL -3 DAY)'],
                                                             'keyField' => 'id',
                                                            'valueField' => 'created' 
                                                           ]);
         if(!empty($count_new_Orders_query)){
              $count_new_Orders = $count_new_Orders_query->count();
            }else{
                $count_new_Orders ='';
            }
        
        $this->set(compact('count_new_Orders'));
    }
    public function count_new_product_notification(){
        //$count_warehouseStock = $this->WarehouseStock->find('count',array('conditions'=>array('OR'=>array('DATE(WarehouseStock.created)=CURDATE()','DATE(WarehouseStock.created)=DATE_ADD(CURDATE(), INTERVAL -1 DAY)')),'recursive'=>-1,'fields'=>array('id','product_id')));
        $count_warehouseStock_query = $this->WarehouseStock->find('all',array(
                                                                'conditions' => array(
                                                                                    'OR' => array(
                                                                                'DATE(WarehouseStock.created)=CURDATE()',
                                                                                'DATE(WarehouseStock.created)=DATE_ADD(CURDATE(), INTERVAL -3 DAY)'
                                                                                                )
                                                                                    ),
                                                                
                                                                'group' => 'product_id'
                                                                ));
         if(!empty($count_warehouseStock_query)){
              $count_warehouseStock = $count_warehouseStock_query->count();
            }else{
                $count_warehouseStock ='';
            }
       
        $count_products_query = $this->Products->find('all',array('conditions'=>array('DATE(Products.created) >= DATE_ADD(CURDATE(), INTERVAL -3 DAY)'),'fields' => array('id','product','image','selling_price', 'product_code'),'recursive' => -1));
         if(!empty($count_products_query)){
              $count_products = $count_products_query->count();
            }else{
                $count_products ='';
            }
        
        $total = $count_products;//$count_warehouseStock + removed warehouse as of now, since we are only showing the new products addition
        // pr( $total);
        $this->set(compact('total'));
    }
    public function count_product_pricechnge_notification(){
        $count_prdct_pr_change_query = $this->Products->find('all',array('conditions'=>array(
						    'DATE(Products.lu_sp) >= DATE_ADD(CURDATE(), INTERVAL -3 DAY)'),
						    'recursive' => -1)
						);
         if(!empty($count_prdct_pr_change_query)){
             $count_prdct_pr_change = $count_prdct_pr_change_query->count();
            }else{
                $count_prdct_pr_change ='';
            }
        
         
        $this->set(compact('count_prdct_pr_change'));
    }
    
    public function count_special_offer_notification(){
        $count_offer_notice_query = $this->Products->find('all',array('conditions'=>array(
						    'special_offer' => 1),
						    'recursive' => -1)
						);
        if(!empty($count_offer_notice_query)){
              $count_offer_notice = $count_offer_notice_query->count();
            }else{
                $count_offer_notice ='';
            }
       
        $this->set(compact('count_offer_notice'));
    }
     public function uses_email(){
        $loggedInUser = $this->request->session()->read('Auth.User.id');
       $warehouseKioskId = Configure::read('WAREHOUSE_KIOSK_ID'); 
        $kiosk_id = $this->request->session()->read('kiosk_id');
        if(empty($kiosk_id)){
            $kiosk_id = $warehouseKioskId;
        }
//        $kioskEmailCount = $this->Message->find('count', array(
//				'conditions' => array('receiver_id' => $kiosk_id,
//                                                      'sent_to_id' => '',
//						      array('OR'=>array(array('Message.receiver_read'=>1),
//								  array('Message.receiver_read'=>0))),
//						      array('OR'=>array(array('Message.receiver_status'=>0),
//								  array('Message.receiver_status'=>1)))
//						      )
//						)
//				); changed on 19.12.2015
        $kioskEmailCount_query = $this->Messages->find('all', array(
				'conditions' => array('OR' => array(
								    array(
								      'AND' => array('Messages.receiver_id' => $kiosk_id),
									  array('Messages.sent_to_id IS NULL')
								      ),
								    array(
								      'AND' => array('Messages.sent_to_id' => $loggedInUser),
									  array('Messages.receiver_id IS NULL'),
								    )
								  ),
						      array('OR'=>array(array('Messages.receiver_read'=>1),
								  array('Messages.receiver_read'=>0))),
						      array('OR'=>array(array('Messages.receiver_status'=>0),
								  array('Messages.receiver_status'=>1)))
						      )
						)
				);
         $kioskEmailCount = $kioskEmailCount_query->count();
          //if(!empty($kioskEmailCount_query)){
          //    $kioskEmailCount = $kioskEmailCount_query->count();
          //  }else{
          //      $kioskEmailCount ='';
          //  }
 
        $newEmailCount = $kioskEmailCount;
        // + $userEmailCount
        $this->set('newEmailCount',$newEmailCount);
    }
    
     public function count_comments(){
       
      $kiosk_users_query = $this->Users->find('list',[
                                                    'keyField' => 'id',
                                                     'valueField' => 'username',
                                                    'conditions' => ['Users.group_id IN' => [KIOSK_USERS, ADMINISTRATORS, MANAGERS]]
                                                 ]
                                        ); 
         if(!empty($kiosk_users_query)){
             $kiosk_users = $kiosk_users_query->toArray();
        }
       
        //for service center dashboard
        $comments4ServCentCount_query = $this->CommentMobileRepairs->find('all',array(
						'conditions' => array(
						    'CommentMobileRepairs.user_id IN' => array_keys($kiosk_users),
						    array('DATE(CommentMobileRepairs.created) >= DATE_ADD(CURDATE(),INTERVAL -20 DAY)')
						    ),
						'recursive' => -1
						)
					 );
         
        if(!empty($comments4ServCentCount_query)){
              $comments4ServCentCount = $comments4ServCentCount_query->count();
            }else{
                $comments4ServCentCount ='';
            } 
        //for unlock center dashboard
       
        $comments4UnlockCentCount_query = $this->CommentMobileUnlocks->find('all',array(
						'conditions' => array(
						    'CommentMobileUnlocks.user_id IN' => array_keys($kiosk_users),
						    array('DATE(CommentMobileUnlocks.created) >= DATE_ADD(CURDATE(),INTERVAL -20 DAY)')
						    ),
						'recursive' => -1
						)
					 );
         if(!empty($comments4UnlockCentCount_query)){
              $comments4UnlockCentCount = $comments4UnlockCentCount_query->count();
            }else{
                $comments4UnlockCentCount ='';
            } 
        //for kiosk and admins
        $kiosk_id = $this->request->session()->read('kiosk_id');
        if(
           $this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS ||
            $this->request->session()->read('Auth.User.group_id') == MANAGERS
        ){
             
             $unlockusers_query = $this->Users->find('list',[
                                                    'keyField' => 'id',
                                                     'valueField' => 'username',
                                                    'conditions' => ['Users.group_id IN' => [UNLOCK_TECHNICIANS, ADMINISTRATORS, MANAGERS, KIOSK_USERS]]
                                                 ]
                                        ); 
         
            
        }else{
             $unlockusers_query = $this->Users->find('list',[
                                                    'keyField' => 'id',
                                                     'valueField' => 'username',
                                                    'conditions' => ['Users.group_id IN' => [UNLOCK_TECHNICIANS, ADMINISTRATORS, MANAGERS]]
                                                 ]
                                        ); 
             
        }
        if(!empty($unlockusers_query)){
             $unlockusers = $unlockusers_query->toArray();
        }
        
        if(
            $this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS||
            $this->request->session()->read('Auth.User.group_id') == MANAGERS
        ){
           
            $repairusers_query = $this->Users->find('list',[
                                                    'keyField' => 'id',
                                                     'valueField' => 'username',
                                                    'conditions' => ['Users.group_id IN' => [REPAIR_TECHNICIANS, ADMINISTRATORS, MANAGERS, KIOSK_USERS]]
                                                 ]
                                        ); 
           
        }else{
            $repairusers_query = $this->Users->find('list',[
                                                    'keyField' => 'id',
                                                     'valueField' => 'username',
                                                    'conditions' => ['Users.group_id IN' => [REPAIR_TECHNICIANS, ADMINISTRATORS, MANAGERS]]
                                                 ]
                                        ); 
           
        }
        if(!empty($repairusers_query)){
             $repairusers = $repairusers_query->toArray();
        }
        if($kiosk_id){
            $commentsUnlock4KioskCount_query = $this->CommentMobileUnlocks->find('all',array(
                                            'conditions' => array(
                                                'CommentMobileUnlocks.user_id IN' => array_keys($unlockusers),
                                                'MobileUnlocks.kiosk_id' => $kiosk_id,
                                                array('DATE(CommentMobileUnlocks.created) >= DATE_ADD(CURDATE(),INTERVAL -20 DAY)')
                                                ),
                                            'contain' => array('MobileUnlocks')
                                            )
                                         );
            
             if(!empty($commentsUnlock4KioskCount_query)){
              $commentsUnlock4KioskCount = $commentsUnlock4KioskCount_query->count();
            }else{
                $commentsUnlock4KioskCount ='';
            } 
            
            $commentsRepair4KioskCount_query = $this->CommentMobileRepairs->find('all',array(
                                            'conditions' => array(
                                                'CommentMobileRepairs.user_id IN' => array_keys($repairusers),
                                                //'MobileRepairs.kiosk_id' => $kiosk_id,
                                                array('DATE(CommentMobileRepairs.created) >= DATE_ADD(CURDATE(),INTERVAL -20 DAY)')
                                                ),
                                           // 'contain' => array('MobileRepairs')
                                            )
                                         );
             if(!empty($commentsRepair4KioskCount_query)){
              $commentsRepair4KioskCount = $commentsRepair4KioskCount_query->count();
            }else{
                $commentsRepair4KioskCount ='';
            } 
        }else{
            $commentsUnlock4KioskCount_query = $this->CommentMobileUnlocks->find('all',array(
                                            'conditions' => array(
                                                'CommentMobileUnlocks.user_id IN' => array_keys($unlockusers),
                                                array('DATE(CommentMobileUnlocks.created) >= DATE_ADD(CURDATE(),INTERVAL -20 DAY)')
                                                )
                                            )
                                         );
            if(!empty($commentsUnlock4KioskCount_query)){
              $commentsUnlock4KioskCount = $commentsUnlock4KioskCount_query->count();
            }else{
                $commentsUnlock4KioskCount ='';
            } 
            
            $commentsRepair4KioskCount_query = $this->CommentMobileRepairs->find('all',array(
                                            'conditions' => array(
                                                'CommentMobileRepairs.user_id IN' => array_keys($repairusers),
                                                array('DATE(CommentMobileRepairs.created) >= DATE_ADD(CURDATE(),INTERVAL -20 DAY)')
                                                )
                                            )
                                         );
             if(!empty($commentsRepair4KioskCount_query)){
              $commentsRepair4KioskCount = $commentsRepair4KioskCount_query->count();
            }else{
                $commentsRepair4KioskCount ='';
            } 
        }
        
        $this->set('comments4ServCentCount',$comments4ServCentCount);
        $this->set('comments4UnlockCentCount',$comments4UnlockCentCount);
        $this->set('commentsRepair4KioskCount',$commentsRepair4KioskCount);
        $this->set('commentsUnlock4KioskCount',$commentsUnlock4KioskCount);
    }
    public function check_if_kiosk(){
        $kiosk_id = $this->request->Session()->read('kiosk_id');
        if(empty($kiosk_id)){
            $this->Flash->error(__('Please login from your kiosk website for adding mobile repair'));
            return $this->redirect(array('controller' => 'home', 'action' => 'dashboard'));
        }
    }
     protected function convertToHoursMins($time, $format = '%02d:%02d') {
        if ($time < 1) {
            return;
        }
        $hours = floor($time / 60);
        $minutes = ($time % 60);
        return sprintf($format, $hours, $minutes);
    }
    public function get_kiosk_for_credit($setting_name = ""){
			$user_id = $this->request->Session()->read('Auth.User.id');
			$this->loadModel('UserSettings');
            
			$res_query = $this->UserSettings->find('all',array('conditions' => array(
																		 'user_id' => $user_id,
																		 'setting_name' => $setting_name,
																		 //'user_session_key' => $session_id,
																		 )));
            $res_query = $res_query->hydrate(false);
            if(!empty($res_query)){
                 $res = $res_query->first();
            }else{
                $res = array();
            }
          // pr($res);die;
			$kiosk_id = "";
            if(empty($kiosk_id)){
                $kiosk_id = 10000;
            }
			if(!empty($res)){
				$json_data = $res['data'];
				$decoded_res = json_decode($json_data);
				$kiosk_id = $decoded_res->kiosk_id;
			}
			return $kiosk_id;
	}
    
    public function get_kiosk_for_invoice(){
			//$session_id = $this->Session->id();
			$user_id = $this->request->Session()->read('Auth.User.id');
			//$data_arr = array('kiosk_id' => $kiosk_id);
			//$jsondata = json_encode($data_arr);
			$this->loadModel('UserSettings');
			
			$res_query = $this->UserSettings->find('all',array('conditions' => array(
																		 'user_id' => $user_id,
																		 'setting_name' => "search",
																		 //'user_session_key' => $session_id,
																		 )));
            $res_query = $res_query->hydrate(false);
            if(!empty($res_query)){
                $res = $res_query->first();
            }else{
                $res = array();
            }
			$kiosk_id = "";
			if(!empty($res)){
				$json_data = $res['data'];
				$decoded_res = json_decode($json_data);
				$kiosk_id = $decoded_res->kiosk_id;
			}
			return $kiosk_id;
	}
    
    public function get_kiosk_for_dr_invoice(){
			//$session_id = $this->Session->id();
			$user_id = $this->request->Session()->read('Auth.User.id');
			//$data_arr = array('kiosk_id' => $kiosk_id);
			//$jsondata = json_encode($data_arr);
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
			$kiosk_id = "";
			if(!empty($res)){
				$json_data = $res['data'];
				$decoded_res = json_decode($json_data);
				$kiosk_id = $decoded_res->kiosk_id;
			}
			return $kiosk_id;
	}
	 public function isAuthorized($user) {
        // return true;
        
        // Admin can access every action
        if (isset($user['role_id']) && $user['role_id'] === 1) {
            return true;
        }

        // Default deny
        return false;
    }
	public function managerLogin(){
		
		$this->loadModel('Kiosks');
        $userID = $this->request->session()->read('Auth.User.id');
		
		$loginkiosk_query = $this->Users->find('all',array(
																 'fields' => array('id', 'kiosk_assigned'),
																 'conditions' => array('Users.id'=>$userID),
																)
												   );
		$loginkiosk = $loginkiosk_query->hydrate(false);
		if(!empty($loginkiosk_query)){
		 $loginkiosk = $loginkiosk_query->first();   
		}else{
			$loginkiosk = array();
		}
		$kiosk_assigned = array();
		
		if(!empty($loginkiosk)){
			 if($loginkiosk['kiosk_assigned'] == -1){
				   $kiosks_query = $this->Kiosks->find('list',[
									'keyField' => 'id',
									'valueField' => 'name',
								 ]);
				if(!empty($kiosks_query)){
					$kiosks = $kiosks_query->toArray();
				}else{
					$kiosks = array();
				}
				$kioskIDs = array_keys($kiosks);//die;
				$loginkiosk['kiosk_assigned'] = implode('|', $kioskIDs);	 
			 }
			 if(is_array(explode('|',$loginkiosk['kiosk_assigned']))){
				   $kioskids = explode('|',$loginkiosk['kiosk_assigned']);
			}
		}
		$manager_kiosks = array();
		if( $this->request->session()->read('Auth.User.group_id') == MANAGERS){
                $manager_kiosks_query = $this->Kiosks->find('list',[
																'conditions' =>['Kiosks.id IN' =>$kioskids],
																'keyField' => 'id',
																'valueField' => 'name',
																'order' => 'Kiosks.name asc'
															]
																 
												  );
				$manager_kiosks_query = $manager_kiosks_query->hydrate(false);
				if(!empty($manager_kiosks_query)){
					$manager_kiosks = $manager_kiosks_query->toArray();
				}else{
					$manager_kiosks = array();
				}
				 $this->set('manager_kiosks',$manager_kiosks);
		}
	 	return $manager_kiosks;
	}
	
	public function allowed_user(){
		$user_id = $this->request->session()->read('Auth.User.id');
		$all_ids = $this->getChildren($user_id);
		if(empty($all_ids)){
			$all_ids = array(0=>null)	;
		}
		return $all_ids;
	}
	
	
	function getChildren($parent_id) {
		  $tree = Array();
		  if (!empty($parent_id)) {
			  $tree = $this->getOneLevel($parent_id);
			  foreach ($tree as $key => $val) {
				  $ids = $this->getChildren($val);
					$tree = array_merge($tree, $ids);
			  }
		  }
		  return $tree;
	 }
	 
	 function getOneLevel($catId){
		  $cat_id = array();
		  $res = $this->Users->find('all',array('conditions' => array('parent_id' => $catId)))->toArray();
		  if(!empty($res)){
			   foreach($res as $key => $value){
					$cat_id[] = $value->id;	
			   }
		  }
		  return $cat_id;
	  }
	  
	  public function get_kiosk(){
		$user_id = $this->request->session()->read('Auth.User.id');
		$group_id = $this->request->session()->read('Auth.User.group_id');
		 $external_site_arry = Configure::read('external_sites');
		$path = dirname(__FILE__);
		$ext_site = 0;
		foreach($external_site_arry as $k=>$v){
			  $isboloRam = strpos($path,$v);
			  if($isboloRam){
				  $ext_site = 1;
			  }
		}
		if($ext_site == 1){
			if($group_id == 1){
				$kiosk_ids = $this->Kiosks->find('list',['keyField' => 'id',
											'valueField' => 'id',
											])->toArray();
				return $kiosk_ids;
			}else{
				if(!empty($user_id)){
					$res = $this->Users->find("all",array('conditions' => array(
																		'id' => $user_id,
																		)))->toArray();
				   if(!empty($res)){
					   $kiosk_assigned_data = $res[0]->kiosk_assigned;
					   if(!empty($kiosk_assigned_data)){
						   $kiosk_ids = explode("|",$kiosk_assigned_data);
					   }else{
						   $kiosk_ids = array();
					   }
				   }else{
					   $kiosk_ids = array();
				   }
				   return $kiosk_ids;	
				}else{
					$kiosk_ids= array();	
					return $kiosk_ids;	
				}	
			}	
		}else{
			$kiosk_ids = $this->Kiosks->find('list',['keyField' => 'id',
											'valueField' => 'id',
											])->toArray();
				return $kiosk_ids;
		}
	  }
	  
	  public function AddEditCustomerProductPrice($customer_id,$product_id,$sale_price){
			$get_entry = $this->CustomerProductPrice->find("all",['conditions' => ['customer_id' => $customer_id,
																						   'product_id' => $product_id,
																						   ]])->toArray();
			if(!empty($get_entry)){
				$cust_product_id = $get_entry[0]->id;
				$CustomerProductPriceEntity = $this->CustomerProductPrice->get($cust_product_id);
				$data_to_save = array(
					'sale_price' => $sale_price,
				);
				$CustomerProductPriceEntity = $this->CustomerProductPrice->patchEntity($CustomerProductPriceEntity, $data_to_save);
				if($this->CustomerProductPrice->save($CustomerProductPriceEntity)){
					
				}else{
					pr($CustomerProductPriceEntity);
					pr($CustomerProductPriceEntity->errors());die;
				}	
			}else{
				$CustomerProductPriceData = array(
											  'customer_id' => $customer_id,
											  'product_id' => $product_id,
											  'sale_price' => $sale_price,
											  );
			
				$CustomerProductPriceEntity = $this->CustomerProductPrice->newEntity();
				$CustomerProductPriceEntity = $this->CustomerProductPrice->patchEntity($CustomerProductPriceEntity, $CustomerProductPriceData);
				if($this->CustomerProductPrice->save($CustomerProductPriceEntity)){
					
				}else{
					pr($CustomerProductPriceEntity);
					pr($CustomerProductPriceEntity->errors());die;
				}	
			}
			return true;
	  }
	  
	  public function countNewArrival(){
		$other_sites = Configure::read('sites');
		$path = dirname(__FILE__);
		$ext_site = 0;
    if(isset($other_sites) && !empty($other_sites)){
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
		if(empty(trim($days))){
			$days = 0;
		}
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
		   $new_arrival_count = 0;
		  $new_arrival_count = count($products);
		  	$conn = ConnectionManager::get('default');
			$this->Settings->connection($conn);
			$this->Products->connection($conn);
		 $this->set(compact('new_arrival_count'));  
	  }
	  
	   public function countbackstock(){
		$CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
		$other_sites = Configure::read('sites');
		$path = dirname(__FILE__);
		$ext_site = 0;
    if(isset($other_sites) && !empty($other_sites)){
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
		   $back_stock_count = 0;
		  $back_stock_count = count($products);
		  	$conn = ConnectionManager::get('default');
			$this->Settings->connection($conn);
			$this->Products->connection($conn);
		 $this->set(compact('back_stock_count'));  
	  }
    
		public function html_to_pdf($file,$html){
		// Set parameters
			$apikey = '0ecc015e-a33a-4f29-a4bf-12ed0f0848ae';
			$value = $html; // can aso be a url, starting with http..
														
			$postdata = http_build_query(
				array(
					'apikey' => $apikey,
					'value' => $value,
					'MarginBottom' => '0',
					'MarginTop' => '0',
					//'FooterSpacing' => '0',
					//'HeaderSpacing' => '0',
					//'PageHeight' => '397mm',
					//'PageWidth' => '310mm',
					//'PageSize' => 'A0',
					//'UsePrintStylesheet'=>true,
				)
			);
			 
			$opts = array('http' =>
				array(
					'method'  => 'POST',
					'header'  => 'Content-type: application/x-www-form-urlencoded',
					'content' => $postdata
				)
			);
			 
			$context  = stream_context_create($opts);
			 
			// Convert the HTML string to a PDF using those parameters
			$result = file_get_contents('http://api.html2pdfrocket.com/pdf', false, $context);
			
			// Save to root folder in website
			file_put_contents($file, $result);
	  }
}
?>
