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

class FaultyConditionsController extends AppController
{
     public $helpers = [
         'Paginator' => ['templates' => 'paginatortemplates']
         ];
    public function initialize()
    {
        parent::initialize();
        $this->loadModel('FaultyConditions');
        $statusOptions = Configure::read('active');
		$this->set(compact('statusOptions'));
    }
    public function index() {
		$faultyconditions = $this->FaultyConditions->find('all', array(
							'conditions' => array('FaultyConditions.internal_purpose' => 0)));
        $faultyconditions->hydrate(false);
        if(!empty($faultyconditions)){
         $faultyconditions  = $faultyconditions->toArray();
        }
		 //pr($faultyconditions);
		$this->set(compact('faultyconditions'));	
	}
    
    public function view($id = null) {
		if (!$this->FaultyConditions->exists($id)) {
			throw new NotFoundException(__('Invalid Faulty Condition'));
		}
		$options = array('conditions' => array('FaultyConditions.id' => $id));
        $result = $this->FaultyConditions->find('all', $options);
        $faultyresult = $result->first();
        $faultyresult = $faultyresult->toArray();
		$this->set('faultyconditions',$faultyresult);
        
	}
    
    public function add() {
        $brand = $this->FaultyConditions->newEntity();
        if ($this->request->is('post')) {
            $brand = $this->FaultyConditions->patchEntity($brand, $this->request->data);
            //pr($brand);die;
            if ($this->FaultyConditions->save($brand)) {
                $this->Flash->success(__('The Faulty Condition has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The Faulty Condition could not be saved. Please, try again.'));
        }
        //$this->set(compact('brand'));
        //$this->set('_serialize', ['brand']);
    }
    
//    public function add() {
//		$faultyconditions = $this->FaultyConditions->newEntity();
//		if ($this->request->is('post')) {
//			// pr($this->request->data);//die;
//			$faultyconditions1 = $this->FaultyConditions->patchEntity($faultyconditions,$this->request->data);
//            //pr($faultyconditions1);die;
//			if ($this->FaultyConditions->save($faultyconditions1)) {
//				// pr($this->request->data);die;
//				$this->Flash->success(__('The Faulty Condition has been saved.'));
//				return $this->redirect(array('action' => 'index'));
//			} else {
//				$this->Flash->error(__('The Faulty Condition could not be saved. Please, try again.'));
//			}
//		}
//	}
    
    public function edit($id = null) {
		if (!$this->FaultyConditions->exists($id)) {
			throw new NotFoundException(__('Invalid Mobile Condition'));
		}
        $faultyconditions = $this->FaultyConditions->get($id); 
		if ($this->request->is(array('post', 'put'))) {
			$faultyconditions1 = $this->FaultyConditions->patchEntity($faultyconditions,$this->request->data);
			  if ($this->FaultyConditions->save($faultyconditions1)) {
				$this->Flash->success(__('The Faulty Condition has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Flash->error(__('The Faulty Condition could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('FaultyConditions.id' => $id));
            $result = $this->FaultyConditions->find('all', $options);
            $resultfaulty = $result->first();
            $resultfaulty = $resultfaulty->toArray();
			$this->request->data = $resultfaulty;
		}
	}
}
?>