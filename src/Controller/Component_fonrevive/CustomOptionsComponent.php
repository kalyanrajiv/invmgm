<?php
namespace App\Controller\Component;

use Cake\Controller\Component;

    class CustomOptionsComponent extends Component {
        public function category_options($categoryArr = array(),$html=false,$selectedOptions=array()) {
            $categoryOptions = array();
            $catArr = array();
            
            foreach($categoryArr as $sngCat){
                $catArr[$sngCat['id']] = $sngCat['category'];
            }
            $htmlOptions = "";
            foreach($categoryArr as $sngCat){
                $selected = "";
                if(in_array($sngCat['id'],$selectedOptions)){
                     $selected="selected='selected'";
                }
                if(!empty($sngCat['id_name_path'])){
                    $idNameArr = explode("|",$sngCat['id_name_path']);
                    $breadCrumbArr = array();
                    foreach($idNameArr as $id_name){
                        list($id,$category) = explode(":",$id_name);
                        $breadCrumbArr[$id] = $category;
                    }
                    $t_category = $sngCat['category'];
                    $breadCrumb = implode(" > ",$breadCrumbArr);
                    $categoryOptions[$sngCat['id']] = "$t_category ($breadCrumb)";
                    $htmlOptions.="<option title = '"."$t_category ($breadCrumb)"."' value='".$sngCat['id']."' $selected>"."$t_category ($breadCrumb)"."</option>";
                }else{
                    $categoryOptions[$sngCat['id']] = $sngCat['category'];
                    $htmlOptions.="<option value='".$sngCat['id']."' $selected>".$sngCat['category']."</option>";
                    //title = '".$sngCat['Category']['category']."'
                }
            }
            if($html){
                return $htmlOptions;
            }
            return $categoryOptions;
        }
    }
?>