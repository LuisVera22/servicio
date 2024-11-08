<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\ChangePasswordController;
use App\Http\Controllers\ClassesController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CombinationsController;
use App\Http\Controllers\CorrelativeController;
use App\Http\Controllers\CorrelativeStoreController;
use App\Http\Controllers\DocumentSalesController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\HeadquartersController;
use App\Http\Controllers\MainWarehouseReceptionController;
use App\Http\Controllers\MaterialsController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\PettyCashController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\QuotationReasonController;
use App\Http\Controllers\ReplenishmentsController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\StoreHouseController;
use App\Http\Controllers\StoreHousexStoreController as ControllersStoreHousexStoreController;
use App\Http\Controllers\SubClassesController;
use App\Http\Controllers\SubTypeController;
use App\Http\Controllers\SuppilerController;
use App\Http\Controllers\TypeController;
use App\Http\Controllers\TypeDocumentController;
use App\Http\Controllers\TypeManufacturingController;
use App\Http\Controllers\StoreHousexStoreController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('auth/register', [AuthController::class, 'create']);
Route::post('auth/login', [AuthController::class, 'login']);
Route::middleware(['auth:sanctum'])->group(function () {

    Route::resource("pettycash",PettyCashController::class);

    Route::put('businessImage', [BusinessController::class, 'updateImage']);
    Route::resource('business', BusinessController::class);

    Route::resource('headquarters', HeadquartersController::class);
    Route::get('headquarters/list/activos', [HeadquartersController::class, 'listAllActivos']);

    Route::resource('stores', StoreController::class);
    Route::get('stores/list/activos', [StoreController::class, 'listAllActivos']);

    Route::resource('typedocuments', TypeDocumentController::class);
    Route::get('typedocuments/list/activos', [TypeDocumentController::class, 'listAllActivos']);

    Route::resource('documentsales', DocumentSalesController::class);
    Route::get('documentsales/list/activos', [DocumentSalesController::class, 'listAllActivos']);
    Route::get('documentsales/correlative/{Id}', [DocumentSalesController::class, 'DocumentSalesxCorrelative']);

    Route::resource('roles', RolesController::class);
    Route::get('roles/list/activos', [RolesController::class, 'listAllActivos']);

    Route::resource('employees', EmployeeController::class);
    Route::get('employees/rolActivos/{name}', [EmployeeController::class, 'listEmployeeRolActivos']);

    Route::resource('correlatives', CorrelativeController::class);

    Route::resource('correlativestore', CorrelativeStoreController::class);

    Route::resource('clients', ClientController::class);
    Route::get('clients/list/activos', [ClientController::class, 'listAllActivos']);

    Route::resource('typemanufactures', TypeManufacturingController::class);
    Route::get('typemanufactures/list/activos', [TypeManufacturingController::class, 'listAllActivos']);
    Route::get('typemanufactures/job/activos/{job}', [TypeManufacturingController::class, 'listManufacturingxJobActivos']);


    Route::get('auth/logout', [AuthController::class, 'logout']);

    Route::resource('suppiler', SuppilerController::class);
    Route::delete('suppiler/{id}', [SuppilerController::class, 'destroy']);
    Route::get('suppileractivos', [SuppilerController::class, 'listAllActivos']);

    Route::resource('type', TypeController::class);
    Route::get('type/list/activos', [TypeController::class, 'listAllActivos']);
    Route::get('type/manufacturing/activos/{id}', [TypeController::class, 'listManufxTipoActivos']);


    Route::resource('subtype', SubTypeController::class);
    Route::get('subtype/list/activos', [SubTypeController::class, 'listAllActivos']);
    Route::get('subtype/type/activos/{id}', [SubTypeController::class, 'listTypexSubtypeActivos']);

    Route::resource('materials', MaterialsController::class);
    Route::get('materials/list/activos', [MaterialsController::class, 'listAllActivos']);
    Route::get('materials/type/activos/{id}', [MaterialsController::class, 'listTypexMaterialActivos']);
    Route::get('materials/subtype/activos/{id}', [MaterialsController::class, 'listSubTypexMaterialActivos']);

    Route::resource('classes', ClassesController::class);
    Route::get('classes/list/activos', [ClassesController::class, 'listAllActivos']);
    Route::get('classes/materials/activos/{id}', [ClassesController::class, 'listClassexMaterialActivos']);

    Route::resource('subclasses', SubClassesController::class);
    Route::get('subclasses/list/activos', [SubClassesController::class, 'listAllActivos']);
    Route::get('subclasses/classes/activos/{id}', [SubClassesController::class, 'listSubClassexClasseActivos']);

    Route::resource('products', ProductsController::class);
    Route::get('products/category/{category}', [ProductsController::class, 'indexByCategory']);
    Route::get('products/service/{category}', [ProductsController::class, 'listServicesActivos']);    
    Route::get('products/manufacturing/activos/{id}', [ProductsController::class, 'listProductsxManufacturingActivos']);

    Route::resource('combinations', CombinationsController::class);
    Route::get('combinations/list/activos', [CombinationsController::class, 'listAllActivos']);

    Route::resource('quotation', QuotationController::class);
    Route::get('quotation/list/{id}', [QuotationController::class, 'indexBySede']);
    Route::post('quotation/filterDate', [QuotationController::class, 'filterByDate']);
    Route::post('quotation/checkfacturado', [QuotationController::class, 'checkFacturado']);
    Route::post('quotation/convertsales', [QuotationController::class, 'convertSales']);

    Route::resource('quotationreason', QuotationReasonController::class);

    Route::resource('storehouse', StoreHouseController::class);    
    Route::get('storehouse/manufacturing/activos/{id}', [StoreHouseController::class, 'listStoreHousexManufacturingActivos']);
    Route::get('storehouse/manufacturing/list/{job}', [StoreHouseController::class, 'indexByJob']);
    Route::get('storehouse/manufacturing/list/activos/{idManfucture}/{idstore}', [StoreHousexStoreController::class, 'listStoreHousexManufacturing']);
    Route::get('storehouse/export/{id}',[StoreHouseController::class,'exportExcel']);
    
    Route::resource('storehousexstore',StoreHousexStoreController::class);
    
    Route::resource('replenishments',ReplenishmentsController::class);
    Route::get('replenishments/manufacturing/list/activos/{idManfucture}/{idstore}',[ReplenishmentsController::class,'listReplenishmentsxManufacturing']);
    Route::get('replenishments/manufacturing/activos/{id}', [ReplenishmentsController::class, 'listReplenishmentsxManufacturingActivos']);

    Route::resource('mainwarehousereception', MainWarehouseReceptionController::class);

});
Route::resource('changepassword', ChangePasswordController::class );