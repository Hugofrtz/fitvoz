<?php
// API JSON: sessão PHP, PDO, consultas preparadas e validação no servidor.
declare(strict_types=1);
ini_set('display_errors','0');
session_set_cookie_params(['httponly'=>true,'samesite'=>'Lax','secure'=>isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off']);
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
function respond($data,int $status=200): void {http_response_code($status);echo json_encode($data,JSON_UNESCAPED_UNICODE);exit;}
function fail(string $message,int $status=400): void {respond(['error'=>$message],$status);}
function field(array $data,string $key,int $min,int $max): string {
 $v=$data[$key]??''; if(!is_string($v)) fail('Campo inválido: '.$key);
 $v=trim($v);$len=preg_match_all('/./us',$v);
 if($len===false || $len<$min || $len>$max) fail("O campo $key deve ter entre $min e $max caracteres."); return $v;
}
function rating(array $data,string $key): int {$v=$data[$key]??null;if(filter_var($v,FILTER_VALIDATE_INT)===false || $v<1 || $v>5)fail('Escolha de 1 a 5 estrelas em '.$key.'.');return (int)$v;}
$_SESSION['csrf']=$_SESSION['csrf']??bin2hex(random_bytes(32));
$action=$_GET['action']??'list';
$method=$_SERVER['REQUEST_METHOD'];
$writes=['register','login','logout','save','delete'];
if(in_array($action,$writes,true)) {
 if($method!=='POST')fail('Método não permitido.',405);
 if(!hash_equals($_SESSION['csrf'],$_SERVER['HTTP_X_CSRF_TOKEN']??''))fail('Sessão expirada. Atualize a página.',403);
}
$data=[];
if($method==='POST'){
 if((int)($_SERVER['CONTENT_LENGTH']??0)>20000)fail('Envio muito grande.',413);
 $data=json_decode(file_get_contents('php://input'),true);if(!is_array($data))fail('Dados inválidos.');
}
if($action==='session')respond(['user'=>$_SESSION['user']??null,'csrf'=>$_SESSION['csrf']]);
if($action==='logout'){$_SESSION=[];session_regenerate_id(true);$_SESSION['csrf']=bin2hex(random_bytes(32));respond(['ok'=>true]);}
try {
 $c=require __DIR__.'/config.php';
 $db=new PDO("mysql:host={$c['host']};port={$c['port']};dbname={$c['database']};charset=utf8mb4",$c['user'],$c['password'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]);
 if($action==='register'||$action==='login'){
  $email=strtolower(field($data,'email',5,190));$password=$data['password']??'';
  if(!is_string($password)||strlen($password)<8||strlen($password)>72)fail('A senha deve ter entre 8 e 72 bytes.');
  if(!filter_var($email,FILTER_VALIDATE_EMAIL))fail('Informe um e-mail válido.');
  if($action==='register') {
   $name=field($data,'name',2,80);
   try{$s=$db->prepare('INSERT INTO usuarios(nome,email,senha) VALUES(?,?,?)');$s->execute([$name,$email,password_hash($password,PASSWORD_DEFAULT)]);}catch(PDOException $e){if($e->getCode()==='23000')fail('Este e-mail já tem cadastro.',409);throw $e;}
   $user=['id'=>(int)$db->lastInsertId(),'nome'=>$name,'email'=>$email];
  }else{
   if(($_SESSION['attempts']??0)>=8 && time()-($_SESSION['last_attempt']??0)<60)fail('Aguarde um minuto antes de tentar novamente.',429);
   $s=$db->prepare('SELECT * FROM usuarios WHERE email=?');$s->execute([$email]);$u=$s->fetch();
   if(!$u || !password_verify($password,$u['senha'])){$_SESSION['attempts']=($_SESSION['attempts']??0)+1;$_SESSION['last_attempt']=time();fail('E-mail ou senha incorretos.',401);}
   $_SESSION['attempts']=0;$user=['id'=>(int)$u['id'],'nome'=>$u['nome'],'email'=>$u['email']];
  }
  session_regenerate_id(true);$_SESSION['user']=$user;$_SESSION['csrf']=bin2hex(random_bytes(32));respond(['user'=>$user,'csrf'=>$_SESSION['csrf']]);
 }
 if($action==='list'){
  $gyms=$db->query('SELECT a.*,COUNT(v.id) total,ROUND(AVG(v.nota),2) media,ROUND(AVG(v.atendimento),2) atendimento,ROUND(AVG(v.estrutura),2) estrutura FROM academias a LEFT JOIN avaliacoes v ON v.academia_id=a.id GROUP BY a.id,a.nome,a.unidade,a.perfil,a.descricao,a.comodidades ORDER BY media DESC,total DESC,a.nome,a.unidade')->fetchAll();
  $trainers=$db->query('SELECT p.*,COUNT(v.nota_personal) total,ROUND(AVG(v.nota_personal),2) media FROM personais p LEFT JOIN avaliacoes v ON v.personal_id=p.id GROUP BY p.id,p.academia_id,p.nome')->fetchAll();
  respond(['gyms'=>$gyms,'trainers'=>$trainers]);
 }
 if($action==='reviews'){
  $id=filter_var($_GET['gym']??0,FILTER_VALIDATE_INT);if(!$id)fail('Academia inválida.');
  $s=$db->prepare('SELECT v.*,u.nome autor,p.nome personal FROM avaliacoes v JOIN usuarios u ON u.id=v.usuario_id LEFT JOIN personais p ON p.id=v.personal_id WHERE v.academia_id=? ORDER BY v.atualizado_em DESC,v.id DESC');$s->execute([$id]);respond(['reviews'=>$s->fetchAll()]);
 }
 if(in_array($action,['save','delete'],true)){
  if(!isset($_SESSION['user']))fail('Entre na sua conta para avaliar.',401);
  $uid=$_SESSION['user']['id'];$gid=filter_var($data['gym']??0,FILTER_VALIDATE_INT);if(!$gid)fail('Selecione a academia e unidade.');
  if($action==='delete'){$s=$db->prepare('DELETE FROM avaliacoes WHERE usuario_id=? AND academia_id=?');$s->execute([$uid,$gid]);respond(['ok'=>true]);}
  $s=$db->prepare('SELECT id FROM academias WHERE id=?');$s->execute([$gid]);if(!$s->fetch())fail('Academia não encontrada.');
  $note=rating($data,'nota');$service=rating($data,'atendimento');$structure=rating($data,'estrutura');
  $comment=field($data,'comentario',10,2000);$improve=field($data,'melhoria',0,1500);
  $pid=null;$pn=null;
  if(!empty($data['personal_id'])){
   $pid=filter_var($data['personal_id'],FILTER_VALIDATE_INT);if(!$pid)fail('Personal inválido.');
   $s=$db->prepare('SELECT id FROM personais WHERE id=? AND academia_id=?');$s->execute([$pid,$gid]);if(!$s->fetch())fail('Este personal não pertence à unidade.');
   $pn=rating($data,'nota_personal');
  }
  $s=$db->prepare('INSERT INTO avaliacoes(usuario_id,academia_id,nota,atendimento,estrutura,personal_id,nota_personal,comentario,melhoria) VALUES(?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE nota=VALUES(nota),atendimento=VALUES(atendimento),estrutura=VALUES(estrutura),personal_id=VALUES(personal_id),nota_personal=VALUES(nota_personal),comentario=VALUES(comentario),melhoria=VALUES(melhoria)');
  $s->execute([$uid,$gid,$note,$service,$structure,$pid,$pn,$comment,$improve]);respond(['ok'=>true]);
 }
 fail('Rota não encontrada.',404);
}catch(Throwable $e){error_log('FitVoz: '.$e->getMessage());fail('Não foi possível acessar o banco. Confira Apache/MySQL no XAMPP, importe banco.sql e revise config.php.',503);}
