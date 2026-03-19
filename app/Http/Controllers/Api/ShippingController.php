<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Services\MelhorEnvioService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
class ShippingController extends Controller {
    public function __construct(private MelhorEnvioService $me) {}
    public function calculate(Request $request): JsonResponse {
        $request->validate(['cep' => 'required|string', 'items' => 'required|array']);
        $result = $this->me->calculateShipping($request->cep, $request->items);
        if (isset($result['error'])) return response()->json(['error' => $result['error']], 422);
        return response()->json($result);
    }
}
