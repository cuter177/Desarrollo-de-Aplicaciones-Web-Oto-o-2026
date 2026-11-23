<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Csrf;
use App\Helpers\RateLimiter;
use App\Helpers\Request;
use App\Helpers\Response;
use App\Middleware\Auth;
use App\Models\Cliente;
use App\Models\Proyecto;
use App\Models\Servicio;
use App\Models\Solicitud;
use App\Models\Usuario;

final class PortalController
{
    public function resumen(): never
    {
        $cliente = $this->clienteDeSesion();
        $proyectos = Proyecto::allForCliente((int) $cliente['id']);
        $solicitudes = Solicitud::allForCliente((int) $cliente['id']);
        $cerrados = ['entregado', 'cancelado'];
        $abiertas = ['nueva', 'en_revision', 'cotizada'];

        $actividad = [];

        foreach ($proyectos as $proyecto) {
            $actividad[] = [
                'tipo' => 'proyecto',
                'id' => $proyecto['id'],
                'titulo' => $proyecto['nombre'],
                'estado' => $proyecto['estado'],
                'fecha' => $proyecto['creado_en'],
            ];
        }

        foreach ($solicitudes as $solicitud) {
            $titulo = $solicitud['servicio_nombre'] ?? $solicitud['plan_nombre'] ?? null;
            $actividad[] = [
                'tipo' => 'solicitud',
                'id' => $solicitud['id'],
                'titulo' => is_string($titulo) && $titulo !== ''
                    ? $titulo
                    : self::recortar((string) $solicitud['descripcion'], 80),
                'estado' => $solicitud['estado'],
                'fecha' => $solicitud['creado_en'],
            ];
        }

        usort(
            $actividad,
            static fn (array $a, array $b): int => strcmp((string) $b['fecha'], (string) $a['fecha'])
        );

        Response::data([
            'cliente' => [
                'id' => (int) $cliente['id'],
                'empresa' => $cliente['empresa'],
                'industria' => $cliente['industria'],
                'telefono' => $cliente['telefono'],
                'sitio_web' => $cliente['sitio_web'],
                'cargo' => $cliente['cargo'],
                'frase' => $cliente['frase'],
                'ciudad' => $cliente['ciudad'],
            ],
            'proyectos_total' => count($proyectos),
            'proyectos_activos' => count(array_filter(
                $proyectos,
                static fn (array $proyecto): bool => !in_array($proyecto['estado'], $cerrados, true)
            )),
            'solicitudes_total' => count($solicitudes),
            'solicitudes_abiertas' => count(array_filter(
                $solicitudes,
                static fn (array $solicitud): bool => in_array($solicitud['estado'], $abiertas, true)
            )),
            'actividad' => array_slice($actividad, 0, 8),
        ]);
    }

    public function proyectos(): never
    {
        $cliente = $this->clienteDeSesion();
        Response::data(Proyecto::allForCliente((int) $cliente['id']));
    }

    public function proyecto(int $id): never
    {
        $cliente = $this->clienteDeSesion();
        $proyecto = Proyecto::findForCliente($id, (int) $cliente['id']);

        if ($proyecto === null) {
            Response::error('NO_ENCONTRADO', 'El proyecto no existe.', 404);
        }

        Response::data($proyecto);
    }

    public function solicitudes(): never
    {
        $cliente = $this->clienteDeSesion();
        Response::data(Solicitud::allForCliente((int) $cliente['id']));
    }

    public function solicitud(int $id): never
    {
        $cliente = $this->clienteDeSesion();
        $solicitud = Solicitud::findForCliente($id, (int) $cliente['id']);

        if ($solicitud === null) {
            Response::error('NO_ENCONTRADO', 'La solicitud no existe.', 404);
        }

        Response::data($solicitud);
    }

