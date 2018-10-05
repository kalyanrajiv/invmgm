<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * ScreenHints Model
 *
 * @method \App\Model\Entity\ScreenHint get($primaryKey, $options = [])
 * @method \App\Model\Entity\ScreenHint newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\ScreenHint[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\ScreenHint|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\ScreenHint patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\ScreenHint[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\ScreenHint findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ScreenHintsTable extends Table
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

        $this->table('screen_hints');
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
    //        ->requirePresence('controller', 'create')
    //        ->notEmpty('controller');
    //
    //    $validator
    //        ->requirePresence('action', 'create')
    //        ->notEmpty('action');
    //
    //    $validator
    //        ->integer('status')
    //        ->requirePresence('status', 'create')
    //        ->notEmpty('status');
    //
    //    return $validator;
    //}
}
