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
use App\Models\Reunion;
use App\Models\Servicio;
use App\Models\Solicitud;
use App\Models\Usuario;
use DateTimeImmutable;
use DateTimeZone;

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

    public function reuniones(): never
    {
        $cliente = $this->clienteDeSesion();
        $desde = $this->instanteConsulta(Request::query('desde'));
        $hasta = $this->instanteConsulta(Request::query('hasta'));

        if (Request::query('desde') !== null && $desde === null) {
            Response::error('VALIDACION', 'La fecha inicial no es válida.', 422, [
                'desde' => 'Usa una fecha válida.',
            ]);
        }

        if (Request::query('hasta') !== null && $hasta === null) {
            Response::error('VALIDACION', 'La fecha final no es válida.', 422, [
                'hasta' => 'Usa una fecha válida.',
            ]);
        }

        Response::data(Reunion::paraCliente((int) $cliente['id'], $desde, $hasta));
    }

    public function crearReunion(): never
    {
        $cliente = $this->clienteDeSesion();
        Csrf::validate();
        $data = Request::json();
        $proyectoId = (int) ($data['proyecto_id'] ?? 0);
        $titulo = trim((string) ($data['titulo'] ?? ''));
        $inicio = Reunion::parseLocal((string) ($data['inicio'] ?? ''));
        $fin = Reunion::parseLocal((string) ($data['fin'] ?? ''));
        $errors = [];

        if (Proyecto::findForCliente($proyectoId, (int) $cliente['id']) === null) {
            $errors['proyecto_id'] = 'Selecciona uno de tus proyectos.';
        }

        if (strlen($titulo) < 3 || strlen($titulo) > 120) {
            $errors['titulo'] = 'El título debe tener entre 3 y 120 caracteres.';
        }

        if ($inicio === null) {
            $errors['inicio'] = 'Indica cuándo empieza la reunión.';
        }

        if ($fin === null) {
            $errors['fin'] = 'Indica cuándo termina la reunión.';
        }

        if ($inicio !== null && $fin !== null) {
            $duracion = $fin->getTimestamp() - $inicio->getTimestamp();

            if ($duracion <= 0) {
                $errors['fin'] = 'La reunión debe terminar después de empezar.';
            } elseif ($duracion < 15 * 60 || $duracion > 4 * 3600) {
                $errors['fin'] = 'La reunión debe durar entre 15 minutos y 4 horas.';
            } elseif ($inicio <= Reunion::ahora()) {
                $errors['inicio'] = 'La reunión debe quedar en el futuro.';
            } elseif (Reunion::hayEmpalme((int) $cliente['id'], $inicio, $fin)) {
                $errors['inicio'] = 'Ese horario se empalma con otra reunión tuya.';
            }
        }

        if ($errors !== []) {
            Response::error('VALIDACION', 'Revisa los datos de la reunión.', 422, $errors);
        }

        $id = Reunion::crear($proyectoId, $titulo, $inicio, $fin);
        $reunion = Reunion::findForCliente($id, (int) $cliente['id']);
        Response::data($reunion, 201);
    }

    public function cancelarReunion(int $id): never
    {
        $cliente = $this->clienteDeSesion();
        Csrf::validate();
        $reunion = Reunion::findForCliente($id, (int) $cliente['id']);

        if ($reunion === null) {
            Response::error('NO_ENCONTRADO', 'La reunión no existe.', 404);
        }

        if (!in_array($reunion['estado'], Reunion::ACTIVAS, true)) {
            Response::error('VALIDACION', 'Esta reunión ya no se puede cancelar.', 422, [
                'estado' => 'Solo puedes cancelar una reunión solicitada o confirmada.',
            ]);
        }

        $inicio = new DateTimeImmutable((string) $reunion['inicio']);

        if ($inicio <= Reunion::ahora()) {
            Response::error('VALIDACION', 'Esta reunión ya empezó.', 422, [
                'inicio' => 'Solo puedes cancelar una reunión que aún no empieza.',
            ]);
        }

        Reunion::cancelar($id);
        Response::data(Reunion::findForCliente($id, (int) $cliente['id']));
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

    public function verFoto(): never
    {
        $sesion = Auth::requireRole('cliente');
        $usuario = Usuario::findById((int) $sesion['id']);
        $extension = is_array($usuario) ? (string) ($usuario['foto'] ?? '') : '';
        $tipos = [
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
        ];
        $ruta = $extension !== '' ? Usuario::rutaFoto((int) $sesion['id'], $extension) : '';

        if ($ruta === '' || !is_file($ruta) || !isset($tipos[$extension])) {
            Response::error('NO_ENCONTRADO', 'No hay foto de perfil.', 404);
        }

        Response::file($ruta, $tipos[$extension]);
    }

    public function subirFoto(): never
    {
        $sesion = Auth::requireRole('cliente');
        Csrf::validate();
        $archivo = $_FILES['foto'] ?? null;

        if (!is_array($archivo)) {
            Response::error('VALIDACION', 'Elige una imagen.', 422, [
                'foto' => 'Elige una imagen.',
            ]);
        }

        $codigo = (int) ($archivo['error'] ?? UPLOAD_ERR_NO_FILE);

        if ($codigo === UPLOAD_ERR_INI_SIZE || $codigo === UPLOAD_ERR_FORM_SIZE) {
            Response::error('VALIDACION', 'La imagen no puede superar 2 MB.', 422, [
                'foto' => 'La imagen no puede superar 2 MB.',
            ]);
        }

        if ($codigo !== UPLOAD_ERR_OK) {
            Response::error('VALIDACION', 'No se pudo leer la imagen.', 422, [
                'foto' => 'No se pudo leer la imagen.',
            ]);
        }

        $temporal = (string) ($archivo['tmp_name'] ?? '');
        $peso = (int) ($archivo['size'] ?? 0);

        if ($peso < 1 || $peso > 2 * 1024 * 1024) {
            Response::error('VALIDACION', 'La imagen no puede superar 2 MB.', 422, [
                'foto' => 'Usa una imagen de hasta 2 MB.',
            ]);
        }

        $info = @getimagesize($temporal);
        $tipos = [
            IMAGETYPE_JPEG => 'jpg',
            IMAGETYPE_PNG => 'png',
            IMAGETYPE_WEBP => 'webp',
        ];
        $tipo = is_array($info) ? (int) ($info[2] ?? 0) : 0;

        if (!isset($tipos[$tipo])) {
            Response::error('VALIDACION', 'Usa una imagen JPG, PNG o WebP.', 422, [
                'foto' => 'Usa una imagen JPG, PNG o WebP.',
            ]);
        }

        $extension = $tipos[$tipo];
        $directorio = Usuario::directorioFotos();

        if (!is_dir($directorio) && !mkdir($directorio, 0775, true) && !is_dir($directorio)) {
            Response::error('ERROR_INTERNO', 'No se pudo guardar la foto.', 500);
        }

        $destino = Usuario::rutaFoto((int) $sesion['id'], $extension);

        if (!move_uploaded_file($temporal, $destino)) {
            Response::error('ERROR_INTERNO', 'No se pudo guardar la foto.', 500);
        }

        Usuario::borrarOtrasFotos((int) $sesion['id'], $extension);
        Usuario::guardarFoto((int) $sesion['id'], $extension);
        $usuario = Usuario::findById((int) $sesion['id']);

        if ($usuario === null) {
            Response::error('ERROR_INTERNO', 'No se pudo recuperar el perfil actualizado.', 500);
        }

        $_SESSION['usuario'] = Usuario::publicData($usuario);
        Response::data(['foto' => $_SESSION['usuario']['foto']]);
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

    private function instanteConsulta(?string $valor): ?DateTimeImmutable
    {
        if ($valor === null) {
            return null;
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $valor) === 1) {
            return Reunion::parseLocal($valor . 'T00:00');
        }

        if (str_ends_with($valor, 'Z') || str_contains($valor, '+')) {
            try {
                return (new DateTimeImmutable($valor))->setTimezone(new DateTimeZone('UTC'));
            } catch (\Exception) {
                return null;
            }
        }

        return Reunion::parseLocal($valor);
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
                'foto' => $usuario['foto'] ?? null,
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
