<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\SubnetResource;
use App\Models\Subnet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubnetController extends Controller
{
    public function index()
    {
        $subnets = Subnet::query();

        $subnets = $subnets->paginate(10);

        return SubnetResource::collection($subnets);
    }

    public function store(Request $request)
    {
        $subnet = new Subnet;
        $subnet->name = $request->input('name');
        $subnet->slug = $request->input('slug');
        $subnet->size = $request->input('size');
        $subnet->pci_dss = $request->input('pci_dss');
        $subnet->save();

        return new SubnetResource($subnet);
    }

    public function show(Request $request)
    {
        $subnets = Subnet::query();

        if ($request->has('name')) {
            $subnets->where('name', 'like', '%' . $request->input('name') . '%');
        }

        $subnets = $subnets->paginate($request->input('per_page', 10));

        return SubnetResource::collection($subnets);
    }

    public function update(Request $request, $id)
    {
        $subnet = Subnet::find($id);
        $subnet->name = $request->input('name');
        $subnet->size = $request->input('size');
        $subnet->pci_dss = $request->input('pci_dss');
        $subnet->save();

        return new SubnetResource($subnet);
    }

    public function destroy($id)
    {
        $data = Validator::validate(
            ['id' => $id],
            ['id' => 'required|uuid'],
            ['id', 'Subnet not found!']
        );

        $subnet = Subnet::find($data['id']);
        $subnet->delete();

        return response()->json(null, 204);
    }
}
