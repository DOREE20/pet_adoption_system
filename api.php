
<?php
session_start();
header('Content-Type: application/json');

define('H','localhost');
define('U','root');
define('P','');
define('D','petproject');
define('MASTER_PW','Manish@0312');

function db(){
  $c = new mysqli(H,U,P,D);
  if ($c->connect_error) die(json_encode(['success'=>false,'msg'=>'DB connection failed']));
  $c->set_charset('utf8mb4');
  return $c;
}

$a = $_POST['action'] ?? $_GET['action'] ?? '';

switch($a){
  case 'register':        r();   break;
  case 'login':           l();   break;
  case 'logout':          ulogout(); break;
  case 'get_slots':       gs();  break;
  case 'book_playtime':   bp();  break;
  case 'my_appointments': ma();  break;
  case 'pest_booking':    pb();  break;
  case 'feedback':        fb();  break;
  case 'avg_feedback':    avg(); break;
  case 'get_pets':        gp();  break;
  case 'get_feedback_avg':gfa(); break;
  case 'get_user_data':   gud(); break;
  // Employee
  case 'emp_login':       el();  break;
  case 'emp_logout':      elo(); break;
  case 'check_emp_session': ces(); break;
  case 'get_all_appts':   gaa(); break;
  case 'update_appt':     ua();  break;
  case 'get_employee_actions': gea(); break;
  // Vet
  case 'vet_booking':     vbook(); break;
  case 'get_vet_bookings':gvb(); break;
  default: echo json_encode(['success'=>false,'msg'=>'Unknown action']);
}

// ─── USER AUTH ────────────────────────────────────────────────────────────────

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

  if(!$f||!$l||!$e||!$fn||!$b||!$c||!$p||!$pw)
    die(json_encode(['success'=>false,'msg'=>'All fields required']));

  $s=$d->prepare('SELECT id FROM users WHERE email=?');
  $s->bind_param('s',$e); $s->execute(); $s->store_result();
  if($s->num_rows) die(json_encode(['success'=>false,'msg'=>'Email already registered']));

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
  $s->bind_param('s',$e); $s->execute();
  $row=$s->get_result()->fetch_assoc();

  if(!$row||!password_verify($p,$row['password']))
    die(json_encode(['success'=>false,'msg'=>'Invalid email or password']));

  $user=[
    'id'        =>$row['id'],
    'firstName' =>$row['first_name'],
    'lastName'  =>$row['last_name'],
    'email'     =>$row['email'],
    'city'      =>$row['city'],
    'flatNo'    =>$row['flat_no'],
    'building'  =>$row['building']
  ];
  // PHP session for user
  $_SESSION['user']=$user;

  echo json_encode(['success'=>true,'user'=>$user]);
}

function ulogout(){
  unset($_SESSION['user']);
  echo json_encode(['success'=>true]);
}

// ─── PLAYTIME ────────────────────────────────────────────────────────────────

function gs(){
  $d=db();
  $date=$_GET['date']??date('Y-m-d');
  $slots=['10:00 AM – 11:00 AM','02:00 PM – 03:00 PM'];
  $out=[];
  foreach($slots as $sl){
    $s=$d->prepare('SELECT COUNT(*) c FROM playtime_bookings WHERE date=? AND slot=?');
    $s->bind_param('ss',$date,$sl); $s->execute();
    $c=$s->get_result()->fetch_assoc()['c'];
    $out[]=['time'=>$sl,'booked'=>(int)$c];
  }
  echo json_encode(['success'=>true,'slots'=>$out]);
}

function bp(){
  $d=db();
  $u=(int)($_POST['user_id']??0);
  $date=$_POST['date']??'';
  $pet=$_POST['pet_type']??'';
  $slt=$_POST['slot']??'';
  $ppl=(int)($_POST['people']??1);

  $s=$d->prepare('SELECT COUNT(*) c FROM playtime_bookings WHERE date=? AND slot=?');
  $s->bind_param('ss',$date,$slt); $s->execute();
  if($s->get_result()->fetch_assoc()['c']>=2)
    die(json_encode(['success'=>false,'msg'=>'Slot full']));

  $s=$d->prepare('INSERT INTO playtime_bookings(user_id,date,slot,pet_type,people) VALUES(?,?,?,?,?)');
  $s->bind_param('isssi',$u,$date,$slt,$pet,$ppl);
  echo json_encode(['success'=>$s->execute()]);
}

function ma(){
  $d=db();
  $u=(int)($_GET['user_id']??0);
  $s=$d->prepare('SELECT date,slot,pet_type,people,status FROM playtime_bookings WHERE user_id=? ORDER BY date DESC');
  $s->bind_param('i',$u); $s->execute();
  $r=$s->get_result(); $a=[];
  while($x=$r->fetch_assoc()) $a[]=$x;
  echo json_encode(['success'=>true,'appointments'=>$a]);
}

// ─── PEST ────────────────────────────────────────────────────────────────────

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

// ─── FEEDBACK ────────────────────────────────────────────────────────────────

function fb(){
  $d=db();
  $e=$_POST['email']??'';
  $s=$_POST['service']??'';
  $r=(int)($_POST['rating']??0);
  $m=$_POST['message']??'';

  if(!$e||!$s||!$r||!$m) die(json_encode(['success'=>false,'msg'=>'All fields required']));

  $q=$d->prepare('INSERT INTO feedback(email,service,rating,message) VALUES(?,?,?,?)');
  $q->bind_param('ssis',$e,$s,$r,$m);
  echo json_encode(['success'=>$q->execute()]);
}

function avg(){
  $d=db();
  $r=$d->query('SELECT ROUND(AVG(rating),2) a FROM feedback')->fetch_assoc();
  echo json_encode(['success'=>true,'avg'=>$r['a']??0]);
}

function gfa(){
  $d=db();
  $r=$d->query('SELECT ROUND(AVG(rating),2) as avg_rating FROM feedback')->fetch_assoc();
  echo json_encode(['success'=>true,'avg_rating'=>$r['avg_rating']??0]);
}

// ─── PETS / CATALOGUE ────────────────────────────────────────────────────────

function gp(){
  $d=db();
  $type=$_GET['type']??'all';
  if($type==='all'){
    $sql="SELECT * FROM pets ORDER BY RAND() LIMIT 16";
    $stmt=$d->prepare($sql);
  } else {
    $sql="SELECT * FROM pets WHERE type=? ORDER BY RAND() LIMIT 16";
    $stmt=$d->prepare($sql);
    $stmt->bind_param('s',$type);
  }
  $stmt->execute();
  $r=$stmt->get_result();
  $pets=[];
  while($row=$r->fetch_assoc()) $pets[]=$row;
  echo json_encode(['success'=>true,'pets'=>$pets]);
}

// ─── USER DATA (dashboard) ───────────────────────────────────────────────────

function gud(){
  $d=db();
  $u=(int)($_GET['user_id']??0);

  $s=$d->prepare('SELECT flat_no,building,city,pincode FROM users WHERE id=?');
  $s->bind_param('i',$u); $s->execute();
  $addr=$s->get_result()->fetch_assoc()?:[];

  $s=$d->prepare('SELECT date,slot,pet_type,people,status FROM playtime_bookings WHERE user_id=? ORDER BY created_at DESC LIMIT 5');
  $s->bind_param('i',$u); $s->execute();
  $play=$s->get_result()->fetch_all(MYSQLI_ASSOC);

  $s=$d->prepare('SELECT pests,date,time_slot,status FROM pest_bookings WHERE user_id=? ORDER BY created_at DESC LIMIT 5');
  $s->bind_param('i',$u); $s->execute();
  $pest=$s->get_result()->fetch_all(MYSQLI_ASSOC);

  $s=$d->prepare('SELECT pet_name,date,status FROM shopping_bookings WHERE user_id=? ORDER BY date DESC LIMIT 5');
  $s->bind_param('i',$u); $s->execute();
  $shop=$s->get_result()->fetch_all(MYSQLI_ASSOC);

  $s=$d->prepare('SELECT pet_name,pet_type,date,time_slot,status,vet_notes,medicine FROM vet_bookings WHERE user_id=? ORDER BY created_at DESC LIMIT 5');
  $s->bind_param('i',$u); $s->execute();
  $vet=$s->get_result()->fetch_all(MYSQLI_ASSOC);

  echo json_encode([
    'success'  =>true,
    'playtime' =>$play,
    'pest'     =>$pest,
    'shopping' =>$shop,
    'vet'      =>$vet,
    'address'  =>$addr
  ]);
}

// ─── EMPLOYEE AUTH ────────────────────────────────────────────────────────────

