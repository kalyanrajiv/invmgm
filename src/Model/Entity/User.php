<?php
namespace App\Model\Entity;
use Cake\Auth\DefaultPasswordHasher;
use Cake\ORM\Entity;
 
class User extends Entity
{
 
    protected $_accessible = [
        '*' => true,
        'id' => false
    ];

     
    protected $_hidden = [
        'password'
    ];
   
    protected function _setPassword($password)
    {
        return (new DefaultPasswordHasher)->hash($password);
    }
	
	public function parentNode()
    {
        if (!$this->id) {
            return null;
        }
        if (isset($this->group_id)) {
            $groupId = $this->group_id;
        } else {
            $Users = TableRegistry::get('Users');
            $user = $Users->find('all', ['fields' => ['group_id']])->where(['id' => $this->id])->first();
            $groupId = $user->group_id;
        }
        if (!$groupId) {
            return null;
        }
        return ['Groups' => ['id' => $groupId]];
    }
}
