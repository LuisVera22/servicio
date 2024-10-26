<?php

namespace App\Http\Controllers;

use App\Interfaces\QuotationRepositoryInterface;
use App\Models\Laboratory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use App\Models\Quotation;
use App\Models\QuotationCuotas;
use App\Models\QuotationDetail;
use Illuminate\Support\Facades\Auth;

class QuotationController
{
    private $repository;

    public function __construct(
        QuotationRepositoryInterface $repository
    ) {
        $this->repository = $repository;
    }

    public function indexBySede($id)
    {
        $fechaInicio = date('Y-m-01');
        $fechaFin = date('Y-m-d');

        if ($id === "0") {
            $quotation = Quotation::with(['store', 'courier', 'vendor', 'typedocument', 'typemanufacturing'])
                ->whereBetween('date_issue', [$fechaInicio, $fechaFin])                
                ->orderBy('id', 'desc')
                ->get();
        } else {
            $quotation = Quotation::with(['store', 'courier', 'vendor', 'typedocument', 'typemanufacturing'])
                ->whereBetween('date_issue', [$fechaInicio, $fechaFin])
                ->where('idstore', $id)
                ->orderBy('id', 'desc')
                ->get();
        }

        return response()->json($quotation);
    }
    public function show(Quotation $quotation)
    {
        $quotation->load(['quotationdetail', 'quotationcuotas', 'store', 'courier', 'vendor', 'typedocument', 'typemanufacturing']);
        return response()->json(['status'   => true, 'data' => $quotation]);
    }
    public function store(Request $request)
    {
        $rules = [
            'client'                        => 'required|string',
            'number_document'               => 'required|string',
            'date_issue'                    => 'required',
            'subtotal'                      => 'required',
            'igv'                           => 'required',
            'total'                         => 'required',
            'adelanto'                      => 'required',
            'saldo'                         => 'required',
            'forma_pago'                    => 'required',
            'selectedItems'                 => 'required|array'
        ];
        $messages = [
            'client.required'           =>  'El campo cliente es obligatorio.',
            'client.string'             =>  'El campo cliente debe ser una cadena de texto.',
            'number_document.required'  =>  'El campo número documento es obligatorio.',
            'date_issue.required'       =>  'El campo Fecha Emisión es obligatorio.',
            'delivery_time.required'    =>  'El campo Tiempo Entrega es obligatorio.',
            'forma_pago.required'       =>  'El campo forma pago es obligatorio.',
            'subtotal.required'         =>  'El campo subtotal es obligatorio.',
            'igv.required'              =>  'El campo igv es obligatorio.',
            'total.required'            =>  'El campo total es obligatorio.',
            'adelanto.required'         =>  'El campo adelanto es obligatorio.',
            'saldo.required'            =>  'El campo saldo es obligatorio.',
            'selectedItems.required'    =>  'No existe productos ingresados.',
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
                        DB::beginTransaction();
                        try {
                            $username = Auth::user()->username;
                            $newCode = $this->repository->codeQuotation();
                            $quotation = new Quotation([
                                'codigo'                => $newCode,
                                'number_document'       => $request['number_document'],
                                'client'                => $request['client'],
                                'address_client'        => $request['address_client'],
                                'date_issue'            => $request['date_issue'],
                                'delivery_time'         => $request['delivery_time'],
                                'forma_pago'            => $request['forma_pago'],
                                'subtotal'              => $request['subtotal'],
                                'igv'                   => $request['igv'],
                                'total'                 => $request['total'],
                                'adelanto'              => $request['adelanto'],
                                'saldo'                 => $request['saldo'],
                                'pendiente_pago'        => $request['pendiente_pago'],
                                'notes'                 => $request['notes'],
                                'descripciontotal'      => $request['descripciontotal'],
                                'user_add'              => $username,
                                'idstore'               => $request['idstore'],
                                'idtype_document'       => $request['idtype_document'],
                                'idcourier'             => $request['idcourier'],
                                'idvendor'              => $request['idvendor'],
                                'idtype_manufacturing'  => $request['idtype_manufacturing'],
                            ]);

                            $quotation->save();

                            // Mapear commonId a los índices de selectedItems
                            $commonIdMap = collect($request->selectedItems)->mapWithKeys(function ($item, $index) {
                                return isset($item['commonId']) ? [$item['commonId'] => $index] : [];
                            });

                            // Crear los detalles de la cotización
                            $quotationDetails = [];
                            foreach ($request->selectedItems as $key => $item) {
                                /* $quotationDetail = QuotationDetail::create([
                                    'description'           => $item['description'],
                                    'manufacturing'         => isset($item['manufacturing']) ? $item['manufacturing'] : null,
                                    'detail_manufacturing'  => $item['detail_manufacturing'],
                                    'unidadmedida'          => $item['unidadmedida'],
                                    'quantity'              => $item['quantity'],
                                    'price'                 => $item['price'],
                                    'discount'              => $item['discount'],
                                    'pricesinigv'           => $item['pricesinigv'],
                                    'subtotal'              => $item['subtotal'],
                                    'igv'                   => $item['igv'],
                                    'total'                 => $item['total'],
                                    'material'              => isset($item['material']) ? $item['material'] : null,
                                    'modelo'                => isset($item['modelo']) ? $item['modelo'] : null,
                                    'condicion'             => isset($item['condicion']) ? $item['condicion'] : null,
                                    'color'                 => isset($item['color']) ? $item['color'] : null,
                                    'marca'                 => isset($item['marca']) ? $item['color'] : null,
                                    'idquotation'           => $quotation->id,
                                    'idproducts'            => $item['idproducts']
                                ]); */
                                $quotationDetail = QuotationDetail::create(array_merge($item, ['idquotation' => $quotation->id]));
                                // Almacenar en un array para acceder fácilmente por índice
                                $quotationDetails[$key] = $quotationDetail;
                            }

                            if (!empty($request->selectedLaboratory)) {
                                foreach ($request->selectedLaboratory as $laboratoryItem) {
                                    if (isset($laboratoryItem['commonId']) && !is_null($laboratoryItem['commonId']) && isset($commonIdMap[$laboratoryItem['commonId']])) {
                                        $quotationDetailIndex = $commonIdMap[$laboratoryItem['commonId']];
                                        $quotationDetail = $quotationDetails[$quotationDetailIndex];
                                        /* Laboratory::create([
                                            'esfod'             => isset($laboratoryItem['esfod']) ? $laboratoryItem['esfod'] : null,
                                            'cylod'             => isset($laboratoryItem['cylod']) ? $laboratoryItem['cylod'] : null,
                                            'addod'             => isset($laboratoryItem['addod']) ? $laboratoryItem['addod'] : null,
                                            'ejeod'             => isset($laboratoryItem['ejeod']) ? $laboratoryItem['ejeod'] : null,
                                            'prismaod'          => isset($laboratoryItem['prismaod']) ? $laboratoryItem['prismaod'] : null,
                                            'altod'             => isset($laboratoryItem['altod']) ? $laboratoryItem['altod'] : null,
                                            'dipod'             => isset($laboratoryItem['dipod']) ? $laboratoryItem['dipod'] : null,
                                            'diametrood'        => isset($laboratoryItem['diametrood']) ? $laboratoryItem['diametrood'] : null,
                                            'esfoi'             => isset($laboratoryItem['esfoi']) ? $laboratoryItem['esfoi'] : null,
                                            'cyloi'             => isset($laboratoryItem['cyloi']) ? $laboratoryItem['cyloi'] : null,
                                            'addoi'             => isset($laboratoryItem['addoi']) ? $laboratoryItem['addoi'] : null,
                                            'ejeoi'             => isset($laboratoryItem['ejeoi']) ? $laboratoryItem['ejeoi'] : null,
                                            'prismaoi'          => isset($laboratoryItem['prismaoi']) ? $laboratoryItem['prismaoi'] : null,
                                            'altoi'             => isset($laboratoryItem['altoi']) ? $laboratoryItem['altoi'] : null,
                                            'dipoi'             => isset($laboratoryItem['dipoi']) ? $laboratoryItem['dipoi'] : null,
                                            'diametrooi'        => isset($laboratoryItem['diametrooi']) ? $laboratoryItem['diametrooi'] : null,
                                            'v'                 => isset($laboratoryItem['v']) ? $laboratoryItem['v'] : null,
                                            'h'                 => isset($laboratoryItem['h']) ? $laboratoryItem['h'] : null,
                                            'd'                 => isset($laboratoryItem['d']) ? $laboratoryItem['d'] : null,
                                            'pte'               => isset($laboratoryItem['pte']) ? $laboratoryItem['pte'] : null,
                                            'alt'               => isset($laboratoryItem['alt']) ? $laboratoryItem['alt'] : null,
                                            'dip'               => isset($laboratoryItem['dip']) ? $laboratoryItem['dip'] : null,
                                            'inicialespaciente' => isset($laboratoryItem['inicialespaciente']) ? $laboratoryItem['inicialespaciente'] : null,
                                            'diametro'          => isset($laboratoryItem['diametro']) ? $laboratoryItem['diametro'] : null,
                                            'corredor'          => isset($laboratoryItem['corredor']) ? $laboratoryItem['corredor'] : null,
                                            'reduccion'         => isset($laboratoryItem['reduccion']) ? $laboratoryItem['reduccion'] : null,
                                            'idquotationdetail' => $quotationDetail->id,
                                        ]); */
                                        Laboratory::create(array_merge($laboratoryItem, [
                                            'idquotationdetail' => $quotationDetail->id,
                                        ]));
                                    }
                                }
                            }

                            if (!empty($request->selectedCuotas)) {
                                foreach ($request->selectedCuotas as $itemsCuotas) {

                                    /* QuotationCuotas::create([
                                        'monto'         => $itemsCuotas['monto'],
                                        'fecha'         => $itemsCuotas['fecha'],
                                        'idquotation'   => $quotation->id
                                    ]); */
                                    QuotationCuotas::create(array_merge($itemsCuotas, [
                                        'idquotation' => $quotation->id
                                    ]));
                                }
                            }

                            // Confirmar la transacción
                            DB::commit();
                            return response()->json([
                                'status' => true,
                                'message' => 'Registro Generado.'
                            ], 200);
                        } catch (\Exception $e) {
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
    public function update(Request $request, Quotation $quotation)
    {
        $rules = [
            'client'                        => 'required|string',
            'number_document'               => 'required|string',
            'date_issue'                    => 'required',
            'subtotal'                      => 'required',
            'igv'                           => 'required',
            'total'                         => 'required',
            'adelanto'                      => 'required',
            'saldo'                         => 'required',
            'forma_pago'                    => 'required',
            'selectedItems'                 => 'required|array'
        ];
        $messages = [
            'client.required'           =>  'El campo cliente es obligatorio.',
            'client.string'             =>  'El campo cliente debe ser una cadena de texto.',
            'number_document.required'  =>  'El campo número documento es obligatorio.',
            'date_issue.required'       =>  'El campo Fecha Emisión es obligatorio.',
            'delivery_time.required'    =>  'El campo Tiempo Entrega es obligatorio.',
            'forma_pago.required'       =>  'El campo forma pago es obligatorio.',
            'subtotal.required'         =>  'El campo subtotal es obligatorio.',
            'igv.required'              =>  'El campo igv es obligatorio.',
            'total.required'            =>  'El campo total es obligatorio.',
            'adelanto.required'         =>  'El campo adelanto es obligatorio.',
            'saldo.required'            =>  'El campo saldo es obligatorio.',
            'selectedItems.required'    =>  'No existe productos ingresados.',
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
                            $quotations = Quotation::findOrFail($quotation->id);

                            $quotations->update($request->only([
                                'number_document',
                                'client',
                                'address_client',
                                'date_issue',
                                'delivery_time',
                                'forma_pago',
                                'subtotal',
                                'igv',
                                'total',
                                'adelanto',
                                'saldo',
                                'pendiente_pago',
                                'notes',
                                'descripciontotal',
                                'user_add',
                                'idstore',
                                'idtype_document',
                                'idcourier',
                                'idvendor',
                                'idtype_manufacturing'
                            ]));

                            // Mapear commonId a los índices de selectedItems
                            $commonIdMap = collect($request->selectedItems)->mapWithKeys(function ($item, $index) {
                                return isset($item['commonId']) ? [$item['commonId'] => $index] : [];
                            });

                            // Cargar los detalles de la cotización actualizados
                            //
                            $currentDetails = $quotation->quotationdetail()->get();

                            $quotationDetails = [];
                            foreach ($request->selectedItems as $key => $item) {
                                $detail = null;

                                if (!is_null($item['idDetail'])) {
                                    $detail = $currentDetails->firstWhere('id', $item['idDetail']);
                                }

                                if ($detail) {
                                    // Actualiza los demás campos según sea necesario
                                    $detail->fill($item)->save();
                                } else {
                                    $quotationDetail = QuotationDetail::create(array_merge($item, ['idquotation' => $quotation->id]));
                                    // Almacenar en un array para acceder fácilmente por índice
                                    $quotationDetails[$key] = $quotationDetail;
                                }
                            }
                            // Eliminar detalles que no están en la solicitud actual
                            $currentDetailIds  = $currentDetails->pluck('id')->toArray();
                            // Obtener los IDs de los detalles que se deben eliminar
                            $idsToDelete = array_diff($currentDetailIds, $request->input('selectedItems.*.idDetail'));
                            QuotationDetail::whereIn('id', $idsToDelete)->delete();

                            if (!empty($request->selectedLaboratory)) {
                                foreach ($request->selectedLaboratory as $laboratoryItem) {

                                    if (isset($laboratoryItem['commonId']) && !is_null($laboratoryItem['commonId']) && isset($commonIdMap[$laboratoryItem['commonId']])) {
                                        $quotationDetailIndex = $commonIdMap[$laboratoryItem['commonId']];
                                        if (isset($quotationDetails[$quotationDetailIndex])) {
                                            $quotationDetail = $quotationDetails[$quotationDetailIndex];
                                            Laboratory::create(array_merge($laboratoryItem, ['idquotationdetail' => $quotationDetail->id]));
                                        }
                                    } else {
                                        // Buscar el detalle de cotización correspondiente usando idDetail
                                        $detail = $currentDetails->firstWhere('id', $laboratoryItem['idDetail']);

                                        if ($detail) {
                                            // Si se encuentra el detalle de cotización correspondiente, actualizar o crear laboratorio
                                            $laboratory = $detail->laboratory ?: new Laboratory(['idquotationdetail' => $detail->id]);
                                            $laboratory->fill($laboratoryItem)->save();
                                        }
                                    }
                                }
                            }

                            if (empty($request->selectedCuotas)) {
                                QuotationCuotas::where('idquotation', $quotation->id)->delete();
                            } else {
                                // Obtener las cuotas actuales e indexarlas por 'id'
                                $currentCuotas = $quotation->quotationcuotas()->get()->keyBy('id');

                                // Array de IDs recibidos en la solicitud
                                $receivedIds = collect($request->selectedCuotas)->pluck('idCuotas')->filter()->all();

                                // Eliminar cuotas que no están en la solicitud actual
                                QuotationCuotas::where('idquotation', $quotation->id)
                                    ->whereNotIn('id', $receivedIds)
                                    ->delete();

                                foreach ($request->selectedCuotas as $item) {
                                    if (isset($item['idCuotas']) && !is_null($item['idCuotas'])) {
                                        // Si existe, actualizar la cuota
                                        $cuota = $currentCuotas->get($item['idCuotas']);
                                        if ($cuota) {
                                            $cuota->update($item);
                                        }
                                    } else {
                                        // Si no existe, crear una nueva cuota
                                        QuotationCuotas::create(array_merge($item, ['idquotation' => $quotation->id]));
                                    }
                                }
                            }

                            // Commit de la transacción
                            DB::commit();

                            return response()->json([
                                'status' => true,
                                'message' => 'Actualización exitosa.',
                            ], 200);
                        } catch (\Exception $e) {
                            DB::rollBack();
                            return response()->json([
                                'status'    => false,
                                'errors'    => ['Error al actualizar el registro.'],
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
                            'message'   =>  'Error al eliminar.'
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
    public function filterByDate(Request $request)
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
                    try {
                        $fechaInicio = $request->input('dateInicio');
                        $fechaFin   = $request->input('dateFin');
                        $store      = $request->input('sede');

                        $quotation = Quotation::with(['store', 'courier', 'vendor', 'typedocument', 'typemanufacturing'])
                            ->whereBetween('date_issue', [$fechaInicio, $fechaFin])
                            ->where('idstore', $store)
                            ->orderBy('id', 'desc')
                            ->get();
                        return response()->json($quotation);
                    } catch (\Exception $e) {
                        return response()->json([
                            'status'    => false,
                            'errors'    => ['Error al realizar el filtro.'],
                            'message'   => 'Error: ' . $e->getMessage(),
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
    public function checkFacturado(Request $request)
    {
        $array_id = $request->input('arr_id');

        $facturados = Quotation::whereIn('id', $array_id)
            ->where('status', 1)
            ->get(['id', 'codigo']);
        return response()->json(['status' => true, 'facturados' => $facturados], 200);
    }
    public function convertSales(Request $request)
    {
        // Obtener los IDs de las cotizaciones seleccionadas
        $ids = $request->input('arr_id');

        // Consultar los detalles de las cotizaciones usando whereIn
        $details = QuotationDetail::whereIn('idquotation', $ids)->get();
        // Consultar las cotizaciones para obtener el cliente y los códigos
        $quotations = Quotation::whereIn('id', $ids)->first();

        $response = [
            'client'                => $quotations->client,
            'idtype_document'       => $quotations->idtype_document,
            'number_document'       => $quotations->number_document,
            'address_client'        => $quotations->address_client,
            'idtype_manufacturing'  => $quotations->idtype_manufacturing,
            'date_issue'            => $quotations->date_issue,
            'delivery_time'         => $quotations->delivery_time,
            'idcourier'             => $quotations->idcourier,
            'idvendor'              => $quotations->idvendor,
            'idstore'               => $quotations->idstore,
            'forma_pago'            => $quotations->forma_pago,
            'details'               => $details
        ];

        return response()->json(['status' => true, 'cliente' => $response], 200);
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
