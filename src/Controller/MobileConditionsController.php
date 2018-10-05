<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Event\Event;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Network\Request;
use Cake\ORM\TableRegistry;
use Cake\Mailer\Email;

use Cake\Datasource\ConnectionManager;

class MobileConditionsController extends AppController{
     public $helpers = [
         'Paginator' => ['templates' => 'paginatortemplates']
         ];
    public function initialize()
    {
        parent::initialize();
        $this->loadModel('MobileConditions');
        $statusOptions = Configure::read('active');
        $this->set(compact('statusOptions'));      
    }
    
    public function index(){
        $mobileconditions = $this->MobileConditions->find('all');
        $mobileconditions->hydrate(false);
        if(!empty($mobileconditions)){
         $mobileconditions  = $mobileconditions->toArray();
        }
        $this->set(compact('mobileconditions'));
    }
    
    public function view($id = null) {
		if (!$this->MobileConditions->exists($id)) {
			throw new NotFoundException(__('Invalid Mobile Condition'));
		} 
        $mobileconditions = $this->MobileConditions->get($id,[
                  //'contain' => ['KioskProductSales','Customers']
                   ]);
        if(!empty($mobileconditions)){
         $this->request->data =$mobileconditions->toArray();
        }
		$this->set(compact('mobileconditions'));
	}
    
    public function edit($id = null) {
		if (!$this->MobileConditions->exists($id)) {
			throw new NotFoundException(__('Invalid Mobile Condition'));
		}
        $mobileconditions_id = $this->MobileConditions->get($id);
       
            if(!empty($mobileconditions_id)){
             $mobileconditions = $mobileconditions_id->toArray();
            }
             //pr($mobileconditions);die;
		if ($this->request->is(['patch', 'post', 'put'])) {
            //pr($this->request->data);die;
             $mobileconditions1 = $this->MobileConditions->patchEntity($mobileconditions_id, $this->request->data);
             //pr($mobileconditions1);die;
            if ($this->MobileConditions->save($mobileconditions1)){
              $this->Flash->success(__('The Mobile Condition has been saved.'));
              return $this->redirect(array('action' => 'index'));
            }else{
              $this->Flash->error(__('The Mobile Condition could not be saved. Please, try again.'));
            }
		}
        $this->request->data = $mobileconditions;
        $this->set(compact('mobileconditions'));
	}
    
    
    public function add() {
        $mobileconditions = $this->MobileConditions->newEntity();
        if ($this->request->is('post')) {
            $mobileconditions = $this->MobileConditions->patchEntity($mobileconditions, $this->request->data);
            if ($this->MobileConditions->save($mobileconditions)) {
                $this->Flash->success(__('The Mobile Condition has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The Mobile Condition could not be saved. Please, try again.'));
        }
        $this->set(compact('mobileconditions'));
        $this->set('_serialize', ['mobileconditions']);
    }
    
}

?>