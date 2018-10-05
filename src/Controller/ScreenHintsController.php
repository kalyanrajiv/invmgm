<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Event\Event;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Network\Request;
use Cake\ORM\TableRegistry;
use Cake\Mailer\Email;
use Cake\I18n;

class ScreenHintsController extends AppController
{
     public $helpers = [
         'Paginator' => ['templates' => 'paginatortemplates']
         ];
    public function initialize()
    {
        parent::initialize();
        $this->loadModel('ScreenHints');

    }
    
    public function index(){
		$this->ScreenHints->recursive = 0;
		$this->set('hints', $this->paginate());
		$this->set(compact('hints'));
    }
    
    public function view($id = null){
        if (!$this->ScreenHints->exists($id)) {
			throw new NotFoundException(__('Invalid setting'));
		}
		$options = array('conditions' => array('ScreenHints.id' => $id));
        $query = $this->ScreenHints->find('all', $options);
        $screenHints = $query->first();
        $screenHints = $screenHints->toArray();
		$this->set('ScreenHint',$screenHints);
    }
    
    public function add(){
		$controllersDropDown = array('brands'=>'brands','defective_kiosk_products'=>'defective_kiosk_products','import_order_details'=>'import_order_details','products'=>'products','warehouse_stocks'=>'warehouse_stocks','stock_transfer'=>'stock_transfer','stock'=>'stock',' 	stock_initializers'=>' 	stock_initializers','kiosk_orders'=>'kiosk_orders','kiosk_product_sales'=>'kiosk_product_sales','product_receipts'=>'product_receipts','invoice_orders'=>'invoice_orders','credit_product_details'=>'credit_product_details','user_attendances'=>'user_attendances','mobile_repairs'=>'mobile_repairs','mobile_unlocks'=>'mobile_unlocks','home'=>'home','mobile_purchases'=>'mobile_purchases');
		$this->set(compact('controllersDropDown'));
        $screenhints = $this->ScreenHints->newEntity();
        if ($this->request->is('post')) {
			$screenhints1 = $this->ScreenHints->patchEntity($screenhints,$this->request->data);
			//$this->request->data['Setting']['status'] = 1;
            //pr($screenhints1);die;
			if ($this->ScreenHints->save($screenhints1)) {
				$this->Flash->success(__('The setting has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Flash->error(__('The setting could not be saved. Please, try again.'));
			}
		}
    }
    
    public function edit($id = null){
        if (!$this->ScreenHints->exists($id)) {
			throw new NotFoundException(__('Invalid setting'));
		}
        $screenhint = $this->ScreenHints->get($id);
        if ($this->request->is(array('post', 'put'))) {
			//pr($this->request->data('ScreenHint'));die;
            $screenhint1 = $this->ScreenHints->patchEntity($screenhint,$this->request->data);
            if($this->ScreenHints->save($screenhint1)){
				$this->Flash->success(__('The setting has been saved.'));
				return $this->redirect(array('action' => 'index'));
			}else{
				$this->Flash->error(__('The setting could not be saved. Please, try again.'));
			}
        }else{
			$controllersDropDown = array('brands'=>'brands','defective_kiosk_products'=>'defective_kiosk_products','import_order_details'=>'import_order_details','products'=>'products','warehouse_stocks'=>'warehouse_stocks','stock_transfer'=>'stock_transfer','stock'=>'stock',' 	stock_initializers'=>' 	stock_initializers','kiosk_orders'=>'kiosk_orders','kiosk_product_sales'=>'kiosk_product_sales','product_receipts'=>'product_receipts','invoice_orders'=>'invoice_orders','credit_product_details'=>'credit_product_details','user_attendances'=>'user_attendances','mobile_repairs'=>'mobile_repairs','mobile_unlocks'=>'mobile_unlocks','home'=>'home','mobile_purchases'=>'mobile_purchases');
			//$this->set(compact('controllersDropDown'));
            $options = array('conditions' => array('ScreenHints.id' => $id));
            $query = $this->ScreenHints->find('all', $options);
            $result = $query->first();
			$this->request->data = $result = $result->toArray();
			//required for fck editor only; values should be assigned here $this->request->data
            $this->set(compact('controllersDropDown','result'));
		
        }
    }
}

?>