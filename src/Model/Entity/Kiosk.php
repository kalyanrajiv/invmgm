<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Kiosk Entity
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string $email
 * @property string $communication_password
 * @property string $address_1
 * @property string $address_2
 * @property string $city
 * @property string $state
 * @property string $country
 * @property string $zip
 * @property string $contact
 * @property float $rent
 * @property float $target
 * @property float $monthly_target
 * @property int $target_mon
 * @property int $target_tue
 * @property int $target_wed
 * @property int $target_thu
 * @property int $target_fri
 * @property int $target_sat
 * @property int $target_sun
 * @property float $target_achieved
 * @property int $contract_type
 * @property \Cake\I18n\Time $agreement_from
 * @property \Cake\I18n\Time $agreement_to
 * @property string $break_clause
 * @property int $renewal_weeks
 * @property int $status
 * @property int $renewal_months
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 * @property int $kiosk_type
 *
 * @property \App\Model\Entity\CenterOrder[] $center_orders
 * @property \App\Model\Entity\CreditProductDetail[] $credit_product_details
 * @property \App\Model\Entity\Customer[] $customers
 * @property \App\Model\Entity\DailyTarget[] $daily_targets
 * @property \App\Model\Entity\DeadProduct[] $dead_products
 * @property \App\Model\Entity\DefectiveBinReference[] $defective_bin_references
 * @property \App\Model\Entity\DefectiveBinTransient[] $defective_bin_transients
 * @property \App\Model\Entity\DefectiveBin[] $defective_bins
 * @property \App\Model\Entity\DefectiveKioskProduct[] $defective_kiosk_products
 * @property \App\Model\Entity\DefectiveKioskReference[] $defective_kiosk_references
 * @property \App\Model\Entity\DefectiveKioskTransient[] $defective_kiosk_transients
 * @property \App\Model\Entity\FaultyProduct[] $faulty_products
 * @property \App\Model\Entity\FaultyReturnedProduct[] $faulty_returned_products
 * @property \App\Model\Entity\InvoiceOrderDetail[] $invoice_order_details
 * @property \App\Model\Entity\InvoiceOrder[] $invoice_orders
 * @property \App\Model\Entity\Kiosk10000ProductSale[] $kiosk10000_product_sales
 * @property \App\Model\Entity\Kiosk10CreditProductDetail[] $kiosk10_credit_product_details
 * @property \App\Model\Entity\Kiosk10InvoiceOrderDetail[] $kiosk10_invoice_order_details
 * @property \App\Model\Entity\Kiosk10InvoiceOrder[] $kiosk10_invoice_orders
 * @property \App\Model\Entity\Kiosk10ProductSale[] $kiosk10_product_sales
 * @property \App\Model\Entity\Kiosk11CreditProductDetail[] $kiosk11_credit_product_details
 * @property \App\Model\Entity\Kiosk11InvoiceOrderDetail[] $kiosk11_invoice_order_details
 * @property \App\Model\Entity\Kiosk11InvoiceOrder[] $kiosk11_invoice_orders
 * @property \App\Model\Entity\Kiosk11ProductSale[] $kiosk11_product_sales
 * @property \App\Model\Entity\Kiosk12ProductSale[] $kiosk12_product_sales
 * @property \App\Model\Entity\Kiosk13ProductSale[] $kiosk13_product_sales
 * @property \App\Model\Entity\Kiosk14ProductSale[] $kiosk14_product_sales
 * @property \App\Model\Entity\Kiosk15ProductSale[] $kiosk15_product_sales
 * @property \App\Model\Entity\Kiosk16ProductSale[] $kiosk16_product_sales
 * @property \App\Model\Entity\Kiosk17ProductSale[] $kiosk17_product_sales
 * @property \App\Model\Entity\Kiosk18ProductSale[] $kiosk18_product_sales
 * @property \App\Model\Entity\Kiosk19CreditProductDetail[] $kiosk19_credit_product_details
 * @property \App\Model\Entity\Kiosk19InvoiceOrderDetail[] $kiosk19_invoice_order_details
 * @property \App\Model\Entity\Kiosk19InvoiceOrder[] $kiosk19_invoice_orders
 * @property \App\Model\Entity\Kiosk19ProductSale[] $kiosk19_product_sales
 * @property \App\Model\Entity\Kiosk1CreditProductDetail[] $kiosk1_credit_product_details
 * @property \App\Model\Entity\Kiosk1ProductSale[] $kiosk1_product_sales
 * @property \App\Model\Entity\Kiosk20CreditProductDetail[] $kiosk20_credit_product_details
 * @property \App\Model\Entity\Kiosk20InvoiceOrderDetail[] $kiosk20_invoice_order_details
 * @property \App\Model\Entity\Kiosk20InvoiceOrder[] $kiosk20_invoice_orders
 * @property \App\Model\Entity\Kiosk20ProductSale[] $kiosk20_product_sales
 * @property \App\Model\Entity\Kiosk21CreditProductDetail[] $kiosk21_credit_product_details
 * @property \App\Model\Entity\Kiosk21InvoiceOrderDetail[] $kiosk21_invoice_order_details
 * @property \App\Model\Entity\Kiosk21InvoiceOrder[] $kiosk21_invoice_orders
 * @property \App\Model\Entity\Kiosk21ProductSale[] $kiosk21_product_sales
 * @property \App\Model\Entity\Kiosk22ProductSale[] $kiosk22_product_sales
 * @property \App\Model\Entity\Kiosk2ProductSale[] $kiosk2_product_sales
 * @property \App\Model\Entity\Kiosk3ProductSale[] $kiosk3_product_sales
 * @property \App\Model\Entity\Kiosk4ProductSale[] $kiosk4_product_sales
 * @property \App\Model\Entity\Kiosk5CreditProductDetail[] $kiosk5_credit_product_details
 * @property \App\Model\Entity\Kiosk5InvoiceOrderDetail[] $kiosk5_invoice_order_details
 * @property \App\Model\Entity\Kiosk5InvoiceOrder[] $kiosk5_invoice_orders
 * @property \App\Model\Entity\Kiosk5ProductSale[] $kiosk5_product_sales
 * @property \App\Model\Entity\Kiosk7CreditProductDetail[] $kiosk7_credit_product_details
 * @property \App\Model\Entity\Kiosk7InvoiceOrderDetail[] $kiosk7_invoice_order_details
 * @property \App\Model\Entity\Kiosk7InvoiceOrder[] $kiosk7_invoice_orders
 * @property \App\Model\Entity\Kiosk7ProductSale[] $kiosk7_product_sales
 * @property \App\Model\Entity\Kiosk8CreditProductDetail[] $kiosk8_credit_product_details
 * @property \App\Model\Entity\Kiosk8InvoiceOrderDetail[] $kiosk8_invoice_order_details
 * @property \App\Model\Entity\Kiosk8InvoiceOrder[] $kiosk8_invoice_orders
 * @property \App\Model\Entity\Kiosk8ProductSale[] $kiosk8_product_sales
 * @property \App\Model\Entity\KioskFaultyProductDetail[] $kiosk_faulty_product_details
 * @property \App\Model\Entity\KioskProductSale[] $kiosk_product_sales
 * @property \App\Model\Entity\KioskCancelledOrderProduct[] $kiosk_cancelled_order_products
 * @property \App\Model\Entity\KioskOrderProduct[] $kiosk_order_products
 * @property \App\Model\Entity\KioskOrder[] $kiosk_orders
 * @property \App\Model\Entity\KioskPlacedOrder[] $kiosk_placed_orders
 * @property \App\Model\Entity\KioskTiming[] $kiosk_timing
 * @property \App\Model\Entity\KioskTiming[] $kiosk_timings
 * @property \App\Model\Entity\MobileBlkReSalePayment[] $mobile_blk_re_sale_payments
 * @property \App\Model\Entity\MobileBlkReSale[] $mobile_blk_re_sales
 * @property \App\Model\Entity\MobilePayment[] $mobile_payments
 * @property \App\Model\Entity\MobilePlacedOrder[] $mobile_placed_orders
 * @property \App\Model\Entity\MobilePurchase[] $mobile_purchases
 * @property \App\Model\Entity\MobileReSalePayment[] $mobile_re_sale_payments
 * @property \App\Model\Entity\MobileReSale[] $mobile_re_sales
 * @property \App\Model\Entity\MobileRepairLog[] $mobile_repair_logs
 * @property \App\Model\Entity\MobileRepairPart[] $mobile_repair_parts
 * @property \App\Model\Entity\MobileRepairSale[] $mobile_repair_sales
 * @property \App\Model\Entity\MobileRepair[] $mobile_repairs
 * @property \App\Model\Entity\MobileTransferLog[] $mobile_transfer_logs
 * @property \App\Model\Entity\MobileUnlockLog[] $mobile_unlock_logs
 * @property \App\Model\Entity\MobileUnlockSale[] $mobile_unlock_sales
 * @property \App\Model\Entity\MobileUnlock[] $mobile_unlocks
 * @property \App\Model\Entity\OnDemandOrder[] $on_demand_orders
 * @property \App\Model\Entity\OnDemandProduct[] $on_demand_products
 * @property \App\Model\Entity\OrderDispute[] $order_disputes
 * @property \App\Model\Entity\ProductPayment[] $product_payments
 * @property \App\Model\Entity\ReorderLevel[] $reorder_levels
 * @property \App\Model\Entity\RepairPayment[] $repair_payments
 * @property \App\Model\Entity\RetailCustomer[] $retail_customers
 * @property \App\Model\Entity\RevertStock[] $revert_stocks
 * @property \App\Model\Entity\SessionBackup[] $session_backups
 * @property \App\Model\Entity\StockTakingDetail[] $stock_taking_details
 * @property \App\Model\Entity\StockTakingReference[] $stock_taking_references
 * @property \App\Model\Entity\TKioskProductSale[] $t_kiosk_product_sales
 * @property \App\Model\Entity\TempProductDetail[] $temp_product_details
 * @property \App\Model\Entity\TempProductOrder[] $temp_product_orders
 * @property \App\Model\Entity\UnderstockLevelOrder[] $understock_level_orders
 * @property \App\Model\Entity\UnlockPayment[] $unlock_payments
 * @property \App\Model\Entity\UserAttendance[] $user_attendances
 */
class Kiosk extends Entity
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