function el(){
  $master = $_POST['master_pw']??'';
  $name   = trim($_POST['emp_name']??'');
  $pw     = $_POST['emp_pw']??'';

  if(empty($name)) die(json_encode(['success'=>false,'msg'=>'Enter employee name']));
  if($master !== MASTER_PW) die(json_encode(['success'=>false,'msg'=>'Invalid master password']));

  // Expected password: capitalize first letter of each word, append @123
  $normalised = ucwords(strtolower($name));
  $expected   = $normalised.'@123';
  if($pw !== $expected)
    die(json_encode(['success'=>false,'msg'=>'Invalid employee password. Use '.$normalised.'@123']));

  $d=db();
  $s=$d->prepare('SELECT * FROM employees WHERE LOWER(name)=LOWER(?)');
  $s->bind_param('s',$name); $s->execute();
  $emp=$s->get_result()->fetch_assoc();

  if(!$emp){
    $s2=$d->prepare('INSERT INTO employees(name) VALUES(?)');
    $s2->bind_param('s',$normalised); $s2->execute();
    $emp=['id'=>$d->insert_id,'name'=>$normalised];
  }

  $_SESSION['employee']=['id'=>(int)$emp['id'],'name'=>$emp['name']];
  setcookie('empSession', json_encode(['id'=>$emp['id'],'name'=>$emp['name']]), time()+86400, '/', '', false, true);

  echo json_encode(['success'=>true,'employee'=>['id'=>(int)$emp['id'],'name'=>$emp['name']]]);
}

function elo(){
  unset($_SESSION['employee']);
  setcookie('empSession','',time()-3600,'/');
  echo json_encode(['success'=>true]);
}

function ces(){
  if(isset($_SESSION['employee']))
    echo json_encode(['success'=>true,'employee'=>$_SESSION['employee']]);
  else
    echo json_encode(['success'=>false]);
}

// ─── EMPLOYEE – GET ALL APPOINTMENTS ────────────────────────────────────────

