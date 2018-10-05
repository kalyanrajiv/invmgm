<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * SessionBackups Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Users
 * @property \Cake\ORM\Association\BelongsTo $Kiosks
 *
 * @method \App\Model\Entity\SessionBackup get($primaryKey, $options = [])
 * @method \App\Model\Entity\SessionBackup newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\SessionBackup[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\SessionBackup|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\SessionBackup patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\SessionBackup[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\SessionBackup findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class SessionBackupsTable extends Table
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

        $this->table('session_backups');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Kiosks', [
            'foreignKey' => 'kiosk_id',
            'joinType' => 'INNER'
        ]);
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
    //        ->integer('session_key')
    //        ->requirePresence('session_key', 'create')
    //        ->notEmpty('session_key');
    //
    //    $validator
    //        ->requirePresence('controller', 'create')
    //        ->notEmpty('controller');
    //
    //    $validator
    //        ->requirePresence('action', 'create')
    //        ->notEmpty('action');
    //
    //    return $validator;
    //}

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    //public function buildRules(RulesChecker $rules)
    //{
    //    $rules->add($rules->existsIn(['user_id'], 'Users'));
    //    $rules->add($rules->existsIn(['kiosk_id'], 'Kiosks'));
    //
    //    return $rules;
    //}
}
