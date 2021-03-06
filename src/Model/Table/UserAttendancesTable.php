<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * UserAttendances Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Kiosks
 * @property \Cake\ORM\Association\BelongsTo $Users
 *
 * @method \App\Model\Entity\UserAttendance get($primaryKey, $options = [])
 * @method \App\Model\Entity\UserAttendance newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\UserAttendance[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\UserAttendance|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\UserAttendance patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\UserAttendance[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\UserAttendance findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class UserAttendancesTable extends Table
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

        $this->table('user_attendances');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');
        //
        //$this->belongsTo('Kiosks', [
        //    'foreignKey' => 'kiosk_id',
        //    'joinType' => 'INNER'
        //]);
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
            ->dateTime('logged_in')
            ->requirePresence('logged_in', 'create')
            ->notEmpty('logged_in');

        $validator
            ->dateTime('logged_out')
            ->requirePresence('logged_out', 'create')
            ->notEmpty('logged_out');

        $validator
            ->requirePresence('session_ide', 'create')
            ->notEmpty('session_ide')
            ->add('session_ide', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->integer('status')
            ->requirePresence('status', 'create')
            ->notEmpty('status');
 

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
        $rules->add($rules->isUnique(['session_ide']));
       // $rules->add($rules->existsIn(['kiosk_id'], 'Kiosks'));
        $rules->add($rules->existsIn(['user_id'], 'Users'));

        return $rules;
    }
}