function gaa(){
  if(!isset($_SESSION['employee'])) die(json_encode(['success'=>false,'msg'=>'Unauthorized']));
  $d=db();

  $play=$d->query('
    SELECT pb.id,pb.date,pb.slot,pb.pet_type,pb.people,pb.status,pb.created_at,
           u.first_name,u.last_name,u.email
    FROM playtime_bookings pb
    JOIN users u ON pb.user_id=u.id
    ORDER BY pb.created_at DESC
  ')->fetch_all(MYSQLI_ASSOC);

  $shop=$d->query('
    SELECT sb.id,sb.pet_name,sb.date,sb.status,sb.created_at,
           u.first_name,u.last_name,u.email
    FROM shopping_bookings sb
    JOIN users u ON sb.user_id=u.id
    ORDER BY sb.created_at DESC
  ')->fetch_all(MYSQLI_ASSOC);

  $pest=$d->query('
    SELECT pb.id,pb.flat_no,pb.building,pb.city,pb.pests,pb.date,pb.time_slot,pb.status,pb.created_at,
           u.first_name,u.last_name,u.email
    FROM pest_bookings pb
    LEFT JOIN users u ON pb.user_id=u.id
    ORDER BY pb.created_at DESC
  ')->fetch_all(MYSQLI_ASSOC);

  $vet=$d->query('
    SELECT vb.id,vb.pet_name,vb.pet_type,vb.situation,vb.date,vb.time_slot,vb.status,
           vb.vet_notes,vb.medicine,vb.created_at,
           u.first_name,u.last_name,u.email
    FROM vet_bookings vb
    JOIN users u ON vb.user_id=u.id
    ORDER BY vb.created_at DESC
  ')->fetch_all(MYSQLI_ASSOC);

  echo json_encode(['success'=>true,'playtime'=>$play,'shopping'=>$shop,'pest'=>$pest,'vet'=>$vet]);
}

// ─── EMPLOYEE – UPDATE APPOINTMENT STATUS ────────────────────────────────────

function ua(){
  if(!isset($_SESSION['employee'])) die(json_encode(['success'=>false,'msg'=>'Unauthorized']));

  $d       = db();
  $type    = $_POST['type']    ?? '';
  $id      = (int)($_POST['id']      ?? 0);
  $status  = $_POST['status']  ?? '';
  $notes   = $_POST['notes']   ?? '';
  $ndate   = $_POST['new_date']?? null;
  $vnotes  = $_POST['vet_notes']??'';
  $medicine= $_POST['medicine'] ??'';
  $emp     = $_SESSION['employee'];

  $tables=['playtime'=>'playtime_bookings','shopping'=>'shopping_bookings','pest'=>'pest_bookings','vet'=>'vet_bookings'];
  $tbl=$tables[$type]??'';
  if(!$tbl||!$id||!$status) die(json_encode(['success'=>false,'msg'=>'Missing params']));

  // Fetch user name for the log
  $user_name='';
  $join_col = $type==='pest' ? 'pb' : ($type==='vet'?'vb':($type==='shopping'?'sb':'pb'));
  // Simple approach: query per type
  if($type==='playtime'){
    $uq=$d->prepare('SELECT u.first_name,u.last_name FROM playtime_bookings pb JOIN users u ON pb.user_id=u.id WHERE pb.id=?');
  } elseif($type==='shopping'){
    $uq=$d->prepare('SELECT u.first_name,u.last_name FROM shopping_bookings sb JOIN users u ON sb.user_id=u.id WHERE sb.id=?');
  } elseif($type==='pest'){
    $uq=$d->prepare('SELECT u.first_name,u.last_name FROM pest_bookings pb LEFT JOIN users u ON pb.user_id=u.id WHERE pb.id=?');
  } elseif($type==='vet'){
    $uq=$d->prepare('SELECT u.first_name,u.last_name FROM vet_bookings vb JOIN users u ON vb.user_id=u.id WHERE vb.id=?');
  }
  if(isset($uq)){
    $uq->bind_param('i',$id); $uq->execute();
    $ur=$uq->get_result()->fetch_assoc();
    if($ur) $user_name=$ur['first_name'].' '.$ur['last_name'];
  }

  // Update the appointment
  if($type==='vet'){
    $s=$d->prepare("UPDATE vet_bookings SET status=?,vet_notes=?,medicine=? WHERE id=?");
    $s->bind_param('sssi',$status,$vnotes,$medicine,$id);
  } elseif($status==='rescheduled' && $ndate){
    $s=$d->prepare("UPDATE $tbl SET status=?,date=? WHERE id=?");
    $s->bind_param('ssi',$status,$ndate,$id);
  } else {
    $s=$d->prepare("UPDATE $tbl SET status=? WHERE id=?");
    $s->bind_param('si',$status,$id);
  }
  $ok=$s->execute();

  // Map status to action type
  $action = in_array($status,['approved','completed']) ? 'approved' : ($status==='rescheduled' ? 'rescheduled' : 'rejected');

  // Log employee action
  $s2=$d->prepare('INSERT INTO employee_actions(employee_id,employee_name,action_type,appointment_type,appointment_id,user_name,notes,new_date) VALUES(?,?,?,?,?,?,?,?)');
  $s2->bind_param('isssisss',$emp['id'],$emp['name'],$action,$type,$id,$user_name,$notes,$ndate);
  $s2->execute();

  echo json_encode(['success'=>$ok]);
}

// ─── EMPLOYEE – GET OWN ACTIVITY LOG ────────────────────────────────────────

function gea(){
  if(!isset($_SESSION['employee'])) die(json_encode(['success'=>false,'msg'=>'Unauthorized']));
  $d=db();
  $eid=(int)$_SESSION['employee']['id'];
  $s=$d->prepare('SELECT * FROM employee_actions WHERE employee_id=? ORDER BY created_at DESC LIMIT 100');
  $s->bind_param('i',$eid); $s->execute();
  $rows=$s->get_result()->fetch_all(MYSQLI_ASSOC);
  echo json_encode(['success'=>true,'actions'=>$rows]);
}

// ─── VET BOOKINGS ────────────────────────────────────────────────────────────

function vbook(){
  $d=db();
  $u   =(int)($_POST['user_id']  ??0);
  $pn  =$_POST['pet_name']  ??'';
  $pt  =$_POST['pet_type']  ??'';
  $sit =$_POST['situation'] ??'';
  $date=$_POST['date']      ??'';
  $time=$_POST['time_slot'] ??'';

  if(!$u||!$pn||!$pt||!$sit||!$date||!$time)
    die(json_encode(['success'=>false,'msg'=>'All fields required']));

  $s=$d->prepare('INSERT INTO vet_bookings(user_id,pet_name,pet_type,situation,date,time_slot) VALUES(?,?,?,?,?,?)');
  $s->bind_param('isssss',$u,$pn,$pt,$sit,$date,$time);
  echo json_encode(['success'=>$s->execute()]);
}

function gvb(){
  $d=db();
  $u=(int)($_GET['user_id']??0);
  $s=$d->prepare('SELECT * FROM vet_bookings WHERE user_id=? ORDER BY created_at DESC');
  $s->bind_param('i',$u); $s->execute();
  $rows=$s->get_result()->fetch_all(MYSQLI_ASSOC);
  echo json_encode(['success'=>true,'bookings'=>$rows]);
}
?>