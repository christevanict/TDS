<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\SalesOrderController;
use App\Mail\TestEmail;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Mail;



/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect('/TDS/auth/login');
});

Route::prefix('TDS')->group(function () {
    Route::get('/', function () {
        return redirect('/TDS/auth/login');
    });

    Route::post('/hpp', [App\Http\Controllers\SystemController::class, 'hpp'])->name("hpp");
    Route::post('/getStockByDate', [App\Http\Controllers\SystemController::class, 'getStockByDate'])->name("getStockByDate");
    Route::post('/checkUnit', [App\Http\Controllers\SystemController::class, 'checkUnit'])->name("checkUnit");
    Route::post('/getStockByDatePerItem', [App\Http\Controllers\SystemController::class, 'getStockByDatePerItem'])->name("getStockByDatePerItem");
    Route::post('/checkDateToPeriode', [App\Http\Controllers\SystemController::class, 'checkDateToPeriode'])->name("checkDateToPeriode");


    //print
    Route::prefix('print')->group(function () {
        Route::get('sales-order/{id}', [App\Http\Controllers\SalesOrderPrintController::class, 'printWebservice']);
        Route::get('sales-order-netto/{id}', [App\Http\Controllers\SalesOrderPrintController::class, 'printNettoWebservice']);
        Route::get('sales-invoice/{id}', [App\Http\Controllers\SalesInvoicePrintController::class, 'printWebservice']);
        Route::get('sales-invoice-netto/{id}', [App\Http\Controllers\SalesInvoicePrintController::class, 'printNettoWebservice']);
        Route::get('sales-invoice-do/{id}', [App\Http\Controllers\SalesInvoicePrintController::class, 'printDOWebservice']);
    });

    Route::prefix('auth')->group(function () {
        Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/forgot-password', function () {
            return view('auth.forgot-password');
        });
        Route::post('/forgot-password', [AuthController::class, 'forgetPassword'])->name('password.request');
        Route::get('/reset-password', [AuthController::class, 'showResetForm'])->name('password.reset.form');
        Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.reset');
        Route::post('/reset-token/', [AuthController::class, 'resetDevice'])->name('token.reset');
    });
    Route::middleware(['auth'])->group(function () {

            //kalkulasi HPP nanti dibuatkan tampilan menunya
        Route::post('/calculate/{startDate}/{endDate}', [App\Http\Controllers\SystemController::class, 'calculate']);
        Route::prefix('master')->group(function () {
                Route::middleware(['role:master_customer'])->group(function () {
                    Route::prefix('group-customer')->group(function () {
                        Route::get('/', [App\Http\Controllers\GroupCustomerController::class, 'index']);
                        Route::post('/insert', [App\Http\Controllers\GroupCustomerController::class, 'insert']);
                        Route::post('/edit/{id}', [App\Http\Controllers\GroupCustomerController::class, 'update']);
                        Route::post('/delete/{id}', [App\Http\Controllers\GroupCustomerController::class, 'delete']);
                        Route::post('/import', [App\Http\Controllers\GroupCustomerController::class, 'import'])->name('group-customer.import');
                        Route::get('/export', [App\Http\Controllers\GroupCustomerController::class, 'export'])->name('group-customer.export');
                    });
                    Route::prefix('customer')->group(function () {
                        Route::get('/', [App\Http\Controllers\CustomerController::class, 'index']);
                        Route::post('/insert', [App\Http\Controllers\CustomerController::class, 'insert']);
                        Route::post('/edit/{id}', [App\Http\Controllers\CustomerController::class, 'update']);
                        Route::post('/delete/{id}', [App\Http\Controllers\CustomerController::class, 'delete']);
                        Route::post('/import', [App\Http\Controllers\CustomerController::class, 'import'])->name('customer.import');
                        Route::post('/import-extra', [App\Http\Controllers\CustomerController::class, 'importExtra'])->name('customer.import.extra');
                        Route::get('/export', [App\Http\Controllers\CustomerController::class, 'export'])->name('customer.export');
                        Route::get('updateCustMb2', [App\Http\Controllers\CustomerController::class, 'updateCustomerMb34']);
                        Route::get('setGroupCustomerMb3', [App\Http\Controllers\CustomerController::class, 'setGroupCustomerMb3']);
                    });
                    Route::prefix('category-customer')->group(function () {
                        Route::get('/', [App\Http\Controllers\CategoryCustomerController::class, 'index'])->name('master.category-customer');
                        Route::post('/store', [App\Http\Controllers\CategoryCustomerController::class, 'store'])->name('master.category-customer.store');
                        Route::get('/edit/{category}', [App\Http\Controllers\CategoryCustomerController::class, 'edit'])->name('master.category-customer.edit');
                        Route::post('/update/{category}', [App\Http\Controllers\CategoryCustomerController::class, 'update'])->name('master.category-customer.update');
                        Route::delete('/delete/{category}', [App\Http\Controllers\CategoryCustomerController::class, 'destroy'])->name('master.category-customer.destroy');
                        Route::post('/import', [App\Http\Controllers\CategoryCustomerController::class, 'import'])->name('master.category-customer.import');
                        Route::get('/export', [App\Http\Controllers\CategoryCustomerController::class, 'export'])->name('master.category-customer.export');
                    });
                });
                Route::prefix('type-company')->group(function () {
                    Route::get('/', [App\Http\Controllers\TypeCompanyController::class, 'index']);
                    Route::post('/insert', [App\Http\Controllers\TypeCompanyController::class, 'insert']);
                    Route::post('/edit/{id}', [App\Http\Controllers\TypeCompanyController::class, 'update']);
                    Route::post('/delete/{id}', [App\Http\Controllers\TypeCompanyController::class, 'delete']);
                });
                Route::prefix('company')->group(function () {
                    Route::get('/', [App\Http\Controllers\CompanyController::class, 'index']);
                    Route::post('/insert', [App\Http\Controllers\CompanyController::class, 'insert']);
                    Route::post('/edit/{id}', [App\Http\Controllers\CompanyController::class, 'update']);
                    Route::post('/delete/{id}', [App\Http\Controllers\CompanyController::class, 'delete']);
                });
                Route::prefix('location')->group(function () {
                    Route::get('/', [App\Http\Controllers\LocationController::class, 'index']);
                    Route::post('/insert', [App\Http\Controllers\LocationController::class, 'insert']);
                    Route::post('/edit/{id}', [App\Http\Controllers\LocationController::class, 'update']);
                    Route::post('/delete/{id}', [App\Http\Controllers\LocationController::class, 'delete']);
                });
                Route::prefix('promo')->group(function () {
                    Route::get('/', [App\Http\Controllers\PromoController::class, 'index'])->name('promo.index');
                    Route::post('/insert', [App\Http\Controllers\PromoController::class, 'store'])->name('promo.store');
                    Route::post('/edit/{id}', [App\Http\Controllers\PromoController::class, 'update'])->name('promo.update');
                    Route::delete('/delete/{id}', [App\Http\Controllers\PromoController::class, 'destroy'])->name('promo.destroy');
                });
                Route::prefix('depreciation')->group(function () {
                    Route::get('/', [App\Http\Controllers\DepreciationMethodController::class, 'index']);
                    Route::post('/insert', [App\Http\Controllers\DepreciationMethodController::class, 'insert']);
                    Route::post('/edit/{id}', [App\Http\Controllers\DepreciationMethodController::class, 'update']);
                    Route::post('/delete/{id}', [App\Http\Controllers\DepreciationMethodController::class, 'delete']);
                });

                Route::prefix('asset-type')->group(function () {
                    Route::get('/', [App\Http\Controllers\AssetTypeController::class, 'index']);
                    Route::post('/insert', [App\Http\Controllers\AssetTypeController::class, 'insert']);
                    Route::post('/edit/{id}', [App\Http\Controllers\AssetTypeController::class, 'update']);
                    Route::post('/delete/{id}', [App\Http\Controllers\AssetTypeController::class, 'delete']);
                });
                Route::middleware(['role:master_coa'])->group(function () {
                    Route::prefix('coa')->group(function () {
                        Route::get('/', [App\Http\Controllers\CoaController::class, 'index']);
                        Route::post('/insert', [App\Http\Controllers\CoaController::class, 'insert']);
                        Route::post('/edit/{id}', [App\Http\Controllers\CoaController::class, 'update']);
                        Route::post('/delete/{id}', [App\Http\Controllers\CoaController::class, 'delete']);
                        Route::post('/import', [App\Http\Controllers\CoaController::class, 'import'])->name('coa.import');
                        Route::get('/export', [App\Http\Controllers\CoaController::class, 'export'])->name('coa.export');
                    });
                    Route::prefix('coa-type')->group(function () {
                        Route::get('/', [App\Http\Controllers\CoaTypeController::class, 'index']);
                        Route::post('/insert', [App\Http\Controllers\CoaTypeController::class, 'insert']);
                        Route::post('/edit/{id}', [App\Http\Controllers\CoaTypeController::class, 'update']);
                        Route::post('/delete/{id}', [App\Http\Controllers\CoaTypeController::class, 'delete']);
                    });
                });
                Route::middleware(['role:master_warehouse'])->group(function () {
                    Route::prefix('warehouse')->group(function () {
                        Route::get('/', [App\Http\Controllers\WarehouseController::class, 'index']);
                        Route::post('/insert', [App\Http\Controllers\WarehouseController::class, 'insert']);
                        Route::post('/edit/{id}', [App\Http\Controllers\WarehouseController::class, 'update']);
                        Route::post('/delete/{id}', [App\Http\Controllers\WarehouseController::class, 'delete']);
                    });
                });
                Route::prefix('currency')->group(function () {
                    Route::get('/', [App\Http\Controllers\CurrencyController::class, 'index']);
                    Route::post('/insert', [App\Http\Controllers\CurrencyController::class, 'insert']);
                    Route::post('/edit/{id}', [App\Http\Controllers\CurrencyController::class, 'update']);
                    Route::post('/delete/{id}', [App\Http\Controllers\CurrencyController::class, 'delete']);
                    Route::post('/import', [App\Http\Controllers\CurrencyController::class, 'import'])->name('currency.import');
                    Route::get('/export', [App\Http\Controllers\CurrencyController::class, 'export'])->name('currency.export');
                });

                Route::prefix('cogs-method')->group(function () {
                    Route::get('/', [App\Http\Controllers\CogsMethodController::class, 'index'])->name('master.cogs-method');
                    Route::post('/store', [App\Http\Controllers\CogsMethodController::class, 'store'])->name('master.cogs-method.store');
                    Route::get('/edit/{cogsMethod}', [App\Http\Controllers\CogsMethodController::class, 'edit'])->name('master.cogs-method.edit');
                    Route::post('/update/{cogsMethod}', [App\Http\Controllers\CogsMethodController::class, 'update'])->name('master.cogs-method.update');
                    Route::delete('/delete/{cogsMethod}', [App\Http\Controllers\CogsMethodController::class, 'destroy'])->name('master.cogs-method.destroy');
                });
                Route::middleware(['role:master_department'])->group(function () {
                    Route::prefix('department')->group(function () {
                        Route::get('/', [App\Http\Controllers\DepartmentController::class, 'index']);
                        Route::post('/insert', [App\Http\Controllers\DepartmentController::class, 'insert']);
                        Route::post('/edit/{id}', [App\Http\Controllers\DepartmentController::class, 'update']);
                        Route::post('/delete/{id}', [App\Http\Controllers\DepartmentController::class, 'delete']);
                        Route::post('/import', [App\Http\Controllers\DepartmentController::class, 'import'])->name('department.import');
                        Route::get('/export', [App\Http\Controllers\DepartmentController::class, 'export'])->name('department.export');
                    });
                });
                Route::middleware(['role:master_supplier'])->group(function () {
                    Route::prefix('supplier')->group(function () {
                        Route::get('/', [App\Http\Controllers\SupplierController::class, 'index']);
                        Route::post('/insert', [App\Http\Controllers\SupplierController::class, 'insert']);
                        Route::post('/edit/{id}', [App\Http\Controllers\SupplierController::class, 'update']);
                        Route::post('/delete/{id}', [App\Http\Controllers\SupplierController::class, 'delete']);
                        Route::post('/import', [App\Http\Controllers\SupplierController::class, 'import'])->name('supplier.import'); // Mengarahkan ke SupplierController
                        Route::get('/export', [App\Http\Controllers\SupplierController::class, 'export'])->name('supplier.export'); // Mengarahkan ke SupplierController
                    });
                });
                Route::middleware(['role:master_salesman'])->group(function () {
                    Route::prefix('salesman')->group(function () {
                        Route::get('/', [App\Http\Controllers\SalesmanController::class, 'index']);
                        Route::post('/insert', [App\Http\Controllers\SalesmanController::class, 'insert']);
                        Route::post('/edit/{id}', [App\Http\Controllers\SalesmanController::class, 'update']);
                        Route::post('/delete/{id}', [App\Http\Controllers\SalesmanController::class, 'delete']);
                        Route::post('/inactive/{id}', [App\Http\Controllers\SalesmanController::class, 'inactive']);
                        Route::get('/export', [App\Http\Controllers\SalesmanController::class, 'export'])->name('salesman.export');
                    });
                });
                Route::middleware(['role:master_city'])->group(function () {
                    Route::prefix('city')->group(function () {
                        Route::get('/', [App\Http\Controllers\CityController::class, 'index']);
                        Route::post('/insert', [App\Http\Controllers\CityController::class, 'insert']);
                        Route::post('/edit/{id}', [App\Http\Controllers\CityController::class, 'update']);
                        Route::post('/delete/{id}', [App\Http\Controllers\CityController::class, 'delete']);
                        Route::post('/inactive/{id}', [App\Http\Controllers\CityController::class, 'inactive']);
                        Route::get('/export', [App\Http\Controllers\CityController::class, 'export'])->name('city.export');
                    });
                });
                Route::middleware(['role:master_zone'])->group(function () {
                    Route::prefix('zone')->group(function () {
                        Route::get('/', [App\Http\Controllers\ZoneController::class, 'index'])->name('zone.index');
                        Route::get('/input', [App\Http\Controllers\ZoneController::class, 'inputForm'])->name('zone.input');
                        Route::post('/insert', [App\Http\Controllers\ZoneController::class, 'insert'])->name('zone.insert');
                        Route::get('/edit/{id}', [App\Http\Controllers\ZoneController::class, 'edit'])->name('zone.edit');
                        Route::put('/update/{id}', [App\Http\Controllers\ZoneController::class, 'update'])->name('zone.update');
                        Route::post('/delete/{id}', [App\Http\Controllers\ZoneController::class, 'delete']);
                        Route::post('/inactive/{id}', [App\Http\Controllers\ZoneController::class, 'inactive']);
                    });
                });
                Route::middleware(['role:master_payment_method'])->group(function () {
                    Route::prefix('payment-method')->group(function () {
                        Route::get('/', [App\Http\Controllers\PaymentMethodController::class, 'index']);
                        Route::post('/insert', [App\Http\Controllers\PaymentMethodController::class, 'insert']);
                        Route::post('/edit/{id}', [App\Http\Controllers\PaymentMethodController::class, 'update']);
                        Route::post('/delete/{id}', [App\Http\Controllers\PaymentMethodController::class, 'delete']);
                    });
                });
                Route::middleware(['role:master_payment_method'])->group(function () {
                    Route::prefix('users')->group(function () {
                        Route::get('/', [App\Http\Controllers\UsersController::class, 'index']);
                        Route::post('/insert', [App\Http\Controllers\UsersController::class, 'insert']);
                        Route::post('/edit/{id}', [App\Http\Controllers\UsersController::class, 'update']);
                        Route::post('/delete/{id}', [App\Http\Controllers\UsersController::class, 'delete']);
                        Route::post('/inactive/{id}', [App\Http\Controllers\UsersController::class, 'inactive']);
                    });
                });
                Route::middleware(['role:master_tax_master'])->group(function () {
                    Route::prefix('tax-master')->group(function () {
                        Route::get('/', [App\Http\Controllers\TaxMasterController::class, 'index']);
                        Route::post('/insert', [App\Http\Controllers\TaxMasterController::class, 'insert']);
                        Route::post('/edit/{id}', [App\Http\Controllers\TaxMasterController::class, 'update']);
                        Route::post('/delete/{id}', [App\Http\Controllers\TaxMasterController::class, 'delete']);
                    });
                });
                Route::middleware(['role:master_role'])->group(function () {
                    Route::prefix('role')->group(function () {
                        Route::get('/', [App\Http\Controllers\RoleController::class, 'index'])->name('roles.index');
                        Route::post('/insert', [App\Http\Controllers\RoleController::class, 'insert'])->name('roles.create');
                        Route::post('/edit/{id}', [App\Http\Controllers\RoleController::class, 'update']);
                        Route::post('/delete/{id}', [App\Http\Controllers\RoleController::class, 'delete']);
                    });
                });
                Route::middleware(['role:master_item'])->group(function () {
                    Route::prefix('item-category')->group(function () {
                        Route::get('/', [App\Http\Controllers\ItemCategoryController::class, 'index']);
                        Route::post('/insert', [App\Http\Controllers\ItemCategoryController::class, 'insert']);
                        Route::post('/edit/{id}', [App\Http\Controllers\ItemCategoryController::class, 'update']);
                        Route::post('/delete/{id}', [App\Http\Controllers\ItemCategoryController::class, 'delete']);
                    });
                    Route::prefix('item-detail')->group(function () {
                        Route::get('/', [App\Http\Controllers\ItemDetailController::class, 'index'])->name('master.item-detail.index');
                        Route::post('/insert', [App\Http\Controllers\ItemDetailController::class, 'insert'])->name('master.item-detail.insert');
                        Route::post('/edit/{id}', [App\Http\Controllers\ItemDetailController::class, 'update'])->name('master.item-detail.update');
                        Route::delete('/delete/{id}', [App\Http\Controllers\ItemDetailController::class, 'delete'])->name('master.item-detail.delete');
                    });
                    Route::prefix('item-unit')->group(function () {
                        Route::get('/', [App\Http\Controllers\ItemUnitController::class, 'index']);
                        Route::post('/insert', [App\Http\Controllers\ItemUnitController::class, 'insert']);
                        Route::post('/edit/{id}', [App\Http\Controllers\ItemUnitController::class, 'update']);
                        Route::post('/delete/{id}', [App\Http\Controllers\ItemUnitController::class, 'delete']);
                    });
                    Route::prefix('item')->group(function () {
                        Route::get('/', [App\Http\Controllers\ItemController::class, 'index'])->name('item.index');
                        Route::get('/input', [App\Http\Controllers\ItemController::class, 'inputForm'])->name('item.input'); // Route to display the input form
                        Route::post('/insert', [App\Http\Controllers\ItemController::class, 'insert'])->name('item.insert');  // Route to handle item insertion
                        Route::get('/edit/{id}', [App\Http\Controllers\ItemController::class, 'edit'])->name('item.edit');     // Route to edit an item
                        Route::put('/update/{id}', [App\Http\Controllers\ItemController::class, 'update'])->name('item.update'); // Route to handle item update
                        Route::post('/delete/{id}', [App\Http\Controllers\ItemController::class, 'delete'])->name('item.delete'); // Route to delete an item
                        Route::get('/show/{id}', [App\Http\Controllers\ItemController::class, 'show'])->name('item.show');     // Route to show item details
                        Route::post('/import', [App\Http\Controllers\ItemController::class, 'import'])->name('item.import');
                        Route::post('/import-saldo', [App\Http\Controllers\ItemController::class, 'importSaldo'])->name('item.import');
                        Route::get('/setPurchaseSalesPrice',[App\Http\Controllers\ItemController::class, 'setItemPurchaseSales']);
                    });
                });
                Route::middleware(['role:master_item_purchase'])->group(function () {
                    Route::prefix('item-purchase')->group(function () {
                        Route::get('/', [App\Http\Controllers\ItemPurchaseController::class, 'index']);
                        Route::post('/insert', [App\Http\Controllers\ItemPurchaseController::class, 'insert']);
                        Route::post('/edit/{id}', [App\Http\Controllers\ItemPurchaseController::class, 'update']);
                        Route::post('/delete/{id}', [App\Http\Controllers\ItemPurchaseController::class, 'delete']);
                    });
                });
                Route::middleware(['role:master_item_sales'])->group(function () {
                    Route::prefix('item-sales-price')->group(function () {
                        Route::get('/', [App\Http\Controllers\ItemSalesPriceController::class, 'index'])->name('master.item-sales-price.index'); // Show list
                        Route::post('/insert', [App\Http\Controllers\ItemSalesPriceController::class, 'insert'])->name('master.item-sales-price.insert'); // Insert new item sales price
                        Route::post('/edit/{id}', [App\Http\Controllers\ItemSalesPriceController::class, 'update'])->name('master.item-sales-price.edit'); // Update existing item sales price
                        Route::delete('/delete/{id}', [App\Http\Controllers\ItemSalesPriceController::class, 'delete'])->name('master.item-sales-price.delete'); // Delete item sales price
                    });
                });

            });


        Route::prefix('transaction')->group(function () {
            Route::get('/delete-log',[\App\Http\Controllers\SystemLogController::class,'index'])->name('transaction.delete_log');
            Route::get('/cancel-log',[\App\Http\Controllers\SystemLogController::class,'cancel_log'])->name('transaction.cancel_log');
            Route::prefix('purchase-requisition')->group(function () {
                Route::get('/', [App\Http\Controllers\PurchaseRequisitionController::class, 'index'])->name('transaction.purchase_requisition'); // Display the list of cash ins
                Route::get('/create', [App\Http\Controllers\PurchaseRequisitionController::class, 'create'])->name('transaction.purchase_requisition.create'); // Show the form to create a new cash in
                Route::post('/store', [App\Http\Controllers\PurchaseRequisitionController::class, 'store'])->name('transaction.purchase_requisition.store'); // Store a newly created cash in
                Route::get('/edit/{id}', [App\Http\Controllers\PurchaseRequisitionController::class, 'edit'])->name('transaction.purchase_requisition.edit'); // Show the form to edit a cash in
                Route::put('/generate/{id}', [App\Http\Controllers\PurchaseRequisitionController::class, 'generate'])->name('transaction.purchase_requisition.generate');
                Route::get('/summary', [App\Http\Controllers\PurchaseRequisitionController::class, 'summary'])->name('transaction.purchase_requisition.summary');
                // Update existing cash in using POST
                Route::put('/update/{id}', [App\Http\Controllers\PurchaseRequisitionController::class, 'update'])->name('transaction.purchase_requisition.update');

                Route::post('/delete/{id}', [App\Http\Controllers\PurchaseRequisitionController::class, 'destroy'])->name('transaction.purchase_requisition.destroy'); // Delete a cash in
            });
            Route::middleware(['role:good_receipt'])->group(function () {
                Route::prefix('good-receipt')->group(function () {
                    Route::get('/', [App\Http\Controllers\GoodReceiptController::class, 'index'])->name('transaction.warehouse.good_receipt');
                    Route::get('/create', [App\Http\Controllers\GoodReceiptController::class, 'create'])->name('transaction.warehouse.good_receipt.create');
                    Route::post('/store', [App\Http\Controllers\GoodReceiptController::class, 'store'])->name('transaction.warehouse.good_receipt.store');
                    Route::get('/edit/{id}', [App\Http\Controllers\GoodReceiptController::class, 'edit'])->name('transaction.warehouse.good_receipt.edit');
                    Route::post('/update/{id}', [App\Http\Controllers\GoodReceiptController::class, 'update'])->name('transaction.warehouse.good_receipt.update');
                    Route::post('/cancel/{id}', [App\Http\Controllers\GoodReceiptController::class, 'cancel'])->name('transaction.good_receipt.cancel');
                    Route::post('/delete/{id}', [App\Http\Controllers\GoodReceiptController::class, 'destroy'])->name('transaction.warehouse.good_receipt.destroy');
                    Route::get('/print/{id}', [App\Http\Controllers\GoodReceiptController::class, 'printPDF'])->name('transaction.warehouse.good_receipt.print');
                });
                Route::prefix('delivery-order')->group(function () {
                    Route::get('/', [App\Http\Controllers\DeliveryOrderController::class, 'index'])->name('transaction.warehouse.delivery_order');
                    Route::get('/create', [App\Http\Controllers\DeliveryOrderController::class, 'create'])->name('transaction.warehouse.delivery_order.create');
                    Route::post('/store', [App\Http\Controllers\DeliveryOrderController::class, 'store'])->name('transaction.warehouse.delivery_order.store');
                    Route::get('/edit/{id}', [App\Http\Controllers\DeliveryOrderController::class, 'edit'])->name('transaction.warehouse.delivery_order.edit');
                    Route::post('/update/{id}', [App\Http\Controllers\DeliveryOrderController::class, 'update'])->name('transaction.warehouse.delivery_order.update');
                    Route::post('/cancel/{id}', [App\Http\Controllers\DeliveryOrderController::class, 'cancel'])->name('transaction.warehouse.delivery_order.cancel');
                    Route::post('/delete/{id}', [App\Http\Controllers\DeliveryOrderController::class, 'destroy'])->name('transaction.warehouse.delivery_order.destroy');
                    Route::get('/print/{id}',[App\Http\Controllers\DeliveryOrderController::class, 'printPDF'])->name('transaction.warehouse.delivery_order.print');
                });
            });
            Route::middleware(['role:purchase_order'])->group(function () {
                Route::prefix('purchase-order')->group(function () {
                    Route::get('/', [App\Http\Controllers\PurchaseOrderController::class, 'index'])->name('transaction.purchase_order'); // Display the list of cash ins
                    Route::get('/create', [App\Http\Controllers\PurchaseOrderController::class, 'create'])->name('transaction.purchase_order.create'); // Show the form to create a new cash in
                    Route::post('/store', [App\Http\Controllers\PurchaseOrderController::class, 'store'])->name('transaction.purchase_order.store'); // Store a newly created cash in
                    Route::get('/edit/{id}', [App\Http\Controllers\PurchaseOrderController::class, 'edit'])->name('transaction.purchase_order.edit'); // Show the form to edit a cash in
                    Route::put('/generate/{id}', [App\Http\Controllers\PurchaseOrderController::class, 'generate'])->name('transaction.purchase_order.generate');
                    Route::post('/cancel/{id}', [App\Http\Controllers\PurchaseOrderController::class, 'cancel'])->name('transaction.purchase_order.cancel');
                    // Update existing cash in using POST
                    Route::put('/update/{id}', [App\Http\Controllers\PurchaseOrderController::class, 'update'])->name('transaction.purchase_order.update');

                    Route::post('/delete/{id}', [App\Http\Controllers\PurchaseOrderController::class, 'destroy'])->name('transaction.purchase_order.destroy'); // Delete a cash in

                    // Route to fetch items based on the selected sales order number
                    Route::get('/fetch-items', [App\Http\Controllers\PurchaseOrderController::class, 'fetchItems'])->name('transaction.fetch_items'); // Fetch items for selected sales order

                    Route::get('/purchase-order/{id}/print', [App\Http\Controllers\PurchaseOrderController::class, 'printPDF'])->name('transaction.purchase_order.print');
                    Route::get('/purchase-order/{id}/print/netto', [App\Http\Controllers\PurchaseOrderController::class, 'printPDFNetto'])->name('transaction.purchase_order.print.netto');
                });
            });



                Route::prefix('reimburse')->group(function () {
                    Route::get('/', [App\Http\Controllers\ReimburseController::class, 'index'])->name('transaction.reimburse'); // Display the list of cash ins
                    Route::get('/create', [App\Http\Controllers\ReimburseController::class, 'create'])->name('transaction.reimburse.create'); // Show the form to create a new cash in
                    Route::get('/fetch-items', [App\Http\Controllers\ReimburseController::class, 'fetchItems'])->name('transaction.reimburse.fetch_items'); // Show the form to create a new cash in
                    Route::post('/store', [App\Http\Controllers\ReimburseController::class, 'store'])->name('transaction.reimburse.store'); // Store a newly created cash in
                    Route::get('/edit/{id}', [App\Http\Controllers\ReimburseController::class, 'edit'])->name('transaction.reimburse.edit'); // Show the form to edit a cash in
                    Route::put('/generate/{id}', [App\Http\Controllers\ReimburseController::class, 'generate'])->name('transaction.reimburse.generate');

                    // Update existing cash in using POST
                    Route::put('/update/{id}', [App\Http\Controllers\ReimburseController::class, 'update'])->name('transaction.reimburse.update');

                    Route::post('/delete/{id}', [App\Http\Controllers\ReimburseController::class, 'destroy'])->name('transaction.reimburse.destroy'); // Delete a cash in

                    // Route to fetch items based on the selected sales order number
                    Route::get('/fetch-items', [App\Http\Controllers\ReimburseController::class, 'fetchItems'])->name('transaction.reimburse.fetch_items'); // Fetch items for selected sales order
                    Route::get('/reimburse/{id}/print', [App\Http\Controllers\ReimburseController::class, 'printPDF'])->name('transaction.reimburse.print');
                });

                Route::middleware(['role:purchase_invoice'])->group(function () {

                Route::prefix('purchase-invoice')->group(function () {
                    Route::get('/', [App\Http\Controllers\PurchaseInvoiceController::class, 'index'])->name('transaction.purchase_invoice');
                    Route::get('/edit/{id}', [App\Http\Controllers\PurchaseInvoiceController::class, 'edit'])->name('transaction.purchase_invoice.edit'); //
                    // Update existing cash in using POST
                    Route::put('/update/{id}', [App\Http\Controllers\PurchaseInvoiceController::class, 'update'])->name('transaction.purchase_invoice.update');
                    Route::put('/updateStatus/{id}', [App\Http\Controllers\PurchaseInvoiceController::class, 'updateStatus'])->name('transaction.purchase_invoice.approve');
                    Route::post('/delete/{id}', [App\Http\Controllers\PurchaseInvoiceController::class, 'destroy'])->name('transaction.purchase_invoice.destroy');
                    Route::post('/cancel/{id}', [App\Http\Controllers\PurchaseInvoiceController::class, 'cancel'])->name('transaction.purchase_invoice.cancel');
                    Route::get('/purchase-invoice/{id}/print', [App\Http\Controllers\PurchaseInvoiceController::class, 'printPDF'])->name('transaction.purchase_invoice.print');
                    Route::get('/purchase-invoice/{id}/print/netto', [App\Http\Controllers\PurchaseInvoiceController::class, 'printPDFNetto'])->name('transaction.purchase_invoice.print.netto');
                    Route::get('/summary', [App\Http\Controllers\PurchaseInvoiceController::class, 'summary'])->name('transaction.purchase_invoice.summary');
                    Route::get('/summary-detail', [App\Http\Controllers\PurchaseInvoiceController::class, 'summaryDetail'])->name('transaction.purchase_invoice.summary_detail');
                    Route::get('/create', [App\Http\Controllers\PurchaseInvoiceController::class, 'create'])->name('transaction.purchase_invoice.create'); // Show the form to create a new cash in
                    Route::post('/store', [App\Http\Controllers\PurchaseInvoiceController::class, 'store'])->name('transaction.purchase_invoice.store'); // Store a newly created cash in
                    Route::get('/recalculate-journal', [App\Http\Controllers\PurchaseInvoiceController::class, 'recalcJournal']);
                    Route::get('/setVendorNumber', [App\Http\Controllers\PurchaseInvoiceController::class, 'setVendorNumber']);
                });
            });

            Route::middleware(['role:purchase_return'])->group(function () {
                Route::prefix('purchase-return')->group(function () {
                    Route::get('/', [App\Http\Controllers\PurchaseReturnController::class, 'index'])->name('transaction.purchase_return');
                    Route::get('/edit/{id}', [App\Http\Controllers\PurchaseReturnController::class, 'edit'])->name('transaction.purchase_return.edit'); //
                    // Update existing cash in using POST
                    Route::put('/update/{id}', [App\Http\Controllers\PurchaseReturnController::class, 'update'])->name('transaction.purchase_return.update');
                    Route::put('/updateStatus/{id}', [App\Http\Controllers\PurchaseReturnController::class, 'updateStatus'])->name('transaction.purchase_return.approve');
                    Route::post('/delete/{id}', [App\Http\Controllers\PurchaseReturnController::class, 'destroy'])->name('transaction.purchase_return.destroy');
                    Route::get('/{id}/print', [App\Http\Controllers\PurchaseReturnController::class, 'printPDF'])->name('transaction.purchase_return.print');
                    Route::get('/summary', [App\Http\Controllers\PurchaseReturnController::class, 'summary'])->name('transaction.purchase_return.summary');
                    Route::get('/create', [App\Http\Controllers\PurchaseReturnController::class, 'create'])->name('transaction.purchase_return.create'); // Show the form to create a new cash in
                    Route::post('/store', [App\Http\Controllers\PurchaseReturnController::class, 'store'])->name('transaction.purchase_return.store'); // Store a newly created cash in
                    Route::get('/recalculate-journal', [App\Http\Controllers\PurchaseReturnController::class, 'recalcJournal']);
                    Route::get('/recalculate-inventory', [App\Http\Controllers\PurchaseReturnController::class, 'recalculateInventory']);
                });
            });

            Route::middleware(['role:sales_return'])->group(function () {
                Route::prefix('sales-return')->group(function () {
                    Route::get('/', [App\Http\Controllers\SalesReturnController::class, 'index'])->name('transaction.sales_return');
                    Route::get('/edit/{id}', [App\Http\Controllers\SalesReturnController::class, 'edit'])->name('transaction.sales_return.edit'); //
                    // Update existing cash in using POST
                    Route::put('/update/{id}', [App\Http\Controllers\SalesReturnController::class, 'update'])->name('transaction.sales_return.update');
                    Route::put('/updateStatus/{id}', [App\Http\Controllers\SalesReturnController::class, 'updateStatus'])->name('transaction.sales_return.approve');
                    Route::post('/delete/{id}', [App\Http\Controllers\SalesReturnController::class, 'destroy'])->name('transaction.sales_return.destroy');
                    Route::get('/{id}/print', [App\Http\Controllers\SalesReturnController::class, 'printSalesReturnPDF'])->name('transaction.sales_return.print');
                    Route::get('/summary', [App\Http\Controllers\SalesReturnController::class, 'summary'])->name('transaction.sales_return.summary');
                    Route::get('/create', [App\Http\Controllers\SalesReturnController::class, 'create'])->name('transaction.sales_return.create'); // Show the form to create a new cash in
                    Route::post('/store', [App\Http\Controllers\SalesReturnController::class, 'store'])->name('transaction.sales_return.store'); // Store a newly created cash in
                    Route::get('/recalculate-journal', [App\Http\Controllers\SalesReturnController::class, 'recalcJournal']);
                });
            });
            Route::middleware(['role:sales_order'])->group(function () {
                Route::prefix('sales-order')->group(function () {
                    Route::get('/', [App\Http\Controllers\SalesOrderController::class, 'index'])->name('transaction.sales_order'); // Display the list of cash ins
                    Route::get('/create', [App\Http\Controllers\SalesOrderController::class, 'create'])->name('transaction.sales_order.create'); // Show the form to create a new cash in
                    Route::post('/store', [App\Http\Controllers\SalesOrderController::class, 'store'])->name('transaction.sales_order.store'); // Store a newly created cash in
                    Route::post('/cancel-detail', [App\Http\Controllers\SalesOrderController::class, 'cancelDetail'])->name('transaction.sales_order.cancel_detail'); // Cancel sales order detail
                    Route::get('/edit/{id}', [App\Http\Controllers\SalesOrderController::class, 'edit'])->name('transaction.sales_order.edit'); // Show the form to edit a cash in
                    Route::get('/summary', [App\Http\Controllers\SalesOrderController::class, 'summary'])->name('transaction.sales_order.summary');
                    Route::get('/summary/{id}/print', [App\Http\Controllers\SalesOrderController::class, 'printSummary'])->name('transaction.sales_order.print');
                    Route::get('/summary-detail', [App\Http\Controllers\SalesOrderController::class, 'summaryDetail'])->name('transaction.sales_order.summary_detail');
                    // Update existing cash in using POST
                    Route::put('/update/{id}', [App\Http\Controllers\SalesOrderController::class, 'update'])->name('transaction.sales_order.update');

                    Route::post('/delete/{id}', [App\Http\Controllers\SalesOrderController::class, 'destroy'])->name('transaction.sales_order.destroy'); // Delete a cash in
                    Route::post('/cancel/{id}', [App\Http\Controllers\SalesOrderController::class, 'cancel'])->name('transaction.sales_order.cancel');
                    Route::get('/sales-order/{id}/print', [SalesOrderController::class, 'printSalesOrderPDF'])->name('sales-order.print');
                    Route::get('/sales-order/{id}/print/netto', [SalesOrderController::class, 'printSalesOrderPDFNetto'])->name('sales_order.print.netto');
                    Route::post('customer-item', [SalesOrderController::class, 'getCustomerItem'])->name('sales_order.custItem');

                });
            });


            Route::middleware(['role:sales_invoice'])->group(function () {
                Route::prefix('sales-invoice')->group(function () {
                    Route::get('/', [App\Http\Controllers\SalesInvoiceController::class, 'index'])->name('transaction.sales_invoice');
                     Route::get('/create', [App\Http\Controllers\SalesInvoiceController::class, 'create'])->name('transaction.sales_invoice.create'); // Show the form to create a new cash in
                    Route::post('/store', [App\Http\Controllers\SalesInvoiceController::class, 'store'])->name('transaction.sales_invoice.store'); // Store a newly created cash in
                    Route::get('/edit/{id}', [App\Http\Controllers\SalesInvoiceController::class, 'edit'])->name('transaction.sales_invoice.edit'); //
                    // Update existing cash in using POST
                    Route::put('/update/{id}', [App\Http\Controllers\SalesInvoiceController::class, 'update'])->name('transaction.sales_invoice.update');
                    Route::post('/delete/{id}', [App\Http\Controllers\SalesInvoiceController::class, 'destroy'])->name('transaction.sales_invoice.destroy');

                    Route::get('/sales-invoice/{id}/print', [App\Http\Controllers\SalesInvoiceController::class, 'printSalesInvoicePDF'])
                    ->name('sales_invoice.print');

                    Route::get('/summary', [App\Http\Controllers\SalesInvoiceController::class, 'summary'])->name('transaction.sales_invoice.summary');
                    Route::get('/summary-detail', [App\Http\Controllers\SalesInvoiceController::class, 'summaryDetail'])->name('transaction.sales_invoice.summary_detail');

                    //Route::get('/delivery-confirmation', [App\Http\Controllers\SalesInvoiceController::class, 'showDeliveryPage'])->name('transaction.sales_invoice.delivery_confirmation');
                    //Route::get('/delivery-report', [App\Http\Controllers\SalesInvoiceController::class, 'summaryDelivery'])->name('transaction.sales_invoice_recap.delivery_report');
                    //Route::post('/update-status', [App\Http\Controllers\SalesInvoiceController::class, 'updateStatus'])->name('transaction.sales_invoice.update_status');
                    //Route::get('/delivery-confirmation-cancel', [App\Http\Controllers\SalesInvoiceController::class, 'showCancelDeliveryPage'])->name('transaction.sales_invoice.delivery_confirmation_cancel');
                    //Route::post('/update-status-cancel', [App\Http\Controllers\SalesInvoiceController::class, 'updateStatusCancel'])->name('transaction.sales_invoice.update_status_cancel');
                    //Route::get('/recalculate-journal', [App\Http\Controllers\SalesInvoiceController::class, 'recalcJournal']);
                    //Route::get('/updateStatusDb', [App\Http\Controllers\SalesInvoiceController::class, 'updateStatusDb']);


                });
            });

            Route::middleware(['role:debt_other'])->group(function () {
                Route::prefix('debt-other')->group(function () {
                    Route::get('/', [App\Http\Controllers\DebtOtherController::class, 'index'])->name('transaction.debt_other');
                    Route::get('/create', [App\Http\Controllers\DebtOtherController::class, 'create'])->name('transaction.debt_other.create'); // Show the form to create a new cash in
                    Route::post('/store', [App\Http\Controllers\DebtOtherController::class, 'store'])->name('transaction.debt_other.store'); // Store a newly created cash in
                    Route::get('/edit/{id}', [App\Http\Controllers\DebtOtherController::class, 'edit'])->name('transaction.debt_other.edit'); //
                    // Update existing cash in using POST
                    Route::put('/update/{id}', [App\Http\Controllers\DebtOtherController::class, 'update'])->name('transaction.debt_other.update');
                    Route::post('/delete/{id}', [App\Http\Controllers\DebtOtherController::class, 'destroy'])->name('transaction.debt_other.destroy');

                    Route::get('/print/{id}/', [App\Http\Controllers\DebtOtherController::class, 'printPDF'])
                    ->name('debt_other.print');
                    Route::get('/summary', [App\Http\Controllers\DebtOtherController::class, 'summary'])->name('transaction.debt_other.summary');
                    Route::get('/summary-detail', [App\Http\Controllers\DebtOtherController::class, 'summaryDetail'])->name('transaction.debt_other.summary_detail');

                });
            });
            Route::middleware(['role:bank_in'])->group(function () {
                Route::prefix('bank-cash-in')->group(function () {
                    Route::get('/', [App\Http\Controllers\BankCashInController::class, 'index'])->name('transaction.bank_cash_in'); // Display the list of cash ins
                    Route::get('/create', [App\Http\Controllers\BankCashInController::class, 'create'])->name('transaction.bank_cash_in.create'); // Show the form to create a new cash in
                    Route::post('/store', [App\Http\Controllers\BankCashInController::class, 'store'])->name('transaction.bank_cash_in.store'); // Store a newly created cash in
                    Route::get('/edit/{id}', [App\Http\Controllers\BankCashInController::class, 'edit'])->name('transaction.bank_cash_in.edit'); // Show the form to edit a cash in
                    // Update existing cash in using POST
                    Route::post('/update/{id}', [App\Http\Controllers\BankCashInController::class, 'update'])->name('transaction.bank_cash_in.update');
                    Route::post('/delete/{id}', [App\Http\Controllers\BankCashInController::class, 'destroy'])->name('transaction.bank_cash_in.destroy'); // Delete a cash in
                    Route::get('/print/{id}', [App\Http\Controllers\BankCashInController::class, 'printPDF'])->name('transaction.bank_cash_in.print');
                });
            });
            Route::middleware(['role:bank_out'])->group(function () {
                Route::prefix('bank-cash-out')->group(function () {
                    Route::get('/', [App\Http\Controllers\BankCashOutController::class, 'index'])->name('transaction.bank_cash_out'); // Display the list of cash ins
                    Route::get('/create', [App\Http\Controllers\BankCashOutController::class, 'create'])->name('transaction.bank_cash_out.create'); // Show the form to create a new cash in
                    Route::post('/store', [App\Http\Controllers\BankCashOutController::class, 'store'])->name('transaction.bank_cash_out.store'); // Store a newly created cash in
                    Route::get('/edit/{id}', [App\Http\Controllers\BankCashOutController::class, 'edit'])->name('transaction.bank_cash_out.edit'); // Show the form to edit a cash in
                    // Update existing cash in using POST
                    Route::post('/update/{id}', [App\Http\Controllers\BankCashOutController::class, 'update'])->name('transaction.bank_cash_out.update');
                    Route::post('/delete/{id}', [App\Http\Controllers\BankCashOutController::class, 'destroy'])->name('transaction.bank_cash_out.destroy'); // Delete a cash in
                    Route::get('/print/{id}', [App\Http\Controllers\BankCashOutController::class, 'printPDF'])->name('transaction.bank_cash_out.print');
                });
            });
            Route::middleware(['role:general_journal'])->group(function () {
                Route::prefix('general-journal')->group(function () {
                    Route::get('/', [App\Http\Controllers\GeneralJournalController::class, 'index'])->name('transaction.general_journal'); // Display the list of cash ins
                    Route::get('/create', [App\Http\Controllers\GeneralJournalController::class, 'create'])->name('transaction.general_journal.create'); // Show the form to create a new cash in
                    Route::post('/store', [App\Http\Controllers\GeneralJournalController::class, 'store'])->name('transaction.general_journal.store'); // Store a newly created cash in
                    Route::get('/edit/{id}', [App\Http\Controllers\GeneralJournalController::class, 'edit'])->name('transaction.general_journal.edit'); // Show the form to edit a cash in
                    // Update existing cash in using POST
                    Route::post('/update/{id}', [App\Http\Controllers\GeneralJournalController::class, 'update'])->name('transaction.general_journal.update');
                    Route::post('/delete/{id}', [App\Http\Controllers\GeneralJournalController::class, 'destroy'])->name('transaction.general_journal.destroy'); // Delete a cash in
                    Route::get('/print/{id}', [App\Http\Controllers\GeneralJournalController::class, 'printPDF'])->name('transaction.general_journal.print');
                });
            });
            Route::middleware(['role:closing'])->group(function () {
                Route::prefix('closing')->group(function() {
                    Route::get('/show', [App\Http\Controllers\PeriodeController::class, 'showClosing'])->name('transaction.closing.show'); // Display the list of cash ins
                    Route::post('/transaction',  [App\Http\Controllers\PeriodeController::class, 'closing'])->name('transaction.closing.closing'); // Show the form to create a new cash in`
                    Route::post('/restore', [App\Http\Controllers\PeriodeController::class, 'restore'])->name('transaction.closing.restore');
                });
            });
            Route::middleware(['role:receivable_list'])->group(function () {
                Route::prefix('receivable-list')->group(function () {
                    Route::get('/', [App\Http\Controllers\ReceivableListController::class, 'index'])->name('transaction.receivable_list'); // Display the list of cash ins
                    Route::get('/create', [App\Http\Controllers\ReceivableListController::class, 'create'])->name('transaction.receivable_list.create'); // Show the form to create a new cash in
                    Route::post('/insert', [App\Http\Controllers\ReceivableListController::class, 'insert'])->name('transaction.receivable_list.insert'); // Store a newly created cash in
                    Route::get('/edit/{id}', [App\Http\Controllers\ReceivableListController::class, 'edit'])->name('transaction.receivable_list.edit'); // Show the form to edit a cash in

                    // Update existing cash in using POST
                    Route::put('/update/{id}', [App\Http\Controllers\ReceivableListController::class, 'update'])->name('transaction.receivable_list.update');

                    Route::post('/delete/{id}', [App\Http\Controllers\ReceivableListController::class, 'delete'])->name('transaction.receivable_list.delete'); // Delete a cash in
                    Route::get('/print/{id}', [App\Http\Controllers\ReceivableListController::class, 'printPDF'])->name('transaction.receivable_list.print');
                });
            });
            Route::middleware(['role:receivable_list_salesman'])->group(function () {
                Route::prefix('receivable-list-salesman')->group(function () {
                    Route::get('/', [App\Http\Controllers\ReceivableListSalesmanController::class, 'index'])->name('transaction.receivable_list_salesman'); // Display the list of cash ins
                    Route::get('/create', [App\Http\Controllers\ReceivableListSalesmanController::class, 'create'])->name('transaction.receivable_list_salesman.create'); // Show the form to create a new cash in
                    Route::post('/insert', [App\Http\Controllers\ReceivableListSalesmanController::class, 'insert'])->name('transaction.receivable_list_salesman.insert'); // Store a newly created cash in
                    Route::get('/edit/{id}', [App\Http\Controllers\ReceivableListSalesmanController::class, 'edit'])->name('transaction.receivable_list_salesman.edit'); // Show the form to edit a cash in

                    // Update existing cash in using POST
                    Route::put('/update/{id}', [App\Http\Controllers\ReceivableListSalesmanController::class, 'update'])->name('transaction.receivable_list_salesman.update');

                    Route::post('/delete/{id}', [App\Http\Controllers\ReceivableListSalesmanController::class, 'delete'])->name('transaction.receivable_list_salesman.delete'); // Delete a cash in
                    Route::get('/print/{id}', [App\Http\Controllers\ReceivableListSalesmanController::class, 'printPDF'])->name('transaction.receivable_list_salesman.print');
                });
            });
            Route::middleware(['role:accounting_report'])->group(function () {
                Route::prefix('journal')->group(function () {
                    Route::get('/', [App\Http\Controllers\JournalController::class, 'index'])
                        ->name('transaction.journal');
                    Route::get('/fetchItems', [App\Http\Controllers\JournalController::class, 'fetchItems'])
                        ->name('transaction.journal.fetch_items');
                    Route::get('/fetchLedgerItems', [App\Http\Controllers\JournalController::class, 'fetchLedgerItems'])
                        ->name('transaction.journal.fetch_ledger_items');
                    Route::get('/journal-ledger', [App\Http\Controllers\JournalController::class, 'ledger'])
                        ->name('transaction.journal.ledger');
                    Route::get('/trial-balance', [App\Http\Controllers\JournalController::class, 'showTrialBalance'])->name('transaction.journal.trial_balance');
                    Route::get('/trial-balance/fetch', [App\Http\Controllers\JournalController::class, 'fetchTrialBalance'])->name('transaction.journal.fetchTrialBalance');
                    Route::get('/trial-balance/pdf', [App\Http\Controllers\JournalController::class, 'generateTrialBalancePdf'])
                        ->name('transaction.journal.trial_balance.pdf');
                    Route::get('/payable-report', [App\Http\Controllers\JournalController::class, 'payableReport'])->name('transaction.journal.payableReport');
                    Route::get('/payable-report/pdf', [App\Http\Controllers\JournalController::class, 'payableReportPdf'])->name('transaction.journal.payableReport.pdf');
                    Route::get('/payable-aging', [App\Http\Controllers\JournalController::class, 'payableAging'])->name('transaction.journal.payableAging');
                    Route::get('/payable-aging/pdf', [App\Http\Controllers\JournalController::class, 'payableAgingPdf'])->name('transaction.journal.payableAgingPdf');
                    Route::get('/receivable-report', [App\Http\Controllers\JournalController::class, 'receivableReport'])->name('transaction.journal.receivableReport');
                    Route::get('/receivable-report/pdf', [App\Http\Controllers\JournalController::class, 'receivableReportPdf'])->name('transaction.journal.receivableReport.pdf');
                    Route::get('/receivable-report-detail', [App\Http\Controllers\JournalController::class, 'receivableReportDetail'])->name('transaction.journal.receivableReportDetail');
                    Route::get('/receivable-report-detail/pdf', [App\Http\Controllers\JournalController::class, 'receivableReportDetailPdf'])->name('transaction.journal.receivableDetailReport.pdf');
                    Route::get('/receivable-aging', [App\Http\Controllers\JournalController::class, 'receivableAging'])->name('transaction.journal.receivableAging');
                    Route::get('/receivable-aging/pdf', [App\Http\Controllers\JournalController::class, 'receivableAgingPdf'])->name('transaction.journal.ReceivableAgingPdf');

                    Route::get('/balance-sheet', [App\Http\Controllers\JournalController::class, 'showBalanceSheet'])
                        ->name('transaction.journal.balance_sheet');

                    Route::get('/balance-sheet-data', [App\Http\Controllers\JournalController::class, 'filterBalanceSheetData'])
                        ->name('transaction.journal.balanceSheetData');

                    Route::get('/balance-sheet/print', [App\Http\Controllers\JournalController::class, 'balanceSheetPrint'])
                        ->name('transaction.journal.balance_sheet.print');
                    Route::get('/income-statement', [App\Http\Controllers\JournalController::class, 'showIncomeStatement'])
                    ->name('transaction.journal.income_statement');
                    Route::get('/income-statement/fetch', [App\Http\Controllers\JournalController::class, 'fetchIncomeStatement'])
                    ->name('transaction.journal.fetchIncomeStatement');
                    Route::get('/income-statement/print', [App\Http\Controllers\JournalController::class, 'generateIncomeStatementPdf'])
                    ->name('transaction.journal.income_statement.print');
                    Route::get('/receivable-aging-summary', [App\Http\Controllers\JournalController::class, 'receivableAgingSummary'])->name('transaction.receivableAgingSummary');
                    Route::get('/payable-aging-summary', [App\Http\Controllers\JournalController::class, 'payableAgingSummary'])->name('transaction.payableAgingSummary');
                    Route::get('/income-statement-accum', [App\Http\Controllers\JournalController::class, 'showIncomeStatementAccumulated']);
                    Route::get('fetch-income-statement-accumulated', [App\Http\Controllers\JournalController::class, 'fetchIncomeStatementAccumulated'])->name('transaction.journal.fetchIncomeStatementAccumulated');
                });
            });
            Route::prefix('report')->group(function () {
                Route::middleware(['role:inventory_report'])->group(function () {
                    Route::get('/inventory',[\App\Http\Controllers\InventoryReportController::class,'index'])->name('report.inventory');
                    Route::post('/inventory/search',[\App\Http\Controllers\InventoryReportController::class,'search'])->name('report.inventory.search');

                    Route::get('/recap-inventory',[\App\Http\Controllers\RecapInventoryReportController::class,'index'])->name('report.recap-inventory');
                    Route::post('/recap-inventory/search',[\App\Http\Controllers\RecapInventoryReportController::class,'search'])->name('report.recap-inventory.search');
                });
                Route::get('/ppn-out',[\App\Http\Controllers\TaxReportController::class,'taxOut'])->name('report.ppn_out');
                Route::post('/ppn-out/search',[\App\Http\Controllers\TaxReportController::class,'searchOut'])->name('report.ppn_out.search');
                Route::get('/ppn-in',[\App\Http\Controllers\TaxReportController::class,'taxIn'])->name('report.ppn_in');
                Route::post('/ppn-in/search',[\App\Http\Controllers\TaxReportController::class,'searchIn'])->name('report.ppn_in.search');
                Route::get('/ppn-out/xml',[\App\Http\Controllers\TaxReportController::class,'xmlIndex'])->name('report.ppn_out.xml');
                Route::get('/ppn-out/xml_file',[\App\Http\Controllers\TaxReportController::class,'exportXML'])->name('report.ppn_out.xml_file');
            });
            Route::middleware(['role:payable_payment'])->group(function () {
                Route::prefix('payable-payment')->group(function () {
                    Route::get('/', [App\Http\Controllers\PayablePaymentController::class, 'index'])->name('transaction.payable_payment');
                    Route::get('/create', [App\Http\Controllers\PayablePaymentController::class, 'create'])->name('transaction.payable_payment.create');
                    Route::post('/store', [App\Http\Controllers\PayablePaymentController::class, 'store'])->name('transaction.payable_payment.store');
                    Route::get('/edit/{id}', [App\Http\Controllers\PayablePaymentController::class, 'edit'])->name('transaction.payable_payment.edit');
                    Route::post('/update/{id}', [App\Http\Controllers\PayablePaymentController::class, 'update'])->name('transaction.payable_payment.update');
                    Route::post('/delete/{id}', [App\Http\Controllers\PayablePaymentController::class, 'destroy'])->name('transaction.payable_payment.destroy');
                    Route::get('/print/{id}', [App\Http\Controllers\PayablePaymentController::class, 'printPDF'])->name('transaction.payable_payment.print');
                });
            });
            Route::middleware(['role:receivable_payment'])->group(function () {
                Route::prefix('receivable-payment')->group(function () {
                    Route::get('/', [App\Http\Controllers\ReceivablePaymentController::class, 'index'])->name('transaction.receivable_payment');
                    Route::get('/create', [App\Http\Controllers\ReceivablePaymentController::class, 'create'])->name('transaction.receivable_payment.create');
                    Route::post('/store', [App\Http\Controllers\ReceivablePaymentController::class, 'store'])->name('transaction.receivable_payment.store');
                    Route::get('/edit/{id}', [App\Http\Controllers\ReceivablePaymentController::class, 'edit'])->name('transaction.receivable_payment.edit');
                    Route::put('/update/{id}', [App\Http\Controllers\ReceivablePaymentController::class, 'update'])->name('transaction.receivable_payment.update');
                    Route::post('/delete/{id}', [App\Http\Controllers\ReceivablePaymentController::class, 'destroy'])->name('transaction.receivable_payment.destroy');
                    Route::get('/print/{id}', [App\Http\Controllers\ReceivablePaymentController::class, 'printPDF'])->name('transaction.receivable_payment.print');
                });
            });

                Route::prefix('sales-debt-credit-notes')->group(function () {
                    Route::get('/debit', [App\Http\Controllers\SalesDebtCreditNoteController::class, 'index'])
                        ->name('transaction.sales_debt_credit_notes.index');
                    Route::get('/credit', [App\Http\Controllers\SalesDebtCreditNoteController::class, 'index1'])
                        ->name('transaction.sales_debt_credit_notes.index1');
                    Route::get('/create', [App\Http\Controllers\SalesDebtCreditNoteController::class, 'create'])
                        ->name('transaction.sales_debt_credit_notes.create');
                    Route::post('/store', [App\Http\Controllers\SalesDebtCreditNoteController::class, 'store'])
                        ->name('transaction.sales_debt_credit_notes.store');
                    Route::get('/{id}/edit', [App\Http\Controllers\SalesDebtCreditNoteController::class, 'edit'])
                        ->name('transaction.sales_debt_credit_notes.edit');
                    Route::put('/{id}', [App\Http\Controllers\SalesDebtCreditNoteController::class, 'update'])
                        ->name('transaction.sales_debt_credit_notes.update');
                    Route::delete('/{id}', [App\Http\Controllers\SalesDebtCreditNoteController::class, 'destroy'])
                        ->name('transaction.sales_debt_credit_notes.destroy');
                });
                Route::prefix('purchase-debt-credit-notes')->group(function () {
                    Route::get('/debit', [App\Http\Controllers\PurchaseDebtCreditNoteController::class, 'index'])
                        ->name('transaction.purchase_debt_credit_notes.index');
                    Route::get('/credit', [App\Http\Controllers\PurchaseDebtCreditNoteController::class, 'index1'])
                        ->name('transaction.purchase_debt_credit_notes.index1');
                    Route::get('/create', [App\Http\Controllers\PurchaseDebtCreditNoteController::class, 'create'])
                        ->name('transaction.purchase_debt_credit_notes.create');
                    Route::post('/store', [App\Http\Controllers\PurchaseDebtCreditNoteController::class, 'store'])
                        ->name('transaction.purchase_debt_credit_notes.store');
                    Route::get('/{id}/edit', [App\Http\Controllers\PurchaseDebtCreditNoteController::class, 'edit'])
                        ->name('transaction.purchase_debt_credit_notes.edit');
                    Route::put('/{id}', [App\Http\Controllers\PurchaseDebtCreditNoteController::class, 'update'])
                        ->name('transaction.purchase_debt_credit_notes.update');
                    Route::delete('/{id}', [App\Http\Controllers\PurchaseDebtCreditNoteController::class, 'destroy'])
                        ->name('transaction.purchase_debt_credit_notes.destroy');
                });

        });
        // Route::prefix('pos')->group(function () {
        //     // POS Routes
        //     Route::get('/', [App\Http\Controllers\POSController::class, 'index'])->name('pos.index');
        //     Route::post('/save-transaction', [App\Http\Controllers\POSController::class, 'store'])->name('pos.saveTransaction');
        //     Route::get('/pos-list', [App\Http\Controllers\POSController::class, 'postList'])->name('transaction.pos.list');
        //     Route::get('/receipt/{posId}', [App\Http\Controllers\POSController::class, 'posReceipt'])->name('pos.receipt');
        //     Route::get('/print-receipt/{posId}', [App\Http\Controllers\POSController::class, 'printReceipt'])->name('pos.printReceipt');
        //     Route::get('/customer', [App\Http\Controllers\POSController::class, 'showCustomerPage'])->name('transaction.customer');
        //     // Hold Order Routes
        //     Route::prefix('hold-orders')->group(function () {
        //         Route::get('/', [App\Http\Controllers\HoldOrderController::class, 'index'])->name('holdOrders.index');
        //         Route::post(
        //             '/create',
        //             [App\Http\Controllers\HoldOrderController::class, 'store']
        //         )->name('holdOrders.store');
        //         Route::get('/{id}', [App\Http\Controllers\HoldOrderController::class, 'show'])->name('holdOrders.show');
        //         Route::put('/{id}', [App\Http\Controllers\HoldOrderController::class, 'update'])->name('holdOrders.update');
        //         Route::delete(
        //             '/{id}',
        //             [App\Http\Controllers\HoldOrderController::class, 'destroy']
        //         )->name('holdOrders.destroy');
        //     });
        // });
    });
});
