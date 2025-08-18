<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\SalesInvoice;
use App\Models\TaxMaster;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\ArrayToXml\ArrayToXml;
use Illuminate\Support\Facades\Storage;

class TaxReportController extends Controller
{
    public function taxOut()
    {
        return view('transaction.report.tax_out');
    }

    public function searchOut(Request $request)
    {
        $dateFrom = Carbon::parse($request->date_from)->toDateString(); // Ensures YYYY-MM-DD
        $dateTo = Carbon::parse($request->date_to)->toDateString();
        $hasil = DB::select("select s.sales_invoice_number as invoice_number, s.document_date as date, s.customer_code as customer_code, c.customer_name as customer_name, s.subtotal as dpp, s.add_tax as ppn from sales_invoice s join customer c on s.customer_code = c.customer_code where s.department_code = 'DP01' and s.document_date between ? and ? order by 1 asc",[$dateFrom,$dateTo]);
        foreach ($hasil as $h) {
            $h->date = Carbon::parse($h->date)->format('d M Y');
            $h->dpp = number_format($h->dpp,2);
            $h->ppn = number_format($h->ppn,2);
        }
        return $hasil;
    }

    public function taxIn()
    {
        return view('transaction.report.tax_in');
    }

    public function searchIn(Request $request)
    {
        $dateFrom = Carbon::parse($request->date_from)->toDateString(); // Ensures YYYY-MM-DD
        $dateTo = Carbon::parse($request->date_to)->toDateString();
        $hasil = DB::select("select s.purchase_invoice_number as invoice_number, s.document_date as date, s.supplier_code as supplier_code, c.supplier_name as supplier_name, s.subtotal as dpp, s.add_tax as ppn from purchase_invoice s join supplier c on s.supplier_code = c.supplier_code where s.department_code = 'DP01' and s.document_date between ? and ? order by 1 asc",[$dateFrom,$dateTo]);
        foreach ($hasil as $h) {
            $h->date = Carbon::parse($h->date)->format('d M Y');
            $h->dpp = number_format($h->dpp,2);
            $h->ppn = number_format($h->ppn,2);
        }
        return $hasil;
    }

    public function xmlIndex()
    {
        return view('transaction.report.xml_page');
    }

    public function exportXML(Request $request)
    {

        $company = Company::first();
        $invoices = SalesInvoice::
        where('document_date', '>=', $request->date_from)
        ->where('document_date', '<=', $request->date_to)->
        where('department_code','DP01')->
        with('details', 'customers') // Assuming you still want these from your earlier example
        ->orderBy('sales_invoice_number', 'asc')
        ->get();
        $taxs = TaxMaster::where('tax_code', 'PPN')->first();
        $units= [
            'DOS'=>'UM.0022',
            'DOZ'=>'UM.0022',
            'DUS'=>'UM.0022',
            'GRS'=>'UM.0033',
            'KD'=>'UM.0033',
            'KG'=>'UM.0003',
            'LSN'=>'UM.0017',
            'PCS'=>'UM.0021',
            'SAK'=>'UM.0033',
            'ZAK'=>'UM.0033',
            'SET'=>'UM.0019',
            'ZAL'=>'UM.0033',
        ];
        $data = [
            '_attributes' => [
                'xmlns:xsd' => 'http://www.w3.org/2001/XMLSchema',
                'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
            ],
            'TIN' => $company->npwp,
            'ListOfTaxInvoice' => [
                'TaxInvoice' => $invoices->map(function($i) use($company,$units,$taxs){
                    return [
                        'TaxInvoiceDate'=>substr($i->document_date,0,10),
                        'TaxInvoiceOpt'=>'Normal',
                        'TrxCode'=>'05',
                        'AddInfo'=>'',
                        'CustomDoc'=>'',
                        'RefDesc'=>$i->sales_invoice_number,
                        'FacilityStamp'=>'',
                        'SellerIDTKU'=>$company->npwp,
                        'BuyerTin' => ($i->customers->npwp ? $i->customers->npwp : '0000000000000000'),
                        'BuyerDocument'=>($i->customers->npwp ? 'TIN' : ($i->customers->nik ? 'National ID' : 'Other ID')),
                        'BuyerCountry'=>'IDN',
                        'BuyerDocumentNumber'=>($i->customers->npwp ? '-' : ($i->customers->nik ? $i->customers->nik : '-')),
                        'BuyerName'=>$i->customers->customer_name,
                        'BuyerAddress'=>$i->customers->address,
                        'BuyerEmail'=>$i->customers->email??'-',
                        'BuyerIDTKU'=>($i->customers->npwp ? 'TIN' : ($i->customers->nik ? 'National ID' : 'Other ID')),
                        'ListOfGoodService'=> [
                            'GoodService' => $i->details->map(function ($d) use($units,$taxs) {
                                return [
                                    'Opt'=>'A',
                                    'Code'=>$d->item_id,
                                    'Name'=>$d->items->item_name,
                                    'Unit'=>$units[$d->unit]??'UM.0033',
                                    'Price'=>$d->price * $d->base_qty,
                                    'Qty'=>$d->qty,
                                    'TotalDiscount'=>number_format((($d->qty*$d->base_qty&$d->price)*$d->disc_percent + $d->disc_nominal)>0??0,2,'.',''),
                                    'TaxBase'=>number_format($d->nominal,2,'.',''),
                                    'OtherTaxBase'=>number_format($d->nominal * $taxs->tax_base,2,'.',''),
                                    'VATRate'=>number_format($taxs->tariff,0,'',''),
                                    'VAT'=>number_format($d->add_tax_detail,2,'.',''),
                                    'STLGRate'=>0,
                                    'STLG'=>0,
                                ];
                            })->all(),
                        ]

                    ];
                })->all(),
            ],
        ];
        $xml = ArrayToXml::convert($data, [
            'rootElementName' => 'TaxInvoiceBulk',
        ]);

        // return response($xml, 200)->header('Content-Type', 'application/xml');
            // Define the file name
        $fileName = 'export_pajak_keluaran_' . substr($request->dateFrom,0,10).'-'.substr($request->dateTo,0,10) . '.xml';

        // Save the XML to a temporary file in the storage directory
        Storage::put('temp/' . $fileName, $xml);

        // Get the full path to the file
        $filePath = storage_path('app/temp/' . $fileName);

        // Return the file as a download response
        return response()->download($filePath, $fileName)->deleteFileAfterSend(true);
    }
}
