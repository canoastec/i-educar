<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class IdiarioAppController extends Controller
{
    /**
     * Retorna informações do cliente para o i-diário
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'customers' => [
                [
                    'name' => 'Canoas - RS',
                    'url' => 'https://idiario.canoas.rs.gov.br/',
                    'support_url' => 'http://www.canoastec.rs.gov.br/'
                ]
            ]
        ]);
    }
}
