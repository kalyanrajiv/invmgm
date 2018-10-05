<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Agents Model
 *
 * @method \App\Model\Entity\Agent get($primaryKey, $options = [])
 * @method \App\Model\Entity\Agent newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Agent[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Agent|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Agent patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Agent[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Agent findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class PmtLogsTable extends Table
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

        $this->table('pmt_logs');
        //$this->displayField('name');
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
    //        ->requirePresence('name', 'create')
    //        ->notEmpty('name');
    //
    //    $validator
    //        ->requirePresence('memo', 'create')
    //        ->notEmpty('memo');
    //
    //    $validator
    //        ->integer('status')
    //        ->requirePresence('status', 'create')
    //        ->notEmpty('status');
    //
    //    return $validator;
    //}
}
