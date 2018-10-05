<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Messages Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Receivers
 * @property \Cake\ORM\Association\BelongsTo $Senders
 * @property \Cake\ORM\Association\BelongsTo $Users
 * @property \Cake\ORM\Association\BelongsTo $SentTos
 *
 * @method \App\Model\Entity\Message get($primaryKey, $options = [])
 * @method \App\Model\Entity\Message newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Message[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Message|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Message patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Message[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Message findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class MessagesTable extends Table
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

        $this->table('messages');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

        //$this->belongsTo('Receivers', [
        //    'foreignKey' => 'receiver_id'
        //]);
        //$this->belongsTo('Senders', [
        //    'foreignKey' => 'sender_id'
        //]);
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id'
        ]);
        //$this->belongsTo('SentTos', [
        //    'foreignKey' => 'sent_to_id'
        //]);
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
            ->integer('sent_by')
            ->allowEmpty('sent_by');

        $validator
            ->integer('read_by')
            ->allowEmpty('read_by');

        $validator
            ->integer('read_by_user')
            ->allowEmpty('read_by_user');
        // $validator->add('subject',[
        //'notEmptyCheck'=>[
        //'rule'=>'notEmptyCheck',
        //'provider'=>'table',
        //'message'=>'Please enter the Subject'
        // ]
        //]);
        $validator
            ->requirePresence('subject', 'create')
            ->notEmpty('subject');

        $validator
            ->requirePresence('message', 'create')
            ->notEmpty('message');

        //$validator
        //    ->dateTime('date')
        //    ->requirePresence('date', 'create')
        //    ->notEmpty('date');

        $validator
            ->integer('type')
            ->requirePresence('type', 'create')
            ->notEmpty('type');

        //$validator
        //    ->integer('receiver_status')
        //    ->requirePresence('receiver_status', 'create')
        //    ->notEmpty('receiver_status');

        $validator
            ->integer('sender_status')
            ->requirePresence('sender_status', 'create')
            ->notEmpty('sender_status');

        //$validator
        //    ->integer('receiver_read')
        //    ->requirePresence('receiver_read', 'create')
        //    ->notEmpty('receiver_read');

        //$validator
        //    ->integer('sender_read')
        //    ->requirePresence('sender_read', 'create')
        //    ->notEmpty('sender_read');

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
       // $rules->add($rules->existsIn(['receiver_id'], 'Receivers'));
        //$rules->add($rules->existsIn(['sender_id'], 'Senders'));
        $rules->add($rules->existsIn(['user_id'], 'Users'));
        //$rules->add($rules->existsIn(['sent_to_id'], 'SentTos'));

        return $rules;
    }
}
