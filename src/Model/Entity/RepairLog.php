<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * RepairLog Entity
 *
 * @property int $id
 * @property int $repair_id
 * @property int $user_id
 * @property int $comments
 * @property int $repair_status
 * @property int $status
 * @property \Cake\I18n\Time $created_at
 * @property \Cake\I18n\Time $updated_at
 *
 * @property \App\Model\Entity\Repair $repair
 * @property \App\Model\Entity\User $user
 */
class RepairLog extends Entity
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
