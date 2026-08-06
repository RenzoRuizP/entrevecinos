<?php
declare(strict_types=1);
require_once __DIR__.'/../Config/config.php';
require_once __DIR__.'/../models/SesionJWT.php';
require_once __DIR__.'/../models/ComunidadVecino.php';

final class comunidadVecinoController
{
    private function usuarioAuth(): ?array
    {
        $auth=$GLOBALS['EV_AUTH']??null;
        if(is_array($auth)&&(int)($auth['codigo_usuario']??0)>0)return $auth;
        $token=$_COOKIE['auth_token']??null;
        $usuario=$token?SesionJWT::verificarToken((string)$token):null;
        return is_array($usuario)?$usuario:null;
    }
    public function index():void
    {
        $usuario=$this->usuarioAuth();
        if(!$usuario){http_response_code(401);header('Content-Type: application/json; charset=utf-8');echo json_encode(['ok'=>false,'mensaje'=>'Tu sesión ha finalizado.'],JSON_UNESCAPED_UNICODE);return;}
        $rol=strtolower(trim((string)($usuario['rol']??$usuario['nombre_rol']??'')));
        $codigoRol=(int)($usuario['codigo_rol']??0);
        $adminRoleId=defined('EV_ADMIN_ROLE_ID')?(int)EV_ADMIN_ROLE_ID:1;
        $esAdminComunidad=in_array($rol,['admin','administrador'],true)||$codigoRol===$adminRoleId;
        if($rol!=='vecino'&&!$esAdminComunidad){http_response_code(403);require __DIR__.'/../views/comunidadAccesoDenegadoView.php';return;}
        $modelo=new ComunidadVecino();
        $comunidadesAdmin=[];
        try{
            if($esAdminComunidad){
                $comunidadesAdmin=$modelo->listarComunidadesActivas();
                $comunidadVecino=[
                    'tipo_conjunto'=>'',
                    'codigo_comunidad'=>0,
                    'codigo_condominio'=>null,
                    'codigo_urbanizacion'=>null,
                    'nombre_comunidad'=>'Sin comunidad seleccionada',
                    'etiqueta_tipo'=>'Comunidad',
                    'direccion'=>'',
                    'nombre_distrito'=>'',
                    'nombre_provincia'=>'',
                    'nombre_departamento'=>'',
                ];
            }else{$comunidadVecino=$modelo->obtenerComunidadActual($usuario);}
        }catch(Throwable $e){
            $comunidadVecino=['tipo_conjunto'=>'','codigo_comunidad'=>0,'nombre_comunidad'=>'Comunidad no disponible','etiqueta_tipo'=>'Comunidad','nombre_distrito'=>''];
        }
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('X-Partial-Ok: 1');
        require __DIR__.'/../views/comunidadVecinoView.php';
    }
}
