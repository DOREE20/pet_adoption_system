<?php
header('Content-Type: application/json');

define('H','localhost');
define('U','root');
define('P','');
define('D','petproject');

function db(){
  $c=new mysqli(H,U,P,D);
  if($c->connect_error) die(json_encode(['success'=>false]));
  $c->set_charset('utf8mb4');
  return $c;
}

$a=$_POST['action']??$_GET['action']??'';

switch($a){
case 'register':r();break;
case 'login':l();break;
case 'get_slots':gs();break;
case 'book_playtime':bp();break;
case 'my_appointments':ma();break;
case 'pest_booking':pb();break;
case 'feedback':fb();break;
case 'avg_feedback':avg();break;
case 'get_pets':gp();break;
case 'get_feedback_avg':gfa();break;
case 'get_user_data':gud();break;
default:echo json_encode(['success'=>false]);
}

function r(){
$d=db();
$f=trim($_POST['firstName']??'');
$l=trim($_POST['lastName']??'');
$e=trim($_POST['email']??'');
$fn=trim($_POST['flatNo']??'');
$b=trim($_POST['building']??'');
$c=trim($_POST['city']??'');
$p=trim($_POST['pincode']??'');
$pw=$_POST['password']??'';

if(!$f||!$l||!$e||!$fn||!$b||!$c||!$p||!$pw) die(json_encode(['success'=>false]));

$s=$d->prepare('SELECT id FROM users WHERE email=?');
$s->bind_param('s',$e);$s->execute();$s->store_result();
if($s->num_rows){die(json_encode(['success'=>false]));}

$h=password_hash($pw,PASSWORD_BCRYPT);

$s=$d->prepare('INSERT INTO users(first_name,last_name,email,flat_no,building,city,pincode,password) VALUES(?,?,?,?,?,?,?,?)');
$s->bind_param('ssssssss',$f,$l,$e,$fn,$b,$c,$p,$h);
echo json_encode(['success'=>$s->execute()]);
}

function l(){
$d=db();
$e=trim($_POST['email']??'');
$p=$_POST['password']??'';

$s=$d->prepare('SELECT * FROM users WHERE email=?');
$s->bind_param('s',$e);$s->execute();
$r=$s->get_result()->fetch_assoc();

if(!$r||!password_verify($p,$r['password'])) die(json_encode(['success'=>false]));

echo json_encode([
'success'=>true,
'user'=>[
'id'=>$r['id'],
'firstName'=>$r['first_name'],
'lastName'=>$r['last_name'],
'email'=>$r['email'],
'city'=>$r['city']
]
]);
}

function gs(){
$d=db();
$date=$_GET['date']??date('Y-m-d');
$t=['10:00 AM – 11:00 AM','02:00 PM – 03:00 PM'];
$o=[];

foreach($t as $x){
$s=$d->prepare('SELECT COUNT(*) c FROM playtime_bookings WHERE date=? AND slot=?');
$s->bind_param('ss',$date,$x);$s->execute();
$c=$s->get_result()->fetch_assoc()['c'];
$o[]=['time'=>$x,'booked'=>(int)$c];
}
echo json_encode(['success'=>true,'slots'=>$o]);
}

function bp(){
$d=db();
$u=(int)($_POST['user_id']??0);
$date=$_POST['date']??'';
$p=$_POST['pet_type']??'';
$slt=$_POST['slot']??'';
$pe=(int)($_POST['people']??1);

$s=$d->prepare('SELECT COUNT(*) c FROM playtime_bookings WHERE date=? AND slot=?');
$s->bind_param('ss',$date,$slt);$s->execute();
if($s->get_result()->fetch_assoc()['c']>=2) die(json_encode(['success'=>false]));

$s=$d->prepare('INSERT INTO playtime_bookings(user_id,date,slot,pet_type,people) VALUES(?,?,?,?,?)');
$s->bind_param('isssi',$u,$date,$slt,$p,$pe);
echo json_encode(['success'=>$s->execute()]);
}

