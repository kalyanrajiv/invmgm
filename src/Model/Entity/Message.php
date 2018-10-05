<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Message Entity
 *
 * @property int $id
 * @property int $receiver_id
 * @property int $sender_id
 * @property int $user_id
 * @property int $sent_by
 * @property int $sent_to_id
 * @property int $read_by
 * @property int $read_by_user
 * @property string $subject
 * @property string $message
 * @property \Cake\I18n\Time $date
 * @property int $type
 * @property int $receiver_status
 * @property int $sender_status
 * @property int $receiver_read
 * @property int $sender_read
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 *
 * @property \App\Model\Entity\Receiver $receiver
 * @property \App\Model\Entity\Sender $sender
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\SentTo $sent_to
 */
class Message extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        '*' => true,
        'id' => false
    ];
}
