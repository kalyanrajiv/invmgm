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

class PostsController extends AppController{
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
    public function index(){
        $this->paginate = [
            'contain' => ['Users']
        ];
        $posts = $this->paginate($this->Posts);

        $this->set(compact('posts'));
        $this->set('_serialize', ['posts']);
    }
     
    public function view($id = null)
    {
        $activeOptions = $active = Configure::read('active');
        $featuredOptions = Configure::read('options.featured');
        $this->set(compact('featuredOptions'));
        $this->set(compact('activeOptions'));
        $post = $this->Posts->get($id, [
            'contain' => ['Users', 'Comments']
        ]);

        $this->set('post', $post);
        $this->set('_serialize', ['post']);
    }
 
    public function add()
    {
        $post = $this->Posts->newEntity();
        if ($this->request->is('post')) {
            $post = $this->Posts->patchEntity($post, $this->request->data);
            if ($this->Posts->save($post)) {
                $this->Flash->success(__('The post has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The post could not be saved. Please, try again.'));
        }
        $users_query = $this->Users->find('list',array(
														 'keyField' => 'id',
                                         'valueField' => 'username'
														));
        if(!empty($users_query)){
         $users = $users_query->toArray();
        }else{
            $users = array();
        }
        $this->set(compact('post', 'users'));
        $this->set('_serialize', ['post']);
    }

     
    public function edit($id = null)
    {
        $post = $this->Posts->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $post = $this->Posts->patchEntity($post, $this->request->data);
            if ($this->Posts->save($post)) {
                $this->Flash->success(__('The post has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The post could not be saved. Please, try again.'));
        }
        $users_query = $this->Users->find('list',array(
														 'keyField' => 'id',
                                         'valueField' => 'username'
														));
        if(!empty($users_query)){
         $users = $users_query->toArray();
        }else{
            $users = array();
        }
        //pr($users);
        $this->set(compact('post', 'users'));
        $this->set('_serialize', ['post']);
    }

    
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $post = $this->Posts->get($id);
        if ($this->Posts->delete($post)) {
            $this->Flash->success(__('The post has been deleted.'));
        } else {
            $this->Flash->error(__('The post could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