function ma(){
$d=db();
$u=(int)($_GET['user_id']??0);
$s=$d->prepare('SELECT date,slot,pet_type,people FROM playtime_bookings WHERE user_id=? ORDER BY date DESC');
$s->bind_param('i',$u);$s->execute();
$r=$s->get_result();$a=[];
while($x=$r->fetch_assoc())$a[]=$x;
echo json_encode(['success'=>true,'appointments'=>$a]);
}

function pb(){
$d=db();
$u=(int)($_POST['user_id']??0);
$f=$_POST['flat']??'';
$b=$_POST['building']??'';
$c=$_POST['city']??'';
$p=$_POST['pincode']??'';
$ps=$_POST['pests']??'';
$date=$_POST['date']??'';
$t=$_POST['time']??'';

$s=$d->prepare('INSERT INTO pest_bookings(user_id,flat_no,building,city,pincode,pests,date,time_slot) VALUES(?,?,?,?,?,?,?,?)');
$s->bind_param('isssssss',$u,$f,$b,$c,$p,$ps,$date,$t);
echo json_encode(['success'=>$s->execute()]);
}

function fb(){
$d=db();
$e=$_POST['email']??'';
$s=$_POST['service']??'';
$r=(int)($_POST['rating']??0);
$m=$_POST['message']??'';

if(!$e||!$s||!$r||!$m) die(json_encode(['success'=>false]));

$q=$d->prepare('INSERT INTO feedback(email,service,rating,message) VALUES(?,?,?,?)');
$q->bind_param('ssis',$e,$s,$r,$m);
echo json_encode(['success'=>$q->execute()]);
}

function avg(){
$d=db();
$r=$d->query('SELECT ROUND(AVG(rating),2) a FROM feedback')->fetch_assoc();
echo json_encode(['success'=>true,'avg'=>$r['a']??0]);
}

function gp(){
$d=db();
$type=$_GET['type']??'all';
$where=$type==='all' ? '1=1' : 'type=?';
$sql="SELECT * FROM pets WHERE $where AND featured=0 ORDER BY RAND() LIMIT 16";
$stmt=$d->prepare($sql);
if($type!=='all') $stmt->bind_param('s',$type);
$stmt->execute();
$r=$stmt->get_result();
$pets=[];
while($row=$r->fetch_assoc()) $pets[]=$row;
echo json_encode(['success'=>true,'pets'=>$pets]);
}

function gfa(){
$d=db();
$r=$d->query('SELECT ROUND(AVG(rating),2) as avg_rating FROM feedback')->fetch_assoc();
echo json_encode(['success'=>true,'avg_rating'=>$r['avg_rating']??0]);
}

function gud(){
  $d=db();
  $u=(int)($_GET['user_id']??0);
  
  // User address
  $s=$d->prepare('SELECT flat_no,building,city,pincode FROM users WHERE id=?');
  $s->bind_param('i',$u);
  $s->execute();
  $addr=$s->get_result()->fetch_assoc() ?: [];
  
  // Playtime
  $s=$d->prepare('SELECT date,slot,pet_type,people FROM playtime_bookings WHERE user_id=? ORDER BY created_at DESC LIMIT 5');
  $s->bind_param('i',$u);
  $s->execute();
  $play=$s->get_result()->fetch_all(MYSQLI_ASSOC);
  
  // Pest
  $s=$d->prepare('SELECT pests,date,time_slot FROM pest_bookings WHERE user_id=? ORDER BY created_at DESC LIMIT 5');
  $s->bind_param('i',$u);
  $s->execute();
  $pest=$s->get_result()->fetch_all(MYSQLI_ASSOC);
  
  // Shopping (new table - add status)
  $s=$d->prepare('SELECT pet_name,date,status FROM shopping_bookings WHERE user_id=? ORDER BY date DESC LIMIT 5');
  $s->bind_param('i',$u);
  $s->execute();
  $shop=$s->get_result()->fetch_all(MYSQLI_ASSOC);
  
  echo json_encode([
    'success'=>true,
    'playtime'=>$play,
    'pest'=>$pest,
    'shopping'=>$shop,
    'address'=>$addr
  ]);
}
?>


