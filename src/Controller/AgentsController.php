<?php
namespace App\Controller;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
 

class AgentsController extends AppController
{

    public $helpers = [
         'Paginator' => ['templates' => 'paginatortemplates']
         ];
     public function initialize(){
        parent::initialize();
        $activeOptions = $active = Configure::read('active');
        $featuredOptions = Configure::read('options.featured');
        $this->set(compact('featuredOptions'));
        $this->set(compact('activeOptions'));
     }
    public function index()
    {
        $activeOptions = $active = Configure::read('active');
        $this->set(compact('active'));
        $agents = $this->paginate($this->Agents);

        $this->set(compact('agents'));
        $this->set('_serialize', ['agents']);
    }

    
    public function view($id = null)
    {
        $agent = $this->Agents->get($id, [
            'contain' => []
        ]);

        $this->set('agent', $agent);
        $this->set('_serialize', ['agent']);
    }

    /**
     * Add method
     *
     * @return \Cake\Network\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $agent = $this->Agents->newEntity();
        if ($this->request->is('post')) {
            $agent = $this->Agents->patchEntity($agent, $this->request->data);
            if ($this->Agents->save($agent)) {
                $this->Flash->success(__('The agent has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The agent could not be saved. Please, try again.'));
        }
        $this->set(compact('agent'));
        $this->set('_serialize', ['agent']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Agent id.
     * @return \Cake\Network\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $agent = $this->Agents->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $agent = $this->Agents->patchEntity($agent, $this->request->data);
            if ($this->Agents->save($agent)) {
                $this->Flash->success(__('The agent has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The agent could not be saved. Please, try again.'));
        }
        $this->set(compact('agent'));
        $this->set('_serialize', ['agent']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Agent id.
     * @return \Cake\Network\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $agent = $this->Agents->get($id);
        if ($this->Agents->delete($agent)) {
            $this->Flash->success(__('The agent has been deleted.'));
        } else {
            $this->Flash->error(__('The agent could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
