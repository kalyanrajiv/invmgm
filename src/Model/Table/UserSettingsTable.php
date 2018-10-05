<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * UserSetting Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Users
 *
 * @method \App\Model\Entity\UserSetting get($primaryKey, $options = [])
 * @method \App\Model\Entity\UserSetting newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\UserSetting[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\UserSetting|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\UserSetting patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\UserSetting[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\UserSetting findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class UserSettingTable extends Table
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

        $this->table('user_setting');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

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
        //$validator
        //    ->integer('id')
        //    ->allowEmpty('id', 'create');
        //
        //$validator
        //    ->requirePresence('user_session_key', 'create')
        //    ->notEmpty('user_session_key');
        //
        //$validator
        //    ->requirePresence('setting_name', 'create')
        //    ->notEmpty('setting_name');
        //
        //$validator
        //    ->requirePresence('data', 'create')
        //    ->notEmpty('data');
        //
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
        $rules->add($rules->existsIn(['user_id'], 'Users'));

        return $rules;
    }
}
