<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * ProblemTypes Model
 *
 * @method \App\Model\Entity\ProblemType get($primaryKey, $options = [])
 * @method \App\Model\Entity\ProblemType newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\ProblemType[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\ProblemType|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\ProblemType patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\ProblemType[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\ProblemType findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ProblemTypesTable extends Table
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

        $this->table('problem_types');
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
    //        ->requirePresence('problem_type', 'create')
    //        ->notEmpty('problem_type');
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
