<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * MobileUnlockLog Entity
 *
 * @property int $id
 * @property int $kiosk_id
 * @property int $unlock_center_id
 * @property int $user_id
 * @property int $mobile_unlock_id
 * @property int $comments
 * @property int $unlock_status
 * @property int $status
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $updated
 *
 * @property \App\Model\Entity\Kiosk $kiosk
 * @property \App\Model\Entity\UnlockCenter $unlock_center
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\MobileUnlock $mobile_unlock
 */
class MobileUnlockLog extends Entity
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
