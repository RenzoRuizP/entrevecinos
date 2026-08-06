<?php
declare(strict_types=1);
require_once __DIR__.'/../../Config/config.php';
require_once __DIR__.'/../../models/SesionJWT.php';
require_once __DIR__.'/../../models/DashboardGerencial.php';

final class apiDashboardGerencialController
{
    private function json(int $status,array $data):void{http_response_code($status);header('Content-Type: application/json; charset=utf-8');header('Cache-Control: no-store');echo json_encode($data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);}
    private function admin():?array{
        $a=$GLOBALS['EV_AUTH']??null;if(!is_array($a)){ $t=$_COOKIE['auth_token']??null;$a=$t?SesionJWT::verificarToken((string)$t):null; }
        if(!is_array($a)||(int)($a['codigo_usuario']??0)<=0){$this->json(401,['ok'=>false,'error'=>'UNAUTHORIZED','mensaje'=>'Tu sesión finalizó.']);return null;}
        $r=strtolower(trim((string)($a['rol']??$a['nombre_rol']??'')));if($r!=='admin'){$this->json(403,['ok'=>false,'error'=>'FORBIDDEN','mensaje'=>'Disponible únicamente para el administrador general de EV.']);return null;}return $a;
    }
    private function input():array{ $raw=file_get_contents('php://input');$j=json_decode((string)$raw,true);return is_array($j)?$j:$_POST; }
    private function csrf():bool{if(session_status()===PHP_SESSION_NONE)session_start();$e=(string)($_SESSION['ev_dashboard_csrf']??'');$r=trim((string)($_SERVER['HTTP_X_EV_CSRF']??''));if($e===''||$r===''||!hash_equals($e,$r)){$this->json(419,['ok'=>false,'mensaje'=>'La sesión del dashboard venció. Vuelve a abrir el módulo.']);return false;}return true;}
    public function catalogos():void{if(!$this->admin())return;try{$this->json(200,['ok'=>true,'data'=>(new DashboardGerencial())->catalogos()]);}catch(Throwable $e){error_log('[EV][DashboardGerencial][catalogos] '.$e->getMessage());$this->json(500,['ok'=>false,'mensaje'=>'No se pudieron cargar los filtros gerenciales.']);}}
    public function resumen():void{if(!$this->admin())return;try{$this->json(200,['ok'=>true,'data'=>(new DashboardGerencial())->resumen($_GET)]);}catch(Throwable $e){error_log('[EV][DashboardGerencial][resumen] '.$e->getMessage());$this->json(500,['ok'=>false,'mensaje'=>'No se pudo generar el dashboard gerencial.','detalle'=>defined('EV_ENTORNO')&&EV_ENTORNO==='local'?$e->getMessage():null]);}}
    public function guardarMeta():void{$a=$this->admin();if(!$a||!$this->csrf())return;try{$this->json(200,(new DashboardGerencial())->guardarMeta($this->input(),(int)$a['codigo_usuario']));}catch(InvalidArgumentException $e){$this->json(422,['ok'=>false,'mensaje'=>$e->getMessage()]);}catch(Throwable $e){error_log('[EV][DashboardGerencial][meta] '.$e->getMessage());$this->json(500,['ok'=>false,'mensaje'=>$e->getMessage()]);}}
}
