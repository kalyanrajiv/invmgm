<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * FunctionConditions Model
 *
 * @method \App\Model\Entity\FunctionCondition get($primaryKey, $options = [])
 * @method \App\Model\Entity\FunctionCondition newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\FunctionCondition[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\FunctionCondition|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\FunctionCondition patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\FunctionCondition[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\FunctionCondition findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class FunctionConditionsTable extends Table
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

        $this->table('function_conditions');
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
    public function validationDefault(Validator $validator)
    {
        $validator
            ->integer('id')
            ->allowEmpty('id', 'create');

        $validator
            ->requirePresence('function_condition', 'create')
            ->notEmpty('function_condition');

        $validator
            ->allowEmpty('description');

        $validator
            ->integer('status')
            ->requirePresence('status', 'create')
            ->notEmpty('status');

        return $validator;
    }
}
