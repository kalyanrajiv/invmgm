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

use Cake\Datasource\ConnectionManager;
class ReorderLevelsController extends AppController
{
    public $helpers = [
         'Paginator' => ['templates' => 'paginatortemplates']
         ];
    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('CustomOptions');
        $this->loadModel('ReorderLevels');
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
    }
    
    public function index() {
		$this->ReorderLevels->recursive = 0;
		$this->paginate = array('contain' => array('Kiosks','Products'));
		$this->set('reorderLevels', $this->paginate('ReorderLevels'));
	}
    
    public function view($id = null) {
		if (!$this->ReorderLevels->exists($id)) {
			throw new NotFoundException(__('Invalid reorder level'));
		}
		$options = array('conditions' => array('ReorderLevels.id' => $id),'contain' => array('Kiosks','Products'));
        $reorderLevel_query = $this->ReorderLevels->find('all', $options);
        $reorderLevel_query = $reorderLevel_query->hydrate(false);
        if(!empty($reorderLevel_query)){
            $reorderLevel = $reorderLevel_query->first();
        }else{
            $reorderLevel = array();
        }
		$this->set('reorderLevel',$reorderLevel);
	}
    
    public function add() {
		if ($this->request->is('post')) {
			$newEntity = $this->ReorderLevels->newEntity();
            $patchEntity = $this->ReorderLevels->patchEntity($newEntity,$this->request->data,['validate' => false]);
            //pr($patchEntity);die;
			if ($this->ReorderLevels->save($patchEntity)) {
				$this->Flash->success(__('The reorder level has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Flash->error(__('The reorder level could not be saved. Please, try again.'));
			}
		}
		$kiosks_query = $this->ReorderLevels->Kiosks->find('list');
        $kiosks_query = $kiosks_query->hydrate(false);
        if(!empty($kiosks_query)){
            $kiosks = $kiosks_query->toArray();
        }else{
            $kiosks = array();
        }
		//$products = $this->ReorderLevel->Product->find('list');
		$products_query = $this->ReorderLevels->Products->find('list',[
                                                                    'keyField' => 'id',
                                                                    'valueField' => 'product',
                                                                    'conditions' => ['Products.status' => 1]
                                                                ]
                                                        );
        $products_query = $products_query->hydrate(false);
        if(!empty($products_query)){
            $products = $products_query->toArray();
        }else{
            $products = array();
        }
		$this->set(compact('kiosks', 'products'));
	}
    
    public function edit($id = null) {
		if (!$this->ReorderLevels->exists($id)) {
			throw new NotFoundException(__('Invalid reorder level'));
		}
		if ($this->request->is(array('post', 'put'))) {
            $getId = $this->ReorderLevels->get($id);
            $patchEntity = $this->ReorderLevels->patchEntity($getId,$this->request->data);
            //pr($patchEntity);die;
			if ($this->ReorderLevels->save($patchEntity)) {
				$this->Flash->success(__('The reorder level has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Flash->error(__('The reorder level could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('ReorderLevels.id' => $id));
			$ReorderLevel_query = $this->ReorderLevels->find('all', $options);
            $ReorderLevel_query = $ReorderLevel_query->hydrate(false);
            if(!empty($ReorderLevel_query)){
                $ReorderLevel = $ReorderLevel_query->first();
            }else{
                $ReorderLevel = array();
            }
            $this->request->data = $ReorderLevel;
		}
		$kiosks_query = $this->ReorderLevels->Kiosks->find('list');
        $kiosks_query = $kiosks_query->hydrate(false);
        if(!empty($kiosks_query)){
            $kiosks = $kiosks_query->toArray();
        }else{
            $kiosks = array();
        }
		$products_query = $this->ReorderLevels->Products->find('list');
        $products_query = $products_query->hydrate(false);
        if(!empty($products_query)){
            $products = $products_query->toArray();
        }else{
            $products = array();
        }
		$this->set(compact('kiosks', 'products'));
	}
    
    public function delete($id = null) {
		$getId = $this->ReorderLevels->get($id);
		if (!$this->ReorderLevels->exists($id)) {
			throw new NotFoundException(__('Invalid reorder level'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->ReorderLevels->delete($getId)) {
			$this->Flash->success(__('The reorder level has been deleted.'));
		} else {
			$this->Flash->error(__('The reorder level could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
}

?>
