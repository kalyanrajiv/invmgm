<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * FaultyConditions Model
 *
 * @method \App\Model\Entity\FaultyCondition get($primaryKey, $options = [])
 * @method \App\Model\Entity\FaultyCondition newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\FaultyCondition[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\FaultyCondition|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\FaultyCondition patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\FaultyCondition[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\FaultyCondition findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class FaultyConditionsTable extends Table
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

        $this->table('faulty_conditions');
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
    
}
