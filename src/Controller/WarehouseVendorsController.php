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

class WarehouseVendorsController extends AppController{
     public $helpers = [
         'Paginator' => ['templates' => 'paginatortemplates']
         ];
    public function initialize(){
        parent::initialize();
        $active = Configure::read('active');
        $country = Configure::read('country');
        $this->set(compact('active','country'));
        $this->loadModel('WarehouseVendors');
        //$this->loadModel('Products');
        //$this->loadModel('Categories');
        //$this->loadModel('TransferUnderstock');
        //$this->loadModel('StockTransfer');
    }
    public function index(){
            $this->WarehouseVendors->recursive = 0;
            $this->set('warehouseVendors', $this->paginate());
        }
    public function view($id = null){
        if (!$this->WarehouseVendors->exists($id)) {
			throw new NotFoundException(__('Invalid warehouse vendor'));
		}
        
        $options = $this->WarehouseVendors->find('all', array('conditions' => array('WarehouseVendors.id'  => $id)));
        $options_result = $options->first();
        if(!empty($options_result)){
            $options_result  = $options_result->toArray();
        }    
        $this->set('warehouseVendor', $options_result);
        
		//$options = array('conditions' => array('WarehouseVendors.id'  => $id));
		//$this->set('warehouseVendor', $this->WarehouseVendors->find('first', $options));
    }
    
    public function add(){
        $warehouseVendor = $this->WarehouseVendors->newEntity();
		if ($this->request->is('post')) {
			$warehouseVendor1 = $this->WarehouseVendors->patchEntity($warehouseVendor,$this->request->data);
			if ($this->WarehouseVendors->save($warehouseVendor1)) {
				$this->Flash->success(__('The warehouse vendor has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Flash->error(__('The warehouse vendor could not be saved. Please, try again.'));
			}
		}
	}
    
    public function edit($id = null) {
		if (!$this->WarehouseVendors->exists($id)) {
			throw new NotFoundException(__('Invalid warehouse vendor'));
		}
        $warehouseVendors = $this->WarehouseVendors->get($id);
		if ($this->request->is(array('post', 'put'))) {
            //$mobileconditions1 = $this->MobileConditions->patchEntity($mobileconditions, $this->request->data);
            $warehouseVendors1 = $this->WarehouseVendors->patchEntity($warehouseVendors, $this->request->data);
			if ($this->WarehouseVendors->save($warehouseVendors1)) {
                $this->Flash->success(__('The warehouse vendor has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
                $this->Flash->error(__('The warehouse vendor could not be saved. Please, try again.'));
			}
    	}else{
			$options = array('conditions' => array('WarehouseVendors.id' => $id));
            $query = $this->WarehouseVendors->find('all', $options);
            $result = $query->first();
            $result  = $result->toArray();
			$this->request->data = $result;
		}
	}
    
    public function delete($id = null) {
        $this->request->allowMethod(['post', 'delete']);
        $WarehouseVendor = $this->WarehouseVendors->get($id);
        if ($this->WarehouseVendors->delete($WarehouseVendor)) {
           $this->Flash->success(__('The warehouse vendor has been deleted.'));
        } else {
           $this->Flash->error(__('The warehouse vendor could not be deleted. Please, try again.'));
        }
		 
		return $this->redirect(array('action' => 'index'));
	}
}
?>