<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * MobileRepair Entity
 *
 * @property int $id
 * @property int $repair_number
 * @property int $kiosk_id
 * @property int $retail_customer_id
 * @property int $booked_by
 * @property int $delivered_by
 * @property int $brand_id
 * @property string $mobile_model_id
 * @property string $problem_type
 * @property string $mobile_condition
 * @property string $mobile_condition_remark
 * @property string $function_condition
 * @property string $imei
 * @property \Cake\I18n\Time $received_at
 * @property \Cake\I18n\Time $delivered_at
 * @property string $brief_history
 * @property string $customer_fname
 * @property string $customer_lname
 * @property string $customer_email
 * @property string $customer_contact
 * @property string $customer_address_1
 * @property string $customer_address_2
 * @property string $city
 * @property string $state
 * @property string $country
 * @property string $description
 * @property string $phone_password
 * @property string $estimated_cost
 * @property float $actual_cost
 * @property float $net_cost
 * @property string $zip
 * @property int $status
 * @property int $internal_repair
 * @property int $status_refund
 * @property int $status_rebooked
 * @property int $status_freezed
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 *
 * @property \App\Model\Entity\Kiosk $kiosk
 * @property \App\Model\Entity\RetailCustomer $retail_customer
 * @property \App\Model\Entity\Brand $brand
 * @property \App\Model\Entity\MobileModel $mobile_model
 * @property \App\Model\Entity\CommentMobileRepair[] $comment_mobile_repairs
 * @property \App\Model\Entity\MobileRepairLog[] $mobile_repair_logs
 * @property \App\Model\Entity\MobileRepairPart[] $mobile_repair_parts
 * @property \App\Model\Entity\MobileRepairSale[] $mobile_repair_sales
 * @property \App\Model\Entity\RepairPayment[] $repair_payments
 */
class MobileRepair extends Entity
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