    public function crearSolicitud(): never
    {
        $sesion = Auth::requireRole('cliente');
        Csrf::validate();
        $cliente = Cliente::findByUsuarioId((int) $sesion['id']);

        if ($cliente === null) {
            Response::error('SIN_PERMISOS', 'Tu cuenta no tiene un perfil de cliente.', 403);
        }

        $llave = 'portal:solicitud:' . $sesion['id'];
        RateLimiter::enforce($llave, 10, 3600);

        $data = Request::json();
        $descripcion = trim((string) ($data['descripcion'] ?? ''));
        $servicioId = $data['servicio_id'] ?? null;
        $errors = [];

        if (strlen($descripcion) < 10 || strlen($descripcion) > 5000) {
            $errors['descripcion'] = 'Describe la solicitud con entre 10 y 5000 caracteres.';
        }

        $servicio = null;

        if ($servicioId !== null && $servicioId !== '') {
            $servicio = Servicio::findById((int) $servicioId);

            if ($servicio === null || ($servicio['activo'] ?? true) !== true) {
                $errors['servicio_id'] = 'Selecciona un servicio activo.';
            }
        }

        if ($errors !== []) {
            Response::error('VALIDACION', 'Revisa los datos de la solicitud.', 422, $errors);
        }

        $id = Solicitud::create([
            'cliente_id' => (int) $cliente['id'],
            'servicio_id' => $servicio !== null ? (int) $servicio['id'] : null,
            'nombre_contacto' => (string) $sesion['nombre'],
            'email' => (string) $sesion['email'],
            'empresa' => $cliente['empresa'],
            'telefono' => $cliente['telefono'],
            'descripcion' => $descripcion,
            'necesidades' => $descripcion,
            'estado' => 'nueva',
            'origen' => 'portal',
        ]);

        RateLimiter::hit($llave);
        $solicitud = Solicitud::findForCliente($id, (int) $cliente['id']);
        Response::data($solicitud, 201);
    }

    public function perfil(): never
    {
        $sesion = Auth::requireRole('cliente');
        $cliente = Cliente::findByUsuarioId((int) $sesion['id']);

        if ($cliente === null) {
            Response::error('SIN_PERMISOS', 'Tu cuenta no tiene un perfil de cliente.', 403);
        }

        Response::data($this->perfilPublico($sesion, $cliente));
    }

