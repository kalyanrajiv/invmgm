<?php
namespace App\Controller;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use App\Controller\AppController;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use Cake\ORM\Entity;
 use Cake\ORM\AssociationCollection;
 use Cake\ORM\Association;
 use Cake\Datasource\ConnectionManager;
use Cake\Database\Schema\Collection;
use Cake\Database\Schema\TableSchema;
class CommentsController extends AppController
{

     public $helpers = [
         'Paginator' => ['templates' => 'paginatortemplates']
         ];
     public function initialize(){
        parent::initialize();
        $activeOptions = $active = Configure::read('active');
        $featuredOptions = Configure::read('options.featured');
        $this->set(compact('featuredOptions'));
        $this->set(compact('active'));
     }
    public function index()
    {
        $this->paginate = [
            'contain' => ['Users', 'Posts']
        ];
        $comments = $this->paginate($this->Comments);

        $this->set(compact('comments'));
        $this->set('_serialize', ['comments']);
    }

    /**
     * View method
     *
     * @param string|null $id Comment id.
     * @return \Cake\Network\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $comment = $this->Comments->get($id, [
            'contain' => ['Users', 'Posts']
        ]);

        $this->set('comment', $comment);
        $this->set('_serialize', ['comment']);
    }

    /**
     * Add method
     *
     * @return \Cake\Network\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $comment = $this->Comments->newEntity();
        if ($this->request->is('post')) {
            $comment = $this->Comments->patchEntity($comment, $this->request->data);
            if ($this->Comments->save($comment)) {
                $this->Flash->success(__('The comment has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The comment could not be saved. Please, try again.'));
        }
        $users_query = $this->Comments->Users->find('list', [
                                                       'keyField' => "id",
														'valueField' => "username", 
                                                       ]);
        $users_query->hydrate(false);
        if(!empty($users_query)){
             $users = $users_query->toArray();
        }else{
             $users = array();
        }
       
        $posts_query = $this->Comments->Posts->find('list', ['limit' => 200]);
        $posts_query->hydrate(false);
        if(!empty($posts_query)){
             $posts = $posts_query->toArray();
        }else{
             $posts = array();
        }
        $this->set(compact('comment', 'users', 'posts'));
        $this->set('_serialize', ['comment']);
    }

    
    public function edit($id = null)
    {
        $comment = $this->Comments->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $comment = $this->Comments->patchEntity($comment, $this->request->data);
            if ($this->Comments->save($comment)) {
                $this->Flash->success(__('The comment has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The comment could not be saved. Please, try again.'));
        }
        $users_query = $this->Comments->Users->find('list', [
                                                       'keyField' => "id",
														'valueField' => "username", 
                                                       ]);
        $users_query->hydrate(false);
        if(!empty($users_query)){
             $users = $users_query->toArray();
        }else{
             $users = array();
        }
       
        $posts_query = $this->Comments->Posts->find('list', ['limit' => 200]);
        $posts_query->hydrate(false);
        if(!empty($posts_query)){
             $posts = $posts_query->toArray();
        }else{
             $posts = array();
        }
        $this->set(compact('comment', 'users', 'posts'));
        $this->set('_serialize', ['comment']);
    }

     
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $comment = $this->Comments->get($id);
        if ($this->Comments->delete($comment)) {
            $this->Flash->success(__('The comment has been deleted.'));
        } else {
            $this->Flash->error(__('The comment could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
