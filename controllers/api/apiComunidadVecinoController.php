<?php
declare(strict_types=1);
require_once __DIR__.'/../../Config/config.php';
require_once __DIR__.'/../../models/SesionJWT.php';
require_once __DIR__.'/../../models/ComunidadVecino.php';

final class apiComunidadVecinoController
{
    private function json(int $status,array $payload):void{http_response_code($status);header('Content-Type: application/json; charset=utf-8');header('Cache-Control: no-store');echo json_encode($payload,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);}
    private function auth():?array{$a=$GLOBALS['EV_AUTH']??null;if(is_array($a)&&(int)($a['codigo_usuario']??0)>0)return $a;$t=$_COOKIE['auth_token']??null;$a=$t?SesionJWT::verificarToken((string)$t):null;return is_array($a)?$a:null;}
    private function exigirConsulta():?array
    {
        $a=$this->auth();if(!$a){$this->json(401,['ok'=>false,'mensaje'=>'Tu sesión ha finalizado.']);return null;}
        $r=strtolower(trim((string)($a['rol']??$a['nombre_rol']??'')));
        $codigoRol=(int)($a['codigo_rol']??0);
        $adminRoleId=defined('EV_ADMIN_ROLE_ID')?(int)EV_ADMIN_ROLE_ID:1;
        $esAdmin=in_array($r,['admin','administrador'],true)||$codigoRol===$adminRoleId;
        if($r!=='vecino'&&!$esAdmin){$this->json(403,['ok'=>false,'mensaje'=>'No tienes permisos para consultar Comunidad.']);return null;}
        return $a;
    }
    private function filtrosComunidad():array{return ['tipo_conjunto'=>$_GET['tipo_conjunto']??'','codigo_comunidad'=>$_GET['codigo_comunidad']??0];}
    public function comunidades():void
    {
        $a=$this->exigirConsulta();if(!$a)return;$r=strtolower((string)($a['rol']??$a['nombre_rol']??''));
        $codigoRol=(int)($a['codigo_rol']??0);$adminRoleId=defined('EV_ADMIN_ROLE_ID')?(int)EV_ADMIN_ROLE_ID:1;
        if(!in_array($r,['admin','administrador'],true)&&$codigoRol!==$adminRoleId){$this->json(403,['ok'=>false,'mensaje'=>'El selector global está disponible únicamente para Administrador EV.']);return;}
        try{$this->json(200,['ok'=>true,'data'=>(new ComunidadVecino())->listarComunidadesActivas()]);}catch(Throwable $e){$this->error($e,'comunidades');}
    }
    public function listar():void
    {
        $a=$this->exigirConsulta();if(!$a)return;
        try{$r=(new ComunidadVecino())->listarPublicaciones($a,array_merge($this->filtrosComunidad(),['tipo'=>$_GET['tipo']??'all','q'=>$_GET['q']??'','page'=>$_GET['page']??1,'size'=>$_GET['size']??9]));$this->json(200,['ok'=>true]+$r);}catch(Throwable $e){$this->error($e,'listar');}
    }
    public function detalle(string|int $id):void
    {
        $a=$this->exigirConsulta();if(!$a)return;
        try{$id=(int)$id;if($id<=0)throw new InvalidArgumentException('Identificador inválido.');$item=(new ComunidadVecino())->obtenerPublicacion($a,$id,$this->filtrosComunidad());if(!$item){$this->json(404,['ok'=>false,'mensaje'=>'La publicación ya no está visible en la comunidad seleccionada.']);return;}$this->json(200,['ok'=>true,'item'=>$item]);}catch(Throwable $e){$this->error($e,'detalle');}
    }
    private function error(Throwable $e,string $m):void
    {
        if($e instanceof InvalidArgumentException){$this->json(422,['ok'=>false,'mensaje'=>$e->getMessage()]);return;}
        if($e instanceof DomainException){$this->json(409,['ok'=>false,'mensaje'=>$e->getMessage()]);return;}
        error_log('[EV][apiComunidadVecinoController::'.$m.'] '.$e->getMessage());$this->json(500,['ok'=>false,'mensaje'=>'No se pudieron cargar las novedades de la comunidad.']);
    }
}
