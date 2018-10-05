<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * RepairLog Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Repairs
 * @property \Cake\ORM\Association\BelongsTo $Users
 *
 * @method \App\Model\Entity\RepairLog get($primaryKey, $options = [])
 * @method \App\Model\Entity\RepairLog newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\RepairLog[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\RepairLog|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\RepairLog patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\RepairLog[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\RepairLog findOrCreate($search, callable $callback = null, $options = [])
 */
class RepairLogTable extends Table
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

        $this->table('repair_log');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->belongsTo('Repairs', [
            'foreignKey' => 'repair_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
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
            ->integer('comments')
            ->allowEmpty('comments');

        $validator
            ->integer('repair_status')
            ->requirePresence('repair_status', 'create')
            ->notEmpty('repair_status');

        $validator
            ->integer('status')
            ->requirePresence('status', 'create')
            ->notEmpty('status');

        $validator
            ->dateTime('created_at')
            ->requirePresence('created_at', 'create')
            ->notEmpty('created_at');

        $validator
            ->dateTime('updated_at')
            ->requirePresence('updated_at', 'create')
            ->notEmpty('updated_at');

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
        $rules->add($rules->existsIn(['repair_id'], 'Repairs'));
        $rules->add($rules->existsIn(['user_id'], 'Users'));

        return $rules;
    }
}
