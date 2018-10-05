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
     $sites1 = Configure::read('sites');
	 $path = dirname(__FILE__);
		$isboloRam1 = strpos($path, ADMIN_DOMAIN);
		if($isboloRam1 == false){
            $this->Flash->error(__("This function works only on ". ADMIN_DOMAIN));
			return $this->redirect(array('controller' => 'home','action' => "dashboard"));die;
		}
        $color = $this->Colors->newEntity();
        if ($this->request->is('post')) {
          $boloram_data = $this->request->data;
            $color = $this->Colors->patchEntity($color, $this->request->getData());
            if ($this->Colors->save($color)) {
               if(!empty($sites1)){
                    foreach($sites1 as $site_id => $site_val){
                           $this->add_on_boloram($boloram_data,$site_val);  		
                    }
                    
               }
               
               
                  $sites = Configure::read('site_full_url');
                  $path = dirname(__FILE__);
                  $isboloRam = false;
                  $domain_name = "";
                  foreach($sites as $site_name => $site_path){
                          $isboloRam = strpos($path,$site_name);
                          if($isboloRam){
                                  $domain_name =  $site_path;
                                  break;
                          }
                  }
                  if($isboloRam != false){
                     $filename = "/var/www/vhosts/$domain_name/httpdocs/config/color.php";
                          require($filename);
                  }
                  
                  
                  //$path = dirname(__FILE__); 
                  //$isboloRam = strpos($path,"mbwaheguru");
                  
                  
                  
                  //if($isboloRam != false){
                  //   //echo "mbwaheguru";die;
                  //       $filename = "/var/www/vhosts/mbwaheguru.co.uk/httpdocs/config/color.php";
                  //        require($filename);
                  //}else{
                  //    $filename = "/var/www/vhosts/hpwaheguru.co.uk/httpdocs/config/color.php";
                  //     require($filename);
                  // }
                   
                   
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
                        $colorName1 = "'".$color['name']."' => '".$color['name']."', " ;
                        $colorName =  rtrim($colorName1, ',');
                         fwrite($myfile, "\n".  $colorName);
                      }
                     $br = "] ,\n ";
                     fwrite($myfile, "\n".  $br);
                     //color
                     $color2 = "'color'=>[";
                     fwrite($myfile, "\n".  $color2);
                  
 
                     foreach($colors as $color){
                        $colorName2 = "'".$color['id']."' => '". $color['name']."', " ;
                        $colorName1 =  rtrim($colorName2, ',');
                         fwrite($myfile, "\n".  $colorName1);
                      }
                     $br2 = "] \n ]?>";
                     fwrite($myfile, "\n".  $br2);
                     fclose($myfile);
                     $this->Flash->success(__('The color has been saved.'));

                    return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The color could not be saved. Please, try again.'));
        }
        $this->set(compact('color'));
    }

    
    public function edit($id = null){
	$this->Flash->error(__("For Safty Purpose We Have Disabled This Functionality."));
			return $this->redirect(array('controller' => 'home','action' => "dashboard"));die;
          $sites1 = Configure::read('sites');
          $path = dirname(__FILE__);
		$isboloRam1 = strpos($path, ADMIN_DOMAIN);
		if($isboloRam1 == false){
            $this->Flash->error(__("This function works only on ". ADMIN_DOMAIN));
			return $this->redirect(array('controller' => 'home','action' => "dashboard"));die;
		}
          
        $color = $this->Colors->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
          $boloramData = $this->request->data;
          
            $color = $this->Colors->patchEntity($color, $this->request->getData());
            if ($this->Colors->save($color)) {
               if(!empty($sites1)){
                    foreach($sites1 as $site_id => $site_value){
                          $this->update_boloram($boloramData,$site_value,$id);	  
                    }
                
               }
                  $sites = Configure::read('site_full_url');
                  $path = dirname(__FILE__);
                  $isboloRam = false;
                  $domain_name = "";
                  foreach($sites as $site_name => $site_path){
                          $isboloRam = strpos($path,$site_name);
                          if($isboloRam){
                                  $domain_name =  $site_path;
                                  break;
                          }
                  }
                  if($isboloRam != false){
                     $filename = "/var/www/vhosts/$domain_name/httpdocs/config/color.php";
                          require($filename);
                  }
               
               
               
            //     $path = dirname(__FILE__); 
            //$isboloRam = strpos($path,"mbwaheguru");
            //if($isboloRam != false){
            //   //echo "mbwaheguru";die;
            //       $filename = "/var/www/vhosts/mbwaheguru.co.uk/httpdocs/config/color.php";
            //        require($filename);
            //}else{
            //   $filename = "/var/www/vhosts/hpwaheguru.co.uk/httpdocs/config/color.php";
            //    require($filename);
            //}
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
                    $colorName1 = "'".$color['name']."' => '".$color['name']."', " ;
                    $colorName =  rtrim($colorName1, ',');
                     fwrite($myfile, "\n".  $colorName);
            }
            $br = "] ,\n ";
            fwrite($myfile, "\n".  $br);
               //color
            $color2 = "'color'=>[";
            fwrite($myfile, "\n".  $color2);
            foreach($colors as $color){
                    $colorName2 = "'".$color['id']."' => '".$color['name']."', " ;
                    $colorName1 =  rtrim($colorName2, ',');
                     fwrite($myfile, "\n".  $colorName1);
            }
            $br2 = "] \n ]?>";
            fwrite($myfile, "\n".  $br2);
            fclose($myfile);
            $this->Flash->success(__('The color has been saved.'));
            return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The color could not be saved. Please, try again.'));
        }
        $this->set(compact('color'));
   }
 
    public function delete($id = null){
	$this->Flash->error(__("For Safty Purpose We Have Disabled This Functionality."));
			return $this->redirect(array('controller' => 'home','action' => "dashboard"));die;
		
        $this->request->allowMethod(['post', 'delete']);
        $color = $this->Colors->get($id);
        if ($this->Colors->delete($color)) {
               $sites = Configure::read('site_full_url');
               $path = dirname(__FILE__);
               $isboloRam = false;
               $domain_name = "";
               foreach($sites as $site_name => $site_path){
                       $isboloRam = strpos($path,$site_name);
                       if($isboloRam){
                               $domain_name =  $site_path;
                               break;
                       }
               }
               if($isboloRam != false){
                  $filename = "/var/www/vhosts/$domain_name/httpdocs/config/color.php";
                       require($filename);
               }
         
         
               //$isboloRam = strpos($path,"mbwaheguru");
               //if($isboloRam != false){
               //   //echo "mbwaheguru";die;
               //       $filename = "/var/www/vhosts/mbwaheguru.co.uk/httpdocs/config/color.php";
               //        require($filename);
               //}else{
               //   $filename = "/var/www/vhosts/hpwaheguru.co.uk/httpdocs/config/color.php";
               //    require($filename);
               //}
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
               $colorName1 = "'".$color['name']."' => '". $color['name']."', " ;
               $colorName =  rtrim($colorName1, ',');
                fwrite($myfile, "\n".  $colorName);
             }
             $br = "] ,\n ";
             fwrite($myfile, "\n".  $br);
            //color
            $color2 = "'color'=>[";
            fwrite($myfile, "\n".  $color2);
            foreach($colors as $color){
                    $colorName2 = "'".$color['id']."' => '". $color['name']."', " ;
                    $colorName1 =  rtrim($colorName2, ',');
                     fwrite($myfile, "\n".  $colorName1);
            }
            $br2 = "] \n ]?>";
            fwrite($myfile, "\n".  $br2);
      
             fclose($myfile);
             $this->Flash->success(__('The color has been deleted.'));
         } else {
            $this->Flash->error(__('The color could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
   }
   
   public function add_on_boloram($boloram_data,$site_val){
     $boloram_data['created'] = date("Y-m-d h-i-s");
     $boloram_data['modified'] = date("Y-m-d h-i-s");
     $conn = ConnectionManager::get($site_val);
     $conn->insert('colors',$boloram_data);
   }
   
   public function update_boloram($boloramData,$site_value,$id){
     $connection = ConnectionManager::get($site_value);
     //$connection->load('Timestamp');
     $res =$connection->update('colors',$boloramData ,['id' => $id]);
     
   }
   
}
