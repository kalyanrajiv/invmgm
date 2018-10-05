<?php
namespace App\Controller;

use App\Controller\AppController;

use Cake\Event\Event;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Network\Request;
use Cake\ORM\TableRegistry;
use Cake\Mailer\Email;

/**
 * ProblemTypes Controller
 *
 * @property \App\Model\Table\ProblemTypesTable $ProblemTypes
 */
class ProblemTypesController extends AppController
{
    public $helpers = [
         'Paginator' => ['templates' => 'paginatortemplates']
         ];
   public function initialize(){
        parent::initialize();
        $statusOptions = Configure::read('active');
		$this->set(compact('statusOptions'));
        
   }
    public function index()
    {
        $problemTypes = $this->paginate($this->ProblemTypes);

        $this->set(compact('problemTypes'));
        $this->set('_serialize', ['problemTypes']);
    }

    /**
     * View method
     *
     * @param string|null $id Problem Type id.
     * @return \Cake\Network\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $problemType = $this->ProblemTypes->get($id, [
            'contain' => []
        ]);

        $this->set('problemType', $problemType);
        $this->set('_serialize', ['problemType']);
    }

    /**
     * Add method
     *
     * @return \Cake\Network\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $problemType = $this->ProblemTypes->newEntity();
        if ($this->request->is('post')) {
            $problemType = $this->ProblemTypes->patchEntity($problemType, $this->request->data);
            if ($this->ProblemTypes->save($problemType)) {
                $this->Flash->success(__('The problem type has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The problem type could not be saved. Please, try again.'));
        }
        $this->set(compact('problemType'));
        $this->set('_serialize', ['problemType']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Problem Type id.
     * @return \Cake\Network\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $problemType = $this->ProblemTypes->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $problemType = $this->ProblemTypes->patchEntity($problemType, $this->request->data);
          //  pr($problemType);die;
            if ($this->ProblemTypes->save($problemType)) {
                $this->Flash->success(__('The problem type has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The problem type could not be saved. Please, try again.'));
        }
        $this->set(compact('problemType'));
        $this->set('_serialize', ['problemType']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Problem Type id.
     * @return \Cake\Network\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $problemType = $this->ProblemTypes->get($id);
        if ($this->ProblemTypes->delete($problemType)) {
            $this->Flash->success(__('The problem type has been deleted.'));
        } else {
            $this->Flash->error(__('The problem type could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
