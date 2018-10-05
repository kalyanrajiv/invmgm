<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * ProductReceipt Entity
 *
 * @property int $id
 * @property int $customer_id
 * @property string $fname
 * @property string $lname
 * @property string $email
 * @property string $mobile
 * @property string $address_1
 * @property string $address_2
 * @property string $city
 * @property string $state
 * @property string $zip
 * @property float $orig_bill_amount
 * @property float $bill_amount
 * @property int $bulk_discount
 * @property int $bill_cost
 * @property float $vat
 * @property float $vat_number
 * @property int $processed_by
 * @property int $status
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 *
 * @property \App\Model\Entity\Customer $customer
 * @property \App\Model\Entity\Kiosk10000PaymentDetail[] $kiosk10000_payment_details
 * @property \App\Model\Entity\Kiosk10000ProductSale[] $kiosk10000_product_sales
 * @property \App\Model\Entity\Kiosk10PaymentDetail[] $kiosk10_payment_details
 * @property \App\Model\Entity\Kiosk10ProductSale[] $kiosk10_product_sales
 * @property \App\Model\Entity\Kiosk11PaymentDetail[] $kiosk11_payment_details
 * @property \App\Model\Entity\Kiosk11ProductSale[] $kiosk11_product_sales
 * @property \App\Model\Entity\Kiosk12PaymentDetail[] $kiosk12_payment_details
 * @property \App\Model\Entity\Kiosk12ProductSale[] $kiosk12_product_sales
 * @property \App\Model\Entity\Kiosk13PaymentDetail[] $kiosk13_payment_details
 * @property \App\Model\Entity\Kiosk13ProductSale[] $kiosk13_product_sales
 * @property \App\Model\Entity\Kiosk14PaymentDetail[] $kiosk14_payment_details
 * @property \App\Model\Entity\Kiosk14ProductSale[] $kiosk14_product_sales
 * @property \App\Model\Entity\Kiosk15PaymentDetail[] $kiosk15_payment_details
 * @property \App\Model\Entity\Kiosk15ProductSale[] $kiosk15_product_sales
 * @property \App\Model\Entity\Kiosk16PaymentDetail[] $kiosk16_payment_details
 * @property \App\Model\Entity\Kiosk16ProductSale[] $kiosk16_product_sales
 * @property \App\Model\Entity\Kiosk17PaymentDetail[] $kiosk17_payment_details
 * @property \App\Model\Entity\Kiosk17ProductSale[] $kiosk17_product_sales
 * @property \App\Model\Entity\Kiosk18PaymentDetail[] $kiosk18_payment_details
 * @property \App\Model\Entity\Kiosk18ProductSale[] $kiosk18_product_sales
 * @property \App\Model\Entity\Kiosk19PaymentDetail[] $kiosk19_payment_details
 * @property \App\Model\Entity\Kiosk19ProductSale[] $kiosk19_product_sales
 * @property \App\Model\Entity\Kiosk1PaymentDetail[] $kiosk1_payment_details
 * @property \App\Model\Entity\Kiosk1ProductSale[] $kiosk1_product_sales
 * @property \App\Model\Entity\Kiosk20PaymentDetail[] $kiosk20_payment_details
 * @property \App\Model\Entity\Kiosk20ProductSale[] $kiosk20_product_sales
 * @property \App\Model\Entity\Kiosk21PaymentDetail[] $kiosk21_payment_details
 * @property \App\Model\Entity\Kiosk21ProductSale[] $kiosk21_product_sales
 * @property \App\Model\Entity\Kiosk22PaymentDetail[] $kiosk22_payment_details
 * @property \App\Model\Entity\Kiosk22ProductSale[] $kiosk22_product_sales
 * @property \App\Model\Entity\Kiosk2PaymentDetail[] $kiosk2_payment_details
 * @property \App\Model\Entity\Kiosk2ProductSale[] $kiosk2_product_sales
 * @property \App\Model\Entity\Kiosk3PaymentDetail[] $kiosk3_payment_details
 * @property \App\Model\Entity\Kiosk3ProductSale[] $kiosk3_product_sales
 * @property \App\Model\Entity\Kiosk4PaymentDetail[] $kiosk4_payment_details
 * @property \App\Model\Entity\Kiosk4ProductSale[] $kiosk4_product_sales
 * @property \App\Model\Entity\Kiosk5PaymentDetail[] $kiosk5_payment_details
 * @property \App\Model\Entity\Kiosk5ProductSale[] $kiosk5_product_sales
 * @property \App\Model\Entity\Kiosk7PaymentDetail[] $kiosk7_payment_details
 * @property \App\Model\Entity\Kiosk7ProductSale[] $kiosk7_product_sales
 * @property \App\Model\Entity\Kiosk8PaymentDetail[] $kiosk8_payment_details
 * @property \App\Model\Entity\Kiosk8ProductSale[] $kiosk8_product_sales
 * @property \App\Model\Entity\KioskProductSale[] $kiosk_product_sales
 * @property \App\Model\Entity\PaymentDetail[] $payment_details
 * @property \App\Model\Entity\ProductPayment[] $product_payments
 * @property \App\Model\Entity\TKioskProductSale[] $t_kiosk_product_sales
 * @property \App\Model\Entity\TPaymentDetail[] $t_payment_details
 * @property \App\Model\Entity\TempProductDetail[] $temp_product_details
 * @property \App\Model\Entity\TempProductOrder[] $temp_product_orders
 */
class ProductReceipt extends Entity
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
