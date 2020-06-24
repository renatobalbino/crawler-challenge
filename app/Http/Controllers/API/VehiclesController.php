<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Manufacturer;
use App\Models\ManufacturersModels;
use App\Models\Vehicle;
use App\Models\VehiclesAttributes;

class VehiclesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $slug = null)
    {
        $vehicles = $this->fetchVehicles($request, $slug);
        return response()->json($vehicles);
    }

    private function fetchVehicles(Request $request, $slug)
    {
        try {
            $vehiclesTableName = (new Vehicle)->getTable();
            $manufacturerTableName = (new Manufacturer)->getTable();
            $manufacturersModelsTableName = (new ManufacturersModels)->getTable();
            $vehiclesAttributesTableName = (new VehiclesAttributes())->getTable();

            $query = Vehicle::select([
                $vehiclesTableName . '.id',
                $vehiclesTableName . '.name',
                $vehiclesTableName . '.price',
                $vehiclesTableName . '.slug',
                $vehiclesTableName . '.manufacturers_models_id',
            ])
                ->join($manufacturersModelsTableName, function ($join) use ($vehiclesTableName, $manufacturersModelsTableName) {
                    $join->on($manufacturersModelsTableName . '.id', '=', $vehiclesTableName . '.manufacturers_models_id');
                })
                ->join($manufacturerTableName, function ($join) use ($manufacturersModelsTableName, $manufacturerTableName) {
                    $join->on($manufacturerTableName . '.id', '=', $manufacturersModelsTableName . '.manufacturer_id');
                })
                ->join($vehiclesAttributesTableName, function ($join) use ($vehiclesTableName, $vehiclesAttributesTableName) {
                    $join->on($vehiclesTableName . '.id', '=', $vehiclesAttributesTableName . '.vehicle_id');
                })
                ->with(['accessories', 'attributes'])
                ->with(['model', 'model.manufacturer']);

            $queryParams = request()->query() ?? [];

            $registrosPagina = request()->query('registrosPagina', 10);

            foreach ($queryParams as $key => $param) {
                if ($key == 'ordenar') {
                    switch ((int)$param) {
                        case 1:
                            $query->orderBy($vehiclesTableName . '.price', 'asc');
                            break;
                        case 2:
                            $query->orderBy($vehiclesTableName . '.price', 'desc');
                            break;
                        case 3:
                            $query->orderBy($vehiclesAttributesTableName . '.year', 'asc');
                            break;
                        case 4:
                            $query->orderBy($vehiclesAttributesTableName . '.year', 'desc');
                            break;
                        default:
                            break;
                    }
                } else {
                    switch ($key) {
                        case 'registrosPagina':
                            if (!in_array($registrosPagina, [10, 25, 50, 100])) {
                                $registrosPagina = 10;
                            }
                            break;
                        case 'ano_de':
                            $query->where($manufacturersModelsTableName . '.year', '>=', (int) $param);
                            break;
                        case 'ano_ate':
                            $query->where($manufacturersModelsTableName . '.year', '<=', (int) $param);
                            break;
                        case 'km_de':
                            $query->where($vehiclesAttributesTableName . '.km', '>=', (int) $param);
                            break;
                        case 'km_ate':
                            $query->where($vehiclesAttributesTableName . '.km', '<=', (int) $param);
                            break;
                        case 'preco_de':
                            $query->where($vehiclesTableName . '.price', '>=', (float) $param);
                            break;
                        case 'preco_ate':
                            $query->where($vehiclesTableName . '.price', '<=', (float) $param);
                            break;
                        default:
                            break;
                    }
                }
            }

            if (is_null($slug) || trim($slug) == '') {
                return $query->paginate($registrosPagina);
            } else {
                $slug = rawurlencode($slug);
                return $query->where('vehicles.slug', $slug)->get()->first() ?? [];
            }
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Vehicle  $vehicle
     * @return \Illuminate\Http\Response
     */
    public function show(Vehicle $vehicle)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Vehicle  $vehicle
     * @return \Illuminate\Http\Response
     */
    public function edit(Vehicle $vehicle)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Vehicle  $vehicle
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Vehicle $vehicle)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Vehicle  $vehicle
     * @return \Illuminate\Http\Response
     */
    public function destroy(Vehicle $vehicle)
    {
        //
    }
}
