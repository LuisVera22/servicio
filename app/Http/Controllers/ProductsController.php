<?php

namespace App\Http\Controllers;

use App\Interfaces\ProductsRepositoryInterface;
use App\Interfaces\ProductsxStoreRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use App\Models\Products;
use App\Models\ProductsxStore;

class ProductsController
{
    private $repository;
    private $repositoryPxS;

    public function __construct(
        ProductsxStoreRepositoryInterface $repositoryPxS,
        ProductsRepositoryInterface $repository,
    ) {
        $this->repositoryPxS = $repositoryPxS;
        $this->repository = $repository;
    }

    public function indexByCategory($category)
    {
        $product = Products::with(['productsxstore'])
            ->where('category', $category)
            ->orderBy('id', 'desc')
            ->get();
        return response()->json($product);
    }
    public function show(Products $product)
    {
        $product->load([
            'productsxstore'
        ]);
        return response()->json(['status'   => true, 'data' => $product]);
    }
    public function store(Request $request)
    {
        $rules = [
            'description'   =>  'required|string',
            'price'         =>  'required|numeric'
        ];

        $messages = [
            'description.required'  =>  'El campo descripción es obligatorio.',
            'description.string'    =>  'El campo descripción debe ser una cadena de texto.',
            'price.required'        =>  'El campo precio es obligatorio.',
            'price.numeric'         =>  'El campo precio debe ser un campo numérico.',
        ];

        try {
            if ($this->isDatabaseDown()) {
                if ($this->isAppDownForMaintenance()) {
                    return response()->json([
                        'status' => false,
                        'errors' => ['mantenimiento.'],
                        'message' => 'La aplicación esta en mantenimiento. Inténtalo de nuevo más tarde.'
                    ], 503);
                } else {
                    $validator = Validator::make($request->input(), $rules, $messages);
                    if ($validator->fails()) {
                        return response()->json([
                            'status'    => false,
                            'alert'     => 'rules',
                            'errors'    => $validator->errors()->all()
                        ], 400);
                    } else {
                        try {
                            DB::beginTransaction();

                            if ($request['category'] == 'services') {
                                $products = new Products($request->only([
                                    'description',
                                    'category',
                                    'price',
                                    'idtype',
                                    'idsubtype',
                                    'idmaterial',
                                    'idclasses',
                                    'idsubclasses',
                                    'idcombinations',
                                    'idtype_manufacturing'
                                ]));

                                $products->save();
                                $repeatProductxStore = $this->repositoryPxS->repeatProductxStore($request['description'], null, null, null, null, null, $request['idtype_manufacturing'], $request->input('idStore'));

                                if (count($repeatProductxStore) > 0) {
                                    DB::rollBack();
                                    return response()->json([
                                        'status'    =>  true,
                                        'message'   =>  'El valor ya existe en la base de datos.'
                                    ], 409);
                                } else {
                                    $productxstore = new ProductsxStore([
                                        'idstore'       => $request->input('idstore'),
                                    ]);
                                    $products->productsxstore()->save($productxstore);
                                }
                            } else if ($request['category'] == 'products') {
                                $descriptionData = $this->repository->generateDescription($request);
                                $productsrepeat = $this->repository->repeatProduct($descriptionData['description'], $descriptionData['abrevmaterials'], $descriptionData['abrevtype'], $descriptionData['abrevsubtype'], $descriptionData['abrevclasses'], $descriptionData['abrevsubclasses'], $request['idtype_manufacturing']);

                                if (count($productsrepeat) > 0) {
                                    DB::rollBack();
                                    return response()->json([
                                        'status'    =>  true,
                                        'message'   =>  'El valor ya existe en la base de datos.'
                                    ], 409);
                                } else {
                                    $products = Products::create([
                                        'description'           => $descriptionData['description'],
                                        'category'              => $request->category,
                                        'price'                 => $request->price,
                                        'abrevtype'             => $descriptionData['abrevtype'],
                                        'abrevsubtype'          => $descriptionData['abrevsubtype'],
                                        'abrevmaterials'        => $descriptionData['abrevmaterials'],
                                        'abrevclasses'          => $descriptionData['abrevclasses'],
                                        'abrevsubclasses'       => $descriptionData['abrevsubclasses'],
                                        'idtype_manufacturing'  => $request->idtype_manufacturing
                                    ]);
                                }
                            }
                            DB::commit();
                            return response()->json([
                                'status'    =>  true,
                                'message'   =>  'Registro Generado.'
                            ], 201);
                        } catch (\Exception $e) {
                            // Revertir la transacción si hay un error
                            DB::rollBack();
                            return response()->json([
                                'status'    => false,
                                'errors'    => ['Error al crear el registro.'],
                                'message'   => 'Error: ' . $e->getMessage(),
                            ], 500);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'errors' => ['No se pudo conectar a la base de datos'],
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
    public function update(Request $request, Products $product)
    {
        $rules = [
            'price'   =>  'required|numeric'
        ];

        $messages = [
            'price.required'        =>  'El campo precio es obligatorio.',
            'price.numeric'         =>  'El campo precio debe ser un campo numérico.',
        ];
        try {
            if ($this->isDatabaseDown()) {
                if ($this->isAppDownForMaintenance()) {
                    return response()->json([
                        'status' => false,
                        'errors' => ['mantenimiento.'],
                        'message' => 'La aplicación esta en mantenimiento. Inténtalo de nuevo más tarde.'
                    ], 503);
                } else {
                    $validator = Validator::make($request->input(), $rules, $messages);
                    if ($validator->fails()) {
                        return response()->json([
                            'status'    => false,
                            'alert'     => 'rules',
                            'errors'    => $validator->errors()->all()
                        ], 400);
                    } else {
                        try {
                            DB::beginTransaction();
                            $products = Products::findOrFail($product->id);

                            if ($request['category'] == 'services') {
                                $products->update($request->only([
                                    'description',
                                    'category',
                                    'price',
                                    'idtype',
                                    'idsubtype',
                                    'idmaterial',
                                    'idclasses',
                                    'idsubclasses',
                                    'idcombinations',
                                    'idtype_manufacturing'
                                ]));

                                $PxS = $product->productsxstore;
                                $repeatProductxStoreUpdate = $this->repositoryPxS->repeatProductxStoreUpdate(null, $request['description'], $request['price'], $request['idstore'], $product->id);
                                if (count($repeatProductxStoreUpdate) > 0) {
                                    DB::rollBack();
                                    return response()->json([
                                        'status'    =>  true,
                                        'message'   =>  'El valor ya existe en la base de datos.'
                                    ], 409);
                                } else {
                                    $PxS->update([
                                        'idstore'       => $request->input('idstore'),
                                    ]);
                                }
                            } else if ($request['category'] == 'products') {
                                $products->update([
                                    'price'   =>  $request->input('price'),
                                ]);
                            }
                            DB::commit();
                            return response()->json([
                                'status'    =>  true,
                                'message'   =>  'Actualización exitosa.'
                            ], 201);
                        } catch (\Exception $e) {
                            // Revertir la transacción si hay un error
                            DB::rollBack();
                            return response()->json([
                                'status'    => false,
                                'errors'    => ['Error al crear el registro.'],
                                'message'   => 'Error: ' . $e->getMessage(),
                            ], 500);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'errors' => ['No se pudo conectar a la base de datos'],
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
    public function destroy($id, Request $request)
    {
        try {
            if ($this->isDatabaseDown()) {
                if ($this->isAppDownForMaintenance()) {
                    return response()->json([
                        'status' => false,
                        'errors' => ['mantenimiento.'],
                        'message' => 'La aplicación esta en mantenimiento. Inténtalo de nuevo más tarde.'
                    ], 503);
                } else {
                    $response = $this->repository->delete($id, $request['enabled']);
                    if (isset($response) && $response == 1) {
                        return response()->json([
                            'status'    =>  true,
                            'message'   =>  'Eliminación exitosa'
                        ], 200);
                    } else {
                        return response()->json([
                            'status'    =>  false,
                            'message'   =>  'Error al eliminar'
                        ], 500);
                    }
                }
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'errors' => ['No se pudo conectar a la base de datos'],
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
    public function listServicesActivos($category)
    {
        $client = Products::with(['productsxstore'])
            ->where(
                [
                    ['category', $category],
                    ['enabled', 1]
                ]
            )
            ->orderBy('id', 'desc')
            ->get();
        return response()->json($client, 200);
    }
    public function listProductsxManufacturingActivos($id)
    {
        $client = Products::select('*')
            ->where(
                [
                    ['enabled', 1],
                    ['idtype_manufacturing', $id],
                ]
            )
            ->orderBy('id', 'desc')
            ->get();
        return response()->json($client, 200);
    }
    private function isAppDownForMaintenance()
    {
        return app()->isDownForMaintenance();
    }
    private function isDatabaseDown()
    {
        try {
            // Intentar ejecutar una consulta de prueba
            DB::select('SELECT 1');

            return true;
        } catch (\Exception $e) {
            // Cualquier excepción, considerar que la base de datos está inaccesible
            return $e;
        }
    }
}