    public function actualizarPerfil(): never
    {
        $sesion = Auth::requireRole('cliente');
        Csrf::validate();
        $cliente = Cliente::findByUsuarioId((int) $sesion['id']);

        if ($cliente === null) {
            Response::error('SIN_PERMISOS', 'Tu cuenta no tiene un perfil de cliente.', 403);
        }

        $data = Request::json();
        $usuarioCambios = [];
        $clienteCambios = [];
        $errors = [];

        if (array_key_exists('nombre', $data)) {
            $nombre = trim((string) $data['nombre']);

            if (strlen($nombre) < 2 || strlen($nombre) > 120) {
                $errors['nombre'] = 'El nombre debe tener entre 2 y 120 caracteres.';
            } else {
                $usuarioCambios['nombre'] = $nombre;
            }
        }

        if (array_key_exists('empresa', $data)) {
            $empresa = trim((string) $data['empresa']);

            if (strlen($empresa) < 2 || strlen($empresa) > 150) {
                $errors['empresa'] = 'La empresa debe tener entre 2 y 150 caracteres.';
            } else {
                $clienteCambios['empresa'] = $empresa;
            }
        }

        if (array_key_exists('telefono', $data)) {
            $telefono = trim((string) $data['telefono']);

            if ($telefono !== '' && (strlen($telefono) < 7 || strlen($telefono) > 30)) {
                $errors['telefono'] = 'El teléfono debe tener entre 7 y 30 caracteres.';
            } else {
                $clienteCambios['telefono'] = $telefono !== '' ? $telefono : null;
            }
        }

        if (array_key_exists('sitio_web', $data)) {
            $sitio = trim((string) $data['sitio_web']);

            if ($sitio !== '' && (!filter_var($sitio, FILTER_VALIDATE_URL) || strlen($sitio) > 200)) {
                $errors['sitio_web'] = 'Ingresa una URL válida de hasta 200 caracteres.';
            } else {
                $clienteCambios['sitio_web'] = $sitio !== '' ? $sitio : null;
            }
        }

        if (array_key_exists('industria', $data)) {
            $industria = trim((string) $data['industria']);

            if (strlen($industria) > 100) {
                $errors['industria'] = 'La industria no puede superar los 100 caracteres.';
            } else {
                $clienteCambios['industria'] = $industria !== '' ? $industria : null;
            }
        }

        $this->textoOpcional($data, 'cargo', 80, 'El cargo no puede superar los 80 caracteres.', $clienteCambios, $errors);
        $this->textoOpcional($data, 'frase', 160, 'La frase no puede superar los 160 caracteres.', $clienteCambios, $errors);
        $this->textoOpcional($data, 'ciudad', 80, 'La ciudad no puede superar los 80 caracteres.', $clienteCambios, $errors);

        if ($errors !== []) {
            Response::error('VALIDACION', 'Revisa los datos del perfil.', 422, $errors);
        }

        if ($usuarioCambios === [] && $clienteCambios === []) {
            Response::error('VALIDACION', 'No se recibieron cambios para el perfil.', 422);
        }

        if ($usuarioCambios !== []) {
            Usuario::updateAccount((int) $sesion['id'], $usuarioCambios);
        }

        if ($clienteCambios !== []) {
            Cliente::update((int) $cliente['id'], $clienteCambios);
        }

        $usuario = Usuario::findById((int) $sesion['id']);
        $actualizado = Cliente::findByUsuarioId((int) $sesion['id']);

        if ($usuario === null || $actualizado === null) {
            Response::error('ERROR_INTERNO', 'No se pudo recuperar el perfil actualizado.', 500);
        }

        $_SESSION['usuario'] = Usuario::publicData($usuario);
        Response::data($this->perfilPublico($_SESSION['usuario'], $actualizado));
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $cambios
     * @param array<string, string> $errors
     */
    private function textoOpcional(
        array $data,
        string $campo,
        int $maximo,
        string $mensaje,
        array &$cambios,
        array &$errors
    ): void {
        if (!array_key_exists($campo, $data)) {
            return;
        }

        $valor = trim((string) $data[$campo]);

        if (strlen($valor) > $maximo) {
            $errors[$campo] = $mensaje;
            return;
        }

        $cambios[$campo] = $valor !== '' ? $valor : null;
    }

    private static function recortar(string $texto, int $limite): string
    {
        if (function_exists('mb_substr')) {
            return mb_substr($texto, 0, $limite);
        }

        return substr($texto, 0, $limite);
    }

    /**
     * @return array<string, mixed>
     */
    private function clienteDeSesion(): array
    {
        $sesion = Auth::requireRole('cliente');
        $cliente = Cliente::findByUsuarioId((int) $sesion['id']);

        if ($cliente === null) {
            Response::error('SIN_PERMISOS', 'Tu cuenta no tiene un perfil de cliente.', 403);
        }

        return $cliente;
    }

    /**
     * @param array<string, mixed> $usuario
     * @param array<string, mixed> $cliente
     * @return array<string, mixed>
     */
    private function perfilPublico(array $usuario, array $cliente): array
    {
        return [
            'usuario' => [
                'id' => (int) $usuario['id'],
                'nombre' => (string) $usuario['nombre'],
                'email' => (string) $usuario['email'],
                'rol' => (string) $usuario['rol'],
            ],
            'cliente' => [
                'id' => (int) $cliente['id'],
                'empresa' => $cliente['empresa'],
                'telefono' => $cliente['telefono'],
                'sitio_web' => $cliente['sitio_web'],
                'industria' => $cliente['industria'],
                'cargo' => $cliente['cargo'],
                'frase' => $cliente['frase'],
                'ciudad' => $cliente['ciudad'],
            ],
        ];
    }
}
