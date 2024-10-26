<?php
namespace App\Providers;
use Illuminate\Support\ServiceProvider;

use App\Interfaces\BusinessRepositoryInterface;
use App\Interfaces\ClassesRepositoryInterface;
use App\Interfaces\ClientRepositoryInterface;
use App\Interfaces\CombinationsRepositoryInterface;
use App\Interfaces\CorrelativeRepositoryInterface;
use App\Interfaces\CorrelativeStoreRepositoryInterface;
use App\Interfaces\DocumentSalesRepositoryInterface;
use App\Interfaces\EmployeeRepositoryInterface;
use App\Interfaces\HeadquartersRepositoryInterface;
use App\Interfaces\MaterialsRepositoryInterface;
use App\Interfaces\ProductsRepositoryInterface;
use App\Interfaces\ProductsxStoreRepositoryInterface;
use App\Interfaces\QuotationRepositoryInterface;
use App\Interfaces\ReplenishmentsRepositoryInterface;
use App\Interfaces\RolesRepositoryInterface;
use App\Interfaces\StoreHouseRepositoryInterface;
use App\Interfaces\StoreHousexStoreRepositoryInterface;
use App\Interfaces\StoreRepositoryInterface;
use App\Interfaces\SubClassesRepositoryInterface;
use App\Interfaces\SubTypeRepositoryInterface;
use App\Interfaces\SuppilerRepositoryInterface;
use App\Interfaces\TypeDocumentRepositoryInterface;
use App\Interfaces\TypeManufacturingRepositoryInterface;
use App\Interfaces\TypeRepositoryInterface;
use App\Models\SubType;
use App\Repositories\BusinessRepository;
use App\Repositories\ClassesRepository;
use App\Repositories\ClientRepository;
use App\Repositories\CombinationsRepository;
use App\Repositories\CorrelativeRepository;
use App\Repositories\CorrelativeStoreRepository;
use App\Repositories\DocumentSalesRepository;
use App\Repositories\EmployeeRepository;
use App\Repositories\HeadquartersRepository;
use App\Repositories\MaterialsRepository;
use App\Repositories\ProductsRepository;
use App\Repositories\ProductsxStoreRepository;
use App\Repositories\QuotationRepository;
use App\Repositories\ReplenishmentsRepository;
use App\Repositories\RolesRepository;
use App\Repositories\StoreHouseRepository;
use App\Repositories\StoreHousexStoreRepository;
use App\Repositories\StoreRepository;
use App\Repositories\SubClassesRepository;
use App\Repositories\SubTypeRepository;
use App\Repositories\SuppilerRepository;
use App\Repositories\TypeDocumentRepository;
use App\Repositories\TypeManufacturingRepository;
use App\Repositories\TypeRepository;
use League\CommonMark\Node\Block\Document;

class RepositoryServiceProvider extends ServiceProvider{
    public function register(){
        $this->app->bind(BusinessRepositoryInterface::class,BusinessRepository::class);
        $this->app->bind(HeadquartersRepositoryInterface::class,HeadquartersRepository::class);
        $this->app->bind(StoreRepositoryInterface::class,StoreRepository::class);
        $this->app->bind(TypeDocumentRepositoryInterface::class,TypeDocumentRepository::class);
        $this->app->bind(DocumentSalesRepositoryInterface::class,DocumentSalesRepository::class);
        $this->app->bind(RolesRepositoryInterface::class,RolesRepository::class);
        $this->app->bind(EmployeeRepositoryInterface::class,EmployeeRepository::class);
        $this->app->bind(SuppilerRepositoryInterface::class,SuppilerRepository::class);
        $this->app->bind(ClientRepositoryInterface::class,ClientRepository::class);
        $this->app->bind(CorrelativeRepositoryInterface::class,CorrelativeRepository::class);
        $this->app->bind(CorrelativeStoreRepositoryInterface::class,CorrelativeStoreRepository::class);
        $this->app->bind(TypeManufacturingRepositoryInterface::class,TypeManufacturingRepository::class);
        $this->app->bind(ProductsRepositoryInterface::class,ProductsRepository::class);
        $this->app->bind(ProductsxStoreRepositoryInterface::class,ProductsxStoreRepository::class);
        $this->app->bind(CombinationsRepositoryInterface::class,CombinationsRepository::class);
        $this->app->bind(QuotationRepositoryInterface::class,QuotationRepository::class);
        $this->app->bind(ClassesRepositoryInterface::class,ClassesRepository::class);
        $this->app->bind(SubClassesRepositoryInterface::class,SubClassesRepository::class);
        $this->app->bind(MaterialsRepositoryInterface::class,MaterialsRepository::class);
        $this->app->bind(SubTypeRepositoryInterface::class,SubTypeRepository::class);
        $this->app->bind(TypeRepositoryInterface::class,TypeRepository::class);
        $this->app->bind(StoreHouseRepositoryInterface::class,StoreHouseRepository::class);
        $this->app->bind(StoreHousexStoreRepositoryInterface::class,StoreHousexStoreRepository::class);
        $this->app->bind(ReplenishmentsRepositoryInterface::class,ReplenishmentsRepository::class);
    }    
}
?>