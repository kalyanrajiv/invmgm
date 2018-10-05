<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Routing\Router;
use Cake\Validation\Validator;

 
class CustomersTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->table('customers');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

        
        $this->hasMany('ProductReceipts', [
            'foreignKey' => 'customer_id'
        ]);
       
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->integer('id')
            ->allowEmpty('id', 'create');

        $validator
            ->requirePresence('business', 'create')
            ->notEmpty('business');

        $validator
            ->email('email')
            ->requirePresence('email', 'create')
            ->notEmpty('email');

        //$validator
        //    ->requirePresence('mobile', 'create')
        //    ->notEmpty('mobile');
        //
        //$validator
        //    ->requirePresence('landline', 'create')
        //    ->notEmpty('landline');
        
        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
       $country = Router::getRequest()->data['country'];
        if($country == "OTH"){
            
        }else{
            $rules->add($rules->isUnique(['email']));
            //  $rules->add($rules->existsIn(['kiosk_id'], 'Kiosks'));
        }
        

        return $rules;
    }
}
