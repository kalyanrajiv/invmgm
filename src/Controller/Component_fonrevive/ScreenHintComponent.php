<?php
namespace App\Controller\Component;
use Cake\Controller\Component;
use Cake\ORM\TableRegistry;

    class ScreenHintComponent extends Component {
        //public $uses = array('ScreenHint');
        public function hint($controller,$action){
            if(!empty($controller) && !empty($action)){
                $screenHintTable = TableRegistry::get("screen_hints");
                $result_query = $screenHintTable->find('all', array('conditions' => array('screen_hints.controller'=>$controller,'screen_hints.action' => $action)));
                $result = $result_query->first();
                if(!empty($result)){
                    $result = $result->toArray();
                }
                if(count($result)){
                    $hint =  $result;//["ScreenHint"]["hint"]
                    if(!empty($hint)){
                        return $hint;
                    }else{
                        return false;
                    }
                }else{
                    return false;
                }
            }
            return false;
        }
    }