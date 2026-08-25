<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DropiToken;
use Illuminate\Http\Request;

class DropiSettingController extends Controller
{
    public function index()
    {
        $dropiToken = DropiToken::first();
        $tokenDetails = $dropiToken ? $dropiToken->decodeToken() : null;

        return view('admin.dropi.settings', compact('dropiToken', 'tokenDetails'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'store' => 'required|string|max:255',
            'token' => 'required|string',
            'sync' => 'required|in:AUTOMÁTICAMENTE,MANUALMENTE',
            'create_prod_empr' => 'nullable|boolean',
            'api_url' => 'nullable|url',
        ]);

        $dropiToken = DropiToken::first();

        $data = [
            'store' => $request->store,
            'token' => trim($request->token),
            'sync' => $request->sync,
            'create_prod_empr' => $request->boolean('create_prod_empr'),
            'api_url' => $request->api_url ?: 'https://api.dropi.co/api/',
        ];

        if ($dropiToken) {
            $dropiToken->update($data);
        } else {
            $dropiToken = DropiToken::create($data);
        }

        $result = $dropiToken->validateTokenDetails();

        if ($result['is_valid']) {
            return back()
                ->with('success', '¡Token de Dropi guardado y validado correctamente!')
                ->with('validation_result', $result);
        } else {
            return back()
                ->with('warning', 'Token guardado, pero la validación arrojó observaciones: ' . $result['message'])
                ->with('validation_result', $result);
        }
    }

    public function validateToken(Request $request)
    {
        $dropiToken = DropiToken::first();

        if (!$dropiToken || empty($dropiToken->token)) {
            return back()->with('error', 'No hay ningún token de Dropi registrado para validar. Por favor ingresa tu token primero.');
        }

        $result = $dropiToken->validateTokenDetails();

        if ($result['is_valid']) {
            return back()
                ->with('success', '¡Token de Dropi validado con éxito! Todos los parámetros de autenticación son correctos.')
                ->with('validation_result', $result);
        } else {
            return back()
                ->with('error', 'Error en la validación del Token de Dropi: ' . $result['message'])
                ->with('validation_result', $result);
        }
    }

    public function destroy(DropiToken $dropiToken)
    {
        $dropiToken->delete();
        return back()->with('success', 'Token de Dropi eliminado correctamente.');
    }
}
