<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
class AddressController extends Controller {
    public function index(Request $request): JsonResponse {
        return response()->json($request->user()->addresses()->get());
    }
    public function store(Request $request): JsonResponse {
        $data = $request->validate([
            'label' => 'nullable|string', 'name' => 'required|string', 'phone' => 'required|string',
            'zipcode' => 'required|string', 'street' => 'required|string', 'number' => 'required|string',
            'complement' => 'nullable|string', 'neighborhood' => 'required|string',
            'city' => 'required|string', 'state' => 'required|string|size:2',
        ]);
        $address = $request->user()->addresses()->create($data + ['country' => 'BR']);
        return response()->json($address, 201);
    }
    public function update(Request $request, int $id): JsonResponse {
        $address = Address::where('user_id', $request->user()->id)->findOrFail($id);
        $address->update($request->validate([
            'label' => 'nullable|string', 'name' => 'required|string', 'phone' => 'required|string',
            'zipcode' => 'required|string', 'street' => 'required|string', 'number' => 'required|string',
            'complement' => 'nullable|string', 'neighborhood' => 'required|string',
            'city' => 'required|string', 'state' => 'required|string|size:2',
        ]));
        return response()->json($address);
    }
    public function destroy(Request $request, int $id): JsonResponse {
        Address::where('user_id', $request->user()->id)->findOrFail($id)->delete();
        return response()->json(null, 204);
    }
}
