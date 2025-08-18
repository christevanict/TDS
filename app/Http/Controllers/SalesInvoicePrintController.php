<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Item;
use App\Models\SalesInvoice;
use App\Models\SalesOrder;
use App\Models\TaxMaster;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SalesInvoicePrintController extends Controller
{
    function numberToWords($number) {
        $number = floor($number);
        $words = [
            '', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'
        ];

        if ($number < 12) {
            return $words[$number];
        } else if ($number < 20) {
            return $words[$number - 10] . ' belas';
        } else if ($number < 100) {
            $result = $words[floor($number / 10)] . ' puluh ' . $words[$number % 10];
        } else if ($number < 200) {
            $result = 'seratus ' . $this->numberToWords($number - 100);
        } else if ($number < 1000) {
            $result = $words[floor($number / 100)] . ' ratus ' . $this->numberToWords($number % 100);
        } else if ($number < 2000) {
            $result = 'seribu ' . $this->numberToWords($number - 1000);
        } else if ($number < 1000000) {
            $result = $this->numberToWords(floor($number / 1000)) . ' ribu ' . $this->numberToWords($number % 1000);
        } else if ($number < 1000000000) {
            $result = $this->numberToWords(floor($number / 1000000)) . ' juta ' . $this->numberToWords($number % 1000000);
        } else if ($number < 1000000000000) {
            $result = $this->numberToWords(floor($number / 1000000000)) . ' milyar ' . $this->numberToWords($number % 1000000000);
        } else if ($number < 1000000000000000) {
            $result = $this->numberToWords(floor($number / 1000000000000)) . ' triliun ' . $this->numberToWords($number % 1000000000000);
        } else {
            return 'Jumlah terlalu besar';
        }

        // Remove double spaces and trim
        return trim(preg_replace('/\s+/', ' ', $result));
    }

    public function printWebservice($id){
        $nominal = 0;
        $revenueTax = 0;
        $addTax = 0;
        $taxed=0;
        $services=0;
        $totalPriceBeforeTaxBeforeDiscount = 0;
        $totalPriceBeforeTaxAfterDiscount = 0;
        $totalAllAfterDiscountBeforeTax = 0;
        $totalAllItemBeforeTax = 0;
        $totalAllDiscountDetail = 0;

        // Retrieve the sales invoice with related data
        $salesInvoice = SalesInvoice::with([
            'company',
            'department',
            'customers',
            'details' => function ($query) {
                $query->orderBy('id', 'asc');
            },
            'details.items.itemDetails.unitConversion', // Nested relationships still load
        ])->findOrFail($id);
        // dd($salesInvoice);

        // Group the invoice details by item_id (if needed)
        $groupedDetails = $salesInvoice->details->groupBy('item_id');

        // Generate and return the PDF
        $totalHuruf = ucfirst($this->numberToWords($salesInvoice->total)).' rupiah';
        $tax = TaxMaster::where('tax_code','PPN')->first();
        
        $ret = [];
        $ret["customer_name"] = $salesInvoice->customers->customer_name;
        $ret["city"] = $salesInvoice->customers->city;
        $ret["document_date"] = Carbon::parse($salesInvoice->document_date)->format('d M Y');
        $ret["sales_invoice_number"] = $salesInvoice->sales_invoice_number;
        $ret["terbilangLines"] = $totalHuruf;
        $ret["subtotal"] = number_format($salesInvoice->subtotal, 0, ',', '.');
        $ret["disc_nominal"] = number_format($salesInvoice->disc_nominal, 0, ',', '.');
        $ret["add_tax"] = number_format($salesInvoice->add_tax, 0, ',', '.');
        $ret["total"] = number_format($salesInvoice->total, 0, ',', '.');
        
        $det = [];$ctr = 1;$totalDisc = 0;
        foreach($salesInvoice->details as $index => $d){
            $itemTax = Item::where('department_code','DP01')->where('item_code', $d->item_id)->first();

            if($salesInvoice->customers->pkp == 1 && strtolower($itemTax->category->item_category_name) != 'service') {
                if($salesInvoice->customers->include == 1) {
                    if ($itemTax->additional_tax == 1 ) {
                        $totalAllAfterDiscountBeforeTax += (($d->qty*$d->price) / (1 + $taxs->tariff / 100)) - ($d->disc_percent/100*(($d->qty*$d->price) / (1 + $taxs->tariff / 100)))-$d->disc_nominal;
                    } else {
                        $totalAllAfterDiscountBeforeTax += $d->qty*$d->price - ($d->disc_percent/100*(($d->qty*$d->price)))-$d->disc_nominal;
                    }
                } else {
                    $totalAllAfterDiscountBeforeTax += $d->qty*$d->price;
                }
            }else{
                $totalAllAfterDiscountBeforeTax += $d->qty*$d->price - ($d->disc_percent/100*(($d->qty*$d->price)))-$d->disc_nominal;
            }
            
            if ($salesInvoice->customers->pkp == 1) {
                if (strtolower($itemTax->category->item_category_name) == 'service') {
                    $services += $d->nominal;
                }
                if($salesInvoice->customers->include == 1) {
                    if ($itemTax->additional_tax == 1) {
                        $totalPriceBeforeTaxBeforeDiscount = ($d->qty*$d->price)/(1 + $taxs->tariff / 100)*$d->base_qty;
                        $totalPriceBeforeTaxAfterDiscount = $totalPriceBeforeTaxBeforeDiscount  - (($d->disc_percent/100*$totalPriceBeforeTaxBeforeDiscount)+$d->disc_nominal);
                        $discPerDetail = $totalPriceBeforeTaxAfterDiscount/$totalAllAfterDiscountBeforeTax * $salesInvoice->disc_nominal;
                        $totalPriceBeforeTaxAfterDiscount -=$discPerDetail;
                        $totalDiscountPerDetail =  (($d->disc_percent/100*$totalPriceBeforeTaxBeforeDiscount)+$d->disc_nominal) + $discPerDetail;
                        $totalAllDiscountDetail +=($d->disc_percent/100*$totalPriceBeforeTaxBeforeDiscount)+$d->disc_nominal;
                        $taxed += $totalPriceBeforeTaxBeforeDiscount;
                    }else{
                        $totalPriceBeforeTaxBeforeDiscount = $d->qty*$d->price*$d->base_qty;
                        $totalPriceBeforeTaxAfterDiscount = $totalPriceBeforeTaxBeforeDiscount  - (($d->disc_percent/100*$totalPriceBeforeTaxBeforeDiscount)+$d->disc_nominal);
                        $discPerDetail = $totalPriceBeforeTaxAfterDiscount/$totalAllAfterDiscountBeforeTax * $salesInvoice->disc_nominal;
                        $totalPriceBeforeTaxAfterDiscount -=$discPerDetail;
                        $totalDiscountPerDetail =  (($d->disc_percent/100*$totalPriceBeforeTaxBeforeDiscount)+$d->disc_nominal) + $discPerDetail;
                        $totalAllDiscountDetail +=($d->disc_percent/100*$totalPriceBeforeTaxBeforeDiscount)+$d->disc_nominal;
                    }
                } else {
                    if ($itemTax->additional_tax == 1) {
                        $totalPriceBeforeTaxBeforeDiscount = $d->qty*$d->price*$d->conversion_value;
                        $totalPriceBeforeTaxAfterDiscount = $totalPriceBeforeTaxBeforeDiscount  - (($d->disc_percent/100*$totalPriceBeforeTaxBeforeDiscount)+$d->disc_nominal);
                        $discPerDetail = $totalPriceBeforeTaxAfterDiscount/$totalAllAfterDiscountBeforeTax * $salesInvoice->disc_nominal;
                        $totalPriceBeforeTaxAfterDiscount -=$discPerDetail;
                        $totalDiscountPerDetail =  (($d->disc_percent/100*$totalPriceBeforeTaxBeforeDiscount)+$d->disc_nominal) + $discPerDetail;
                        $totalAllDiscountDetail +=($d->disc_percent/100*$totalPriceBeforeTaxBeforeDiscount)+$d->disc_nominal;
                        $taxed += $totalPriceBeforeTaxBeforeDiscount;
                    }else{
                        $totalPriceBeforeTaxBeforeDiscount = $d->qty*$d->price*$d->conversion_value;
                        $totalPriceBeforeTaxAfterDiscount = $totalPriceBeforeTaxBeforeDiscount  - (($d->disc_percent/100*$totalPriceBeforeTaxBeforeDiscount)+$d->disc_nominal);
                        $discPerDetail = $totalPriceBeforeTaxAfterDiscount/$totalAllAfterDiscountBeforeTax * $salesInvoice->disc_nominal;
                        $totalPriceBeforeTaxAfterDiscount -=$discPerDetail;
                        $totalDiscountPerDetail =  (($d->disc_percent/100*$totalPriceBeforeTaxBeforeDiscount)+$d->disc_nominal) + $discPerDetail;
                        $totalAllDiscountDetail +=($d->disc_percent/100*$totalPriceBeforeTaxBeforeDiscount)+$d->disc_nominal;
                    }
                }
            } else {
                $revenueTax = 0;
                $addTax = 0;
                $totalPriceBeforeTaxBeforeDiscount = $d->qty*$d->price*$d->conversion_value;
                $totalPriceBeforeTaxAfterDiscount = $totalPriceBeforeTaxBeforeDiscount  - (($d->disc_percent/100*$totalPriceBeforeTaxBeforeDiscount)+$d->disc_nominal);
                $totalPriceBeforeTaxBeforeDiscount  - (($d->disc_percent/100*$totalPriceBeforeTaxBeforeDiscount)+$d->disc_nominal);
                $discPerDetail = $totalPriceBeforeTaxAfterDiscount/$totalAllAfterDiscountBeforeTax * $salesInvoice->disc_nominal;
                $totalPriceBeforeTaxAfterDiscount -=$discPerDetail;
                $totalDiscountPerDetail =  (($d->disc_percent/100*$totalPriceBeforeTaxBeforeDiscount)+$d->disc_nominal) + $discPerDetail;
                $totalAllDiscountDetail +=($d->disc_percent/100*$totalPriceBeforeTaxBeforeDiscount)+$d->disc_nominal;
            }
            $totalDisc += $totalDiscountPerDetail;

            $det[] = [
                "iteration" => $ctr.".",
                "item_name" => $d->items->item_name,
                "qty" => number_format($d->qty, 0),
                "unit_name" => $d->baseUnit->unit_name,
                "base_qty" => number_format($d->base_qty * $d->qty, 0, ',', '.'),
                "base_unit_name" => $d->baseUnit->unit_name,
                "price" => number_format($d->price, 0, ',', '.'),
                "nominal" => number_format($d->price * $d->qty, 0, ',', '.')
            ];
            $ctr++;
        }
        $ret["disc_nominal"] = number_format($totalDisc, 0, ',', '.');
        $ret["detail"] = $det;
        
        return response()->json($ret);
    }

    public function printNettoWebservice($id){
        $nominal = 0;
        $revenueTax = 0;
        $addTax = 0;
        $taxed=0;
        $services=0;
        $totalPriceBeforeTaxBeforeDiscount = 0;
        $totalPriceBeforeTaxAfterDiscount = 0;
        $totalAllAfterDiscountBeforeTax = 0;
        $totalAllItemBeforeTax = 0;
        $totalAllDiscountDetail = 0;

        $salesInvoice = SalesInvoice::with([
            'company',
            'department',
            'customers',
            'details' => function ($query) {
                $query->orderBy('id', 'asc');
            },
            'details.items.itemDetails.unitConversion', // Nested relationships still load
        ])->findOrFail($id);
        // dd($salesInvoice);
        $soNumber = explode(",",$salesInvoice->details[0]->sales_order_number)[0];
        if( SalesOrder::where('sales_order_number',$soNumber)->first()){
            $customerOrigin = SalesOrder::where('sales_order_number',$soNumber)->first()->customers;
        }else{
            $customerOrigin = Customer::where('customer_code',$salesInvoice->customers->group_customer)->first();
            if(!$customerOrigin){
                $customerOrigin = $salesInvoice->customers;
            }
        }

        // Group the invoice details by item_id (if needed)
        $groupedDetails = $salesInvoice->details->groupBy('item_id');
        $tax = TaxMaster::where('tax_code','PPN')->first();

        $totalHuruf = ucfirst($this->numberToWords($salesInvoice->total)).' rupiah';

        $ret = [];
        $ret["customer_name"] = $customerOrigin->customer_name;
        $ret["city"] = $customerOrigin->city;
        $ret["document_date"] = Carbon::parse($salesInvoice->document_date)->format('d M Y');
        $ret["sales_invoice_number"] = $salesInvoice->sales_invoice_number;
        $ret["terbilangLines"] = $totalHuruf;
        $ret["subtotal"] = number_format($salesInvoice->subtotal+$salesInvoice->add_tax, 0, ',', '.');
        $ret["disc_nominal"] = number_format($salesInvoice->disc_nominal, 0, ',', '.');
        $ret["add_tax"] = number_format($salesInvoice->add_tax, 0, ',', '.');
        $ret["total"] = number_format($salesInvoice->total, 0, ',', '.');
        
        $det = [];$ctr = 1;$totalDisc = 0;
        foreach($salesInvoice->details as $index => $d){
            $itemTax = Item::where('department_code','DP01')->where('item_code', $d->item_id)->first();

            if($salesInvoice->customers->pkp == 1 && strtolower($itemTax->category->item_category_name) != 'service') {
                if($salesInvoice->customers->include == 1) {
                    if ($itemTax->additional_tax == 1 ) {
                        $totalAllAfterDiscountBeforeTax += (($d->qty*$d->price) / (1 + $taxs->tariff / 100)) - ($d->disc_percent/100*(($d->qty*$d->price) / (1 + $taxs->tariff / 100)))-$d->disc_nominal;
                    } else {
                        $totalAllAfterDiscountBeforeTax += $d->qty*$d->price - ($d->disc_percent/100*(($d->qty*$d->price)))-$d->disc_nominal;
                    }
                } else {
                    $totalAllAfterDiscountBeforeTax += $d->qty*$d->price;
                }
            }else{
                $totalAllAfterDiscountBeforeTax += $d->qty*$d->price - ($d->disc_percent/100*(($d->qty*$d->price)))-$d->disc_nominal;
            }
            
            if ($salesInvoice->customers->pkp == 1) {
                if (strtolower($itemTax->category->item_category_name) == 'service') {
                    $services += $d->nominal;
                }
                if($salesInvoice->customers->include == 1) {
                    if ($itemTax->additional_tax == 1) {
                        $totalPriceBeforeTaxBeforeDiscount = ($d->qty*$d->price)/(1 + $taxs->tariff / 100)*$d->base_qty;
                        $totalPriceBeforeTaxAfterDiscount = $totalPriceBeforeTaxBeforeDiscount  - (($d->disc_percent/100*$totalPriceBeforeTaxBeforeDiscount)+$d->disc_nominal);
                        $discPerDetail = $totalPriceBeforeTaxAfterDiscount/$totalAllAfterDiscountBeforeTax * $salesInvoice->disc_nominal;
                        $totalPriceBeforeTaxAfterDiscount -=$discPerDetail;
                        $totalDiscountPerDetail =  (($d->disc_percent/100*$totalPriceBeforeTaxBeforeDiscount)+$d->disc_nominal) + $discPerDetail;
                        $totalAllDiscountDetail +=($d->disc_percent/100*$totalPriceBeforeTaxBeforeDiscount)+$d->disc_nominal;
                        $taxed += $totalPriceBeforeTaxBeforeDiscount;
                    }else{
                        $totalPriceBeforeTaxBeforeDiscount = $d->qty*$d->price*$d->base_qty;
                        $totalPriceBeforeTaxAfterDiscount = $totalPriceBeforeTaxBeforeDiscount  - (($d->disc_percent/100*$totalPriceBeforeTaxBeforeDiscount)+$d->disc_nominal);
                        $discPerDetail = $totalPriceBeforeTaxAfterDiscount/$totalAllAfterDiscountBeforeTax * $salesInvoice->disc_nominal;
                        $totalPriceBeforeTaxAfterDiscount -=$discPerDetail;
                        $totalDiscountPerDetail =  (($d->disc_percent/100*$totalPriceBeforeTaxBeforeDiscount)+$d->disc_nominal) + $discPerDetail;
                        $totalAllDiscountDetail +=($d->disc_percent/100*$totalPriceBeforeTaxBeforeDiscount)+$d->disc_nominal;
                    }
                } else {
                    if ($itemTax->additional_tax == 1) {
                        $totalPriceBeforeTaxBeforeDiscount = $d->qty*$d->price*$d->conversion_value;
                        $totalPriceBeforeTaxAfterDiscount = $totalPriceBeforeTaxBeforeDiscount  - (($d->disc_percent/100*$totalPriceBeforeTaxBeforeDiscount)+$d->disc_nominal);
                        $discPerDetail = $totalPriceBeforeTaxAfterDiscount/$totalAllAfterDiscountBeforeTax * $salesInvoice->disc_nominal;
                        $totalPriceBeforeTaxAfterDiscount -=$discPerDetail;
                        $totalDiscountPerDetail =  (($d->disc_percent/100*$totalPriceBeforeTaxBeforeDiscount)+$d->disc_nominal) + $discPerDetail;
                        $totalAllDiscountDetail +=($d->disc_percent/100*$totalPriceBeforeTaxBeforeDiscount)+$d->disc_nominal;
                        $taxed += $totalPriceBeforeTaxBeforeDiscount;
                    }else{
                        $totalPriceBeforeTaxBeforeDiscount = $d->qty*$d->price*$d->conversion_value;
                        $totalPriceBeforeTaxAfterDiscount = $totalPriceBeforeTaxBeforeDiscount  - (($d->disc_percent/100*$totalPriceBeforeTaxBeforeDiscount)+$d->disc_nominal);
                        $discPerDetail = $totalPriceBeforeTaxAfterDiscount/$totalAllAfterDiscountBeforeTax * $salesInvoice->disc_nominal;
                        $totalPriceBeforeTaxAfterDiscount -=$discPerDetail;
                        $totalDiscountPerDetail =  (($d->disc_percent/100*$totalPriceBeforeTaxBeforeDiscount)+$d->disc_nominal) + $discPerDetail;
                        $totalAllDiscountDetail +=($d->disc_percent/100*$totalPriceBeforeTaxBeforeDiscount)+$d->disc_nominal;
                    }
                }
            } else {
                $revenueTax = 0;
                $addTax = 0;
                $totalPriceBeforeTaxBeforeDiscount = $d->qty*$d->price*$d->conversion_value;
                $totalPriceBeforeTaxAfterDiscount = $totalPriceBeforeTaxBeforeDiscount  - (($d->disc_percent/100*$totalPriceBeforeTaxBeforeDiscount)+$d->disc_nominal);
                $totalPriceBeforeTaxBeforeDiscount  - (($d->disc_percent/100*$totalPriceBeforeTaxBeforeDiscount)+$d->disc_nominal);
                $discPerDetail = $totalPriceBeforeTaxAfterDiscount/$totalAllAfterDiscountBeforeTax * $salesInvoice->disc_nominal;
                $totalPriceBeforeTaxAfterDiscount -=$discPerDetail;
                $totalDiscountPerDetail =  (($d->disc_percent/100*$totalPriceBeforeTaxBeforeDiscount)+$d->disc_nominal) + $discPerDetail;
                $totalAllDiscountDetail +=($d->disc_percent/100*$totalPriceBeforeTaxBeforeDiscount)+$d->disc_nominal;
            }
            $totalDisc += $totalDiscountPerDetail;

            $det[] = [
                "iteration" => $ctr.".",
                "item_name" => $d->items->item_name,
                "qty" => number_format($d->qty, 0),
                "unit_name" => $d->baseUnit->unit_name,
                "base_qty" => number_format($d->base_qty * $d->qty, 0, ',', '.'),
                "base_unit_name" => $d->baseUnit->unit_name,
                "price" => $d->items->additional_tax ? number_format($d->price + ($d->price*$tax->tax_base*$tax->tariff/100), 0,',','.') : number_format($d->price, 0,',','.'),
                "nominal" => $d->items->additional_tax? number_format($d->nominal + ($d->price*$d->qty*$d->base_qty*$tax->tax_base*$tax->tariff/100), 0,',','.'):number_format($d->nominal, 0,',','.')
            ];
            $ctr++;
        }
        $ret["disc_nominal"] = number_format($totalDisc, 0, ',', '.');
        $ret["detail"] = $det;
        
        return response()->json($ret);
    }

    public function printDOWebservice($id){
        

        // Retrieve the sales invoice with related data
        $salesInvoice = SalesInvoice::with([
            'company',
            'department',
            'customers',
            'details' => function ($query) {
                $query->orderBy('id', 'asc');
            },
            'details.items.itemDetails.unitConversion', // Nested relationships still load
        ])->findOrFail($id);
        // dd($salesInvoice);

        // Group the invoice details by item_id (if needed)
        $groupedDetails = $salesInvoice->details->groupBy('item_id');

        // Generate and return the PDF
        $totalHuruf = ucfirst($this->numberToWords($salesInvoice->total)).' rupiah';
        $tax = TaxMaster::where('tax_code','PPN')->first();
        
        $ret = [];
        $ret["customer_name"] = $salesInvoice->customers->customer_name;
        $ret["city"] = $salesInvoice->customers->city;
        $ret["document_date"] = Carbon::parse($salesInvoice->document_date)->format('d M Y');
        $ret["sales_invoice_number"] = $salesInvoice->sales_invoice_number;
        $ret["terbilangLines"] = $totalHuruf;
        $ret["subtotal"] = number_format($salesInvoice->subtotal, 0, ',', '.');
        $ret["disc_nominal"] = number_format($salesInvoice->disc_nominal, 0, ',', '.');
        $ret["add_tax"] = number_format($salesInvoice->add_tax, 0, ',', '.');
        $ret["total"] = number_format($salesInvoice->total, 0, ',', '.');
        
        $det = [];$ctr = 1;$totalDisc = 0;$qtys = 0;
        foreach($salesInvoice->details as $index => $d){
            $qtys+=$d->qty;

            $det[] = [
                "iteration" => $ctr.".",
                "item_name" => $d->items->item_name,
                "qty" => number_format($d->qty, 0),
                "unit_name" => $d->baseUnit->unit_name,
                "base_qty" => number_format($d->base_qty * $d->qty, 0, ',', '.'),
                "base_unit_name" => $d->baseUnit->unit_name,
                "price" => number_format($d->price, 0, ',', '.'),
                "nominal" => number_format($d->price * $d->qty, 0, ',', '.')
            ];
            $ctr++;
        }
        $ret["disc_nominal"] = number_format($totalDisc, 0, ',', '.');
        $ret["qtys"] = number_format($qtys, 0, ',', '.');
        $ret["detail"] = $det;
        
        return response()->json($ret);
    }
}