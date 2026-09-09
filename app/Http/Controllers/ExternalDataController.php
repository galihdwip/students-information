<?php

namespace App\Http\Controllers;

use App\Services\ExternalApiService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ExternalDataController extends Controller
{
    public function __construct(
        private ExternalApiService $externalApiService
    ) {
    }

    #[OA\Get(
        path: "/students/data",
        summary: "Ambil semua data students",
        description: "Mengambil data mahasiswa dari API eksternal (memerlukan Bearer Token)",
        security: [["bearerAuth" => []]],
        tags: ["Students Data"],
        responses: [
            new OA\Response(response: 200, description: "Data berhasil diambil"),
            new OA\Response(response: 401, description: "Unauthenticated")
        ]
    )]
    public function index(Request $request)
    {
        try {
            $data = $this->externalApiService->getData();

            return response()->json([
                'success' => true,
                'message' => 'Data retrieved successfully',
                'total' => count($data),
                'data' => $data,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data eksternal: ' . $e->getMessage(),
            ], 500);
        }
    }

    #[OA\Get(
        path: "/students/search",
        summary: "Pencarian data eksternal",
        description: "Mencari data mahasiswa berdasarkan nama, NIM, atau YMD",
        tags: ["Students Data"],
        parameters: [
            new OA\Parameter(name: "nama", in: "query", description: "Filter nama", required: false, schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "nim", in: "query", description: "Filter NIM", required: false, schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "ymd", in: "query", description: "Filter YMD", required: false, schema: new OA\Schema(type: "string")),
        ],
        responses: [
            new OA\Response(response: 200, description: "Hasil pencarian"),
            new OA\Response(response: 500, description: "Terjadi kesalahan server")
        ]
    )]
    public function search(Request $request)
    {
        try {
            $data = $this->externalApiService->getData();

            $nama = strtolower(trim((string) $request->query('nama', '')));
            $nim = trim((string) $request->query('nim', ''));
            $ymd = trim((string) $request->query('ymd', ''));

            $result = collect($data)->filter(function ($item) use ($nama, $nim, $ymd) {
                if (! is_array($item)) {
                    return false;
                }

                $match = true;

                if ($nama !== '') {
                    $match = $match && str_contains(strtolower($item['NAMA'] ?? ''), $nama);
                }

                if ($nim !== '') {
                    $match = $match && (($item['NIM'] ?? '') === $nim);
                }

                if ($ymd !== '') {
                    $match = $match && (($item['YMD'] ?? '') === $ymd);
                }

                return $match;
            })->values();

            return response()->json([
                'success' => true,
                'total' => $result->count(),
                'data' => $result,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses pencarian data eksternal: ' . $e->getMessage(),
            ], 500);
        }
    }
}