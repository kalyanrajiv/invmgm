<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * MobileConditions Model
 *
 * @method \App\Model\Entity\MobileCondition get($primaryKey, $options = [])
 * @method \App\Model\Entity\MobileCondition newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\MobileCondition[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\MobileCondition|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\MobileCondition patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\MobileCondition[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\MobileCondition findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class MobileConditionsTable extends Table
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

        $this->table('mobile_conditions');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    //public function validationDefault(Validator $validator)
    //{
    //    $validator
    //        ->integer('id')
    //        ->allowEmpty('id', 'create');
    //
    //    $validator
    //        ->requirePresence('mobile_condition', 'create')
    //        ->notEmpty('mobile_condition');
    //
    //    $validator
    //        ->requirePresence('description', 'create')
    //        ->notEmpty('description');
    //
    //    $validator
    //        ->integer('status')
    //        ->requirePresence('status', 'create')
    //        ->notEmpty('status');
    //
    //    return $validator;
    //}
}
