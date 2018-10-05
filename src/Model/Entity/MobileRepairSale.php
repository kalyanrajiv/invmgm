<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * MobileRepairSale Entity
 *
 * @property int $id
 * @property int $kiosk_id
 * @property int $mobile_repair_id
 * @property int $sold_by
 * @property \Cake\I18n\Time $sold_on
 * @property int $refund_by
 * @property float $amount
 * @property float $refund_amount
 * @property int $refund_status
 * @property \Cake\I18n\Time $refund_on
 * @property string $refund_remarks
 * @property int $status
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 *
 * @property \App\Model\Entity\Kiosk $kiosk
 * @property \App\Model\Entity\MobileRepair $mobile_repair
 * @property \App\Model\Entity\RepairPayment[] $repair_payments
 */
class MobileRepairSale extends Entity
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
