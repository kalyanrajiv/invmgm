<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * MobileRepairPrices Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Brands
 * @property \Cake\ORM\Association\BelongsTo $MobileModels
 *
 * @method \App\Model\Entity\MobileRepairPrice get($primaryKey, $options = [])
 * @method \App\Model\Entity\MobileRepairPrice newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\MobileRepairPrice[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\MobileRepairPrice|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\MobileRepairPrice patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\MobileRepairPrice[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\MobileRepairPrice findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class MobileRepairPricesTable extends Table
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

        $this->table('mobile_repair_prices');
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
            ->integer('problem_type')
            ->requirePresence('problem_type', 'create')
            ->notEmpty('problem_type');

        $validator
            ->allowEmpty('problem');

        $validator
            ->numeric('repair_cost')
            ->requirePresence('repair_cost', 'create')
            ->notEmpty('repair_cost');

        $validator
            ->numeric('repair_price')
            ->requirePresence('repair_price', 'create')
            ->notEmpty('repair_price');

        $validator
            ->integer('repair_days')
            ->requirePresence('repair_days', 'create')
            ->notEmpty('repair_days');

        //$validator
        //    ->integer('status')
        //    ->requirePresence('status', 'create')
        //    ->notEmpty('status');

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

        return $rules;
    }
}
