<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * MobileBlkTransferLog Entity
 *
 * @property int $id
 * @property string $mobile_purchase_reference
 * @property int $mobile_purchase_id
 * @property int $user_id
 * @property int $kiosk_id
 * @property int $network_id
 * @property int $grade
 * @property int $type
 * @property int $mobile_resale_id
 * @property int $receiving_status
 * @property string $imei
 * @property int $status
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 *
 * @property \App\Model\Entity\MobilePurchase $mobile_purchase
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\Kiosk $kiosk
 * @property \App\Model\Entity\Network $network
 * @property \App\Model\Entity\MobileResale $mobile_resale
 */
class MobileBlkTransferLog extends Entity
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
