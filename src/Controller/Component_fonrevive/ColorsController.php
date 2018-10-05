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
class ColorsController extends AppController{

    /**
     * Index method
     *
     * @return \Cake\Http\Response|void
     */
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
        $colors = $this->paginate($this->Colors);

        $this->set(compact('colors'));
    }

    
    public function view($id = null)
    {
        $color = $this->Colors->get($id, [
            'contain' => []
        ]);

        $this->set('color', $color);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $color = $this->Colors->newEntity();
        if ($this->request->is('post')) {
            $color = $this->Colors->patchEntity($color, $this->request->getData());
            if ($this->Colors->save($color)) {
                 $myfile = fopen("..\config\color.php", "w") or die("Unable to open file!");
                  $color_query = $this->Colors->find('all',[
                                                    'keyField' => 'id',
                                                     'valueField' => 'name',
                                                    
                                                 ]
                                        ); 
           
 
                $color_query = $color_query->hydrate(false);
                 if(!empty($color_query)){
                      $colors = $color_query->toArray();
                 }
                 $text = "<?php \n return[";
                  fwrite($myfile, "\n".  $text);
                  $color1 = "'colour'=>[";
                   fwrite($myfile, "\n".  $color1);
                  
 
                 foreach($colors as $color){
                    $colorName1 = "'".$color['id'] ." ' => '". $color['name'] ."', " ;
                   
                     $colorName =  rtrim($colorName1, ',');
                 fwrite($myfile, "\n".  $colorName);
               
                     
                }
               
                  $br = "] \n ]?>";
               
                 fwrite($myfile, "\n".  $br);
               
                
               
                fclose($myfile);
                $this->Flash->success(__('The color has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The color could not be saved. Please, try again.'));
        }
        $this->set(compact('color'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Color id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $color = $this->Colors->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $color = $this->Colors->patchEntity($color, $this->request->getData());
            if ($this->Colors->save($color)) {
                $path = dirname(__FILE__); 
            $isboloRam = strpos($path,"mbwaheguru");
            if($isboloRam != false){
               //echo "mbwaheguru";die;
                   $filename = "/var/www/vhosts/mbwaheguru.co.uk/httpdocs/httpdocs/config\color.php";
                    require($filename);
               
               
               
            }else{
                $filename = "/var/www/vhosts/hpwaheguru.co.uk/httpdocs/config\color.php";
                 require($filename);
              
               
                
            }
                 $myfile = fopen($filename, "w") or die("Unable to open file!");
                $color_query = $this->Colors->find('all',[
                                                    'keyField' => 'id',
                                                     'valueField' => 'name',
                                                    
                                                 ]
                                        ); 
           
 
                $color_query = $color_query->hydrate(false);
                 if(!empty($color_query)){
                      $colors = $color_query->toArray();
                 }
                  $text = "<?php \n return[";
                  fwrite($myfile, "\n".  $text);
                  $color1 = "'colour'=>[";
                   fwrite($myfile, "\n".  $color1);
                  
 
                 foreach($colors as $color){
                    $colorName1 = "'".$color['id'] ." ' => '". $color['name'] ."', " ;
                   
                     $colorName =  rtrim($colorName1, ',');
                 fwrite($myfile, "\n".  $colorName);
               
                     
                }
               
                  $br = "] \n ]?>";
               
                 fwrite($myfile, "\n".  $br);
               
                
               
                fclose($myfile);
                $this->Flash->success(__('The color has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The color could not be saved. Please, try again.'));
        }
        $this->set(compact('color'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Color id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $color = $this->Colors->get($id);
        if ($this->Colors->delete($color)) {
              $myfile = fopen("..\config\color.php", "w") or die("Unable to open file!");
                 $color_query = $this->Colors->find('all',[
                                                    'keyField' => 'id',
                                                     'valueField' => 'name',
                                                    
                                                 ]
                                        ); 
           
 
                $color_query = $color_query->hydrate(false);
                 if(!empty($color_query)){
                      $colors = $color_query->toArray();
                 }
                  $text = "<?php \n return[";
                  fwrite($myfile, "\n".  $text);
                  $color1 = "'colour'=>[";
                   fwrite($myfile, "\n".  $color1);
                  
 
                 foreach($colors as $color){
                    $colorName1 = "'".$color['id'] ." ' => '". $color['name'] ."', " ;
                   
                     $colorName =  rtrim($colorName1, ',');
                 fwrite($myfile, "\n".  $colorName);
               
                     
                }
               
                  $br = "] \n ]?>";
               
                 fwrite($myfile, "\n".  $br);
               
                
               
                fclose($myfile);
            $this->Flash->success(__('The color has been deleted.'));
        } else {
            $this->Flash->error(__('The color could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
