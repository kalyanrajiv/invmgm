<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * MobileUnlockPrices Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Brands
 * @property \Cake\ORM\Association\BelongsTo $MobileModels
 * @property \Cake\ORM\Association\BelongsTo $Networks
 *
 * @method \App\Model\Entity\MobileUnlockPrice get($primaryKey, $options = [])
 * @method \App\Model\Entity\MobileUnlockPrice newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\MobileUnlockPrice[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\MobileUnlockPrice|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\MobileUnlockPrice patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\MobileUnlockPrice[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\MobileUnlockPrice findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class MobileUnlockPricesTable extends Table
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

        $this->table('mobile_unlock_prices');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Brands', [
            'foreignKey' => 'brand_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('MobileModels', [
            'foreignKey' => 'mobile_model_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Networks', [
            'foreignKey' => 'network_id',
            'joinType' => 'INNER'
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
        //$validator
        //    ->integer('id')
        //    ->allowEmpty('id', 'create');
        
        $validator
            ->integer('brand_id')
             ->requirePresence('brand_id', 'create')
            ->notEmpty('brand_id', 'brand  cannot be empty');
        
        $validator
            ->integer('mobile_model_id')
             ->requirePresence('mobile_model_id', 'create')
            ->notEmpty('mobile_model_id', 'mobile cannot be empty');
            
        $validator
            ->integer('network_id')
            ->requirePresence('network_id', 'create')
            ->notEmpty('network_id', 'network cannot be empty');    
        
        $validator
            ->numeric('unlocking_cost',"unlocking cost value should be integer")
            ->requirePresence('unlocking_cost', 'create')
            ->notEmpty('unlocking_cost');

        $validator
            ->numeric('unlocking_price','unlocking price value should be integer')
            ->requirePresence('unlocking_price', 'create')
            ->notEmpty('unlocking_price');

        $validator
            ->integer('unlocking_days','unlocking days value should be integer')
            ->requirePresence('unlocking_days', 'create')
            ->notEmpty('unlocking_days');

        //$validator
        //    ->integer('unlocking_minutes')
        //    ->allowEmpty('unlocking_minutes');

        //$validator
        //    ->integer('status')
        //    ->requirePresence('status', 'create')
        //    ->notEmpty('status');

        //$validator
        //    ->dateTime('status_change_date')
        //    ->allowEmpty('status_change_date');
        //
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
        $rules->add($rules->existsIn(['brand_id'], 'Brands'));
        $rules->add($rules->existsIn(['mobile_model_id'], 'MobileModels'));
        $rules->add($rules->existsIn(['network_id'], 'Networks'));

        return $rules;
    }
}
