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
class FunctionConditionsController extends AppController{
     public $helpers = [
         'Paginator' => ['templates' => 'paginatortemplates']
         ];
    public function initialize()
    {
        parent::initialize();
        $this->loadModel('FunctionConditions');
        $statusOptions = Configure::read('active');
        $this->set(compact('statusOptions'));      
    }
    
    public function index(){
        $functionconditions = $this->FunctionConditions->find('all');
        $functionconditions->hydrate(false);
        if(!empty($functionconditions)){
         $functionconditions  = $functionconditions->toArray();
        }
        $this->set(compact('functionconditions'));
        
    }
    
    public function view($id = null) {
		if (!$this->FunctionConditions->exists($id)) {
			throw new NotFoundException(__('Invalid Mobile Condition'));
		} 
        $functionconditions = $this->FunctionConditions->get($id,[
                  //'contain' => ['KioskProductSales','Customers']
                   ]);
        if(!empty($functionconditions)){
         $this->request->data =$functionconditions->toArray();
        }
		$this->set(compact('functionconditions'));
	}
    
    public function edit($id = null) {
		if (!$this->FunctionConditions->exists($id)) {
			throw new NotFoundException(__('Invalid Mobile Condition'));
		}
        $functionconditions = $this->FunctionConditions->get($id,[
                  //'contain' => ['KioskProductSales','Customers']
                   ]);
            if(!empty($functionconditions)){
             $result =$functionconditions->toArray();
            }
		if ($this->request->is(['patch', 'post', 'put'])) {
             $functionconditions = $this->FunctionConditions->patchEntity($functionconditions, $this->request->data);
             //pr($mobileconditions1);die;
			  if ($this->FunctionConditions->save($functionconditions)) {
                $this->Flash->success(__('The Function Condition has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
                $this->Flash->error(__('The Function Condition could not be saved. Please, try again.'));
			}
		}
        $this->request->data = $result;
        $this->set(compact('functionconditions'));
	}
    
    public function add() {
        $functionconditions = $this->FunctionConditions->newEntity();
        if ($this->request->is('post')) {
            $functionconditions = $this->FunctionConditions->patchEntity($functionconditions, $this->request->data);
            if ($this->FunctionConditions->save($functionconditions)) {
                $this->Flash->success(__('The Mobile Condition has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The Mobile Condition could not be saved. Please, try again.'));
        }
        $this->set(compact('functionconditions'));
        $this->set('_serialize', ['functionconditions']);
    }
}

?>