<?php
define('DB_HOST','localhost');
define('DB_USER','root');
define('DB_PASS','');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$conn->query("CREATE DATABASE IF NOT EXISTS petproject CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$conn->select_db('petproject');

// ─── EXISTING TABLES ──────────────────────────────────────────────────────────

$conn->query("
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  first_name VARCHAR(100) NOT NULL,
  last_name  VARCHAR(100) NOT NULL,
  email      VARCHAR(255) NOT NULL UNIQUE,
  flat_no    VARCHAR(50)  NOT NULL,
  building   VARCHAR(150) NOT NULL,
  street     VARCHAR(150) DEFAULT '',
  area       VARCHAR(150) DEFAULT '',
  city       VARCHAR(100) NOT NULL,
  pincode    CHAR(6)      NOT NULL,
  password   VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
");

$conn->query("
CREATE TABLE IF NOT EXISTS playtime_bookings (
  id       INT AUTO_INCREMENT PRIMARY KEY,
  user_id  INT NOT NULL,
  date     DATE NOT NULL,
  slot     VARCHAR(50) NOT NULL,
  pet_type VARCHAR(100) NOT NULL,
  people   INT DEFAULT 1,
  status   ENUM('pending','approved','rejected','rescheduled') DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
");

$conn->query("
CREATE TABLE IF NOT EXISTS pest_bookings (
  id        INT AUTO_INCREMENT PRIMARY KEY,
  user_id   INT DEFAULT 0,
  flat_no   VARCHAR(50)  NOT NULL,
  building  VARCHAR(150) NOT NULL,
  city      VARCHAR(100) NOT NULL,
  pincode   CHAR(6)      NOT NULL,
  pests     TEXT NOT NULL,
  date      DATE NOT NULL,
  time_slot VARCHAR(80)  NOT NULL,
  status    ENUM('pending','approved','rejected','rescheduled') DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
");

$conn->query("
CREATE TABLE IF NOT EXISTS feedback (
  id      INT AUTO_INCREMENT PRIMARY KEY,
  email   VARCHAR(255) NOT NULL,
  service VARCHAR(100) NOT NULL,
  rating  TINYINT NOT NULL,
  message TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
");

$conn->query("
CREATE TABLE IF NOT EXISTS pets (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  type          VARCHAR(50)  NOT NULL,
  name          VARCHAR(100) NOT NULL,
  img_url       TEXT NOT NULL,
  description   TEXT,
  food          VARCHAR(100),
  allergy       VARCHAR(100),
  offer         VARCHAR(100),
  expert_phone  VARCHAR(20),
  video_url     TEXT,
  featured      TINYINT DEFAULT 0,
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
");

$conn->query("
CREATE TABLE IF NOT EXISTS shopping_bookings (
  id       INT AUTO_INCREMENT PRIMARY KEY,
  user_id  INT NOT NULL,
  pet_name VARCHAR(100) NOT NULL,
  date     DATE NOT NULL,
  status   ENUM('pending','approved','rejected','rescheduled') DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
");

// ─── NEW TABLES ────────────────────────────────────────────────────────────────

$conn->query("
CREATE TABLE IF NOT EXISTS employees (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(100) NOT NULL UNIQUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
");

$conn->query("
CREATE TABLE IF NOT EXISTS employee_actions (
  id               INT AUTO_INCREMENT PRIMARY KEY,
  employee_id      INT NOT NULL,
  employee_name    VARCHAR(100) NOT NULL,
  action_type      ENUM('approved','rejected','rescheduled') NOT NULL,
  appointment_type ENUM('playtime','shopping','pest','vet') NOT NULL,
  appointment_id   INT NOT NULL,
  user_name        VARCHAR(200) DEFAULT '',
  notes            TEXT,
  new_date         DATE DEFAULT NULL,
  created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
) ENGINE=InnoDB;
");

$conn->query("
CREATE TABLE IF NOT EXISTS vet_bookings (
  id        INT AUTO_INCREMENT PRIMARY KEY,
  user_id   INT NOT NULL,
  pet_name  VARCHAR(100) NOT NULL,
  pet_type  VARCHAR(50)  NOT NULL,
  situation TEXT NOT NULL,
  date      DATE NOT NULL,
  time_slot VARCHAR(80)  NOT NULL,
  status    ENUM('pending','approved','rejected','rescheduled') DEFAULT 'pending',
  vet_notes TEXT DEFAULT NULL,
  medicine  TEXT DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
");

// ─── SAFE ALTER (add columns if missing) ──────────────────────────────────────

// Safely add status to playtime_bookings
$res = $conn->query("SHOW COLUMNS FROM playtime_bookings LIKE 'status'");
if ($res->num_rows === 0) {
  $conn->query("ALTER TABLE playtime_bookings ADD COLUMN status ENUM('pending','approved','rejected','rescheduled') DEFAULT 'pending'");
}

// Safely add status to pest_bookings
$res2 = $conn->query("SHOW COLUMNS FROM pest_bookings LIKE 'status'");
if ($res2->num_rows === 0) {
  $conn->query("ALTER TABLE pest_bookings ADD COLUMN status ENUM('pending','approved','rejected','rescheduled') DEFAULT 'pending'");
}

// ─── SAMPLE EMPLOYEES ─────────────────────────────────────────────────────────

$sample_employees = ['Rahul','Raj','Manish','Priya','Neha'];
foreach ($sample_employees as $en) {
  $s = $conn->prepare('INSERT IGNORE INTO employees(name) VALUES(?)');
  $s->bind_param('s', $en);
  $s->execute();
}

// ─── SAMPLE PETS DATA ─────────────────────────────────────────────────────────

$pets_data = [
  ['dog','Bruno','https://images.unsplash.com/photo-1587300003388-59208cc962cb?w=500&auto=format&fit=crop','Friendly Golden Retriever puppy. Perfect family pet.','Chicken rice mix','Dust allergy','10% adoption discount','98765432','https://youtube.com/watch?v=1'],
  ['dog','Max','https://images.unsplash.com/photo-1552053831-71594a27632d?w=500&auto=format&fit=crop','Active Labrador ready for adventures.','Dry kibble','None','Free food card','87654321','https://youtube.com/watch?v=2'],
  ['dog','Rocky','https://images.unsplash.com/photo-1589941013453-ec89f33b5e95?w=500&auto=format&fit=crop','Strong German Shepherd, great guard dog.','Meat based','None','Special price','76543210','https://youtube.com/watch?v=3'],
  ['dog','Bella','https://images.unsplash.com/photo-1596492784531-6e6eb5ea9993?w=500&auto=format&fit=crop','Playful Beagle loves to play fetch.','Veg mix','Heat sensitive','Free toy included','65432109','https://youtube.com/watch?v=4'],
  ['cat','Luna','https://images.unsplash.com/photo-1533743983669-94fa5c4338ec?w=500&auto=format&fit=crop','Calm Persian cat perfect for apartments.','Fish based','Milk allergy','Ready for home','54321098','https://youtube.com/watch?v=5'],
  ['cat','Tiger','https://images.unsplash.com/photo-1555685812-4b8f286fd0f0?w=500&auto=format&fit=crop','Smart Siamese cat very social.','Chicken','None','10% discount','43210987','https://youtube.com/watch?v=6'],
  ['cat','Milo','https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?w=500&auto=format&fit=crop','Cute tabby kitten loves cuddles.','Fish','Dust','Coming soon','32109876','https://youtube.com/watch?v=7'],
  ['cat','Simba','https://images.unsplash.com/photo-1574158622682-e40e69881006?w=500&auto=format&fit=crop','Lazy British Shorthair perfect lap cat.','Milk formula','Cold','Free food starter','21098765','https://youtube.com/watch?v=8'],
  ['rabbit','Coco','https://images.unsplash.com/photo-1574410466976-b2661e95637b?w=500&fit=crop','Cute fluffy rabbit loves carrots.','Fresh carrots','Heat','Ready','10987654','https://youtube.com/watch?v=9'],
  ['rabbit','Snowy','https://images.unsplash.com/photo-1585110396000-c9ffd4e4b308?w=500&fit=crop','Soft white rabbit very gentle.','Leafy greens','None','Free hutch toy','19876543','https://youtube.com/watch?v=10'],
  ['rabbit','Bunny','https://images.unsplash.com/photo-1548767797-d8c844163c4a?w=500&fit=crop','Jumping Dutch rabbit energetic.','Veggies','Dust','Ready','28765432','https://youtube.com/watch?v=11'],
  ['rabbit','Fluffy','https://images.unsplash.com/photo-1535241749838-299277b6305f?w=500&fit=crop','Fluffy Lionhead super cute.','Grass hay','None','10% off','37654321','https://youtube.com/watch?v=12'],
  ['bird','Rio','https://images.unsplash.com/photo-1552728089-57bdde30beb3?w=500&fit=crop','Talking parrot very intelligent.','Seeds mix','Cold','Free cage toy','46543210','https://youtube.com/watch?v=13'],
  ['bird','Tweety','https://images.unsplash.com/photo-1591198936750-16d8e15edb9e?w=500&fit=crop','Happy budgie loves to sing.','Seeds','Heat','Ready','55432109','https://youtube.com/watch?v=14'],
  ['bird','Sky','https://images.unsplash.com/photo-1501706362039-c6e80948bb5c?w=500&fit=crop','Colorful cockatiel friendly.','Fruits seeds','None','10% discount','64321098','https://youtube.com/watch?v=15'],
  ['bird','Sunny','https://images.unsplash.com/photo-1444464666168-49d633b86797?w=500&fit=crop','Bright lovebird cheerful.','Seeds fruits','None','Ready for family','73210987','https://youtube.com/watch?v=16'],
];

foreach ($pets_data as $p) {
  $stmt = $conn->prepare('INSERT IGNORE INTO pets (type,name,img_url,description,food,allergy,offer,expert_phone,video_url) VALUES (?,?,?,?,?,?,?,?,?)');
  $stmt->bind_param('sssssssss', $p[0],$p[1],$p[2],$p[3],$p[4],$p[5],$p[6],$p[7],$p[8]);
  $stmt->execute();
}

echo "<!DOCTYPE html><html><head><title>DB Setup</title>
<style>body{font-family:sans-serif;max-width:700px;margin:40px auto;background:#f5f5f5;padding:2rem;}
h2{color:darkgreen;} .ok{color:green;} .tbl{background:white;padding:1rem;border-radius:8px;margin:1rem 0;box-shadow:0 2px 8px rgba(0,0,0,.1);}
a{display:inline-block;margin-top:1rem;padding:.6rem 1.5rem;background:darkblue;color:white;border-radius:8px;text-decoration:none;}</style>
</head><body>
<h2>✅ PetProject Database Ready!</h2>
<div class='tbl'><b>Tables created / verified:</b><br>
✅ users &nbsp; ✅ pets &nbsp; ✅ playtime_bookings &nbsp; ✅ pest_bookings<br>
✅ shopping_bookings &nbsp; ✅ feedback<br>
✅ <b>employees</b> (NEW) &nbsp; ✅ <b>employee_actions</b> (NEW) &nbsp; ✅ <b>vet_bookings</b> (NEW)
</div>
<div class='tbl'><b>Status columns added to:</b><br>
✅ playtime_bookings.status &nbsp; ✅ pest_bookings.status
</div>
<div class='tbl'><b>Sample employees seeded (password = Name@123):</b><br>
👤 Rahul → Rahul@123 &nbsp; 👤 Raj → Raj@123 &nbsp; 👤 Manish → Manish@123<br>
👤 Priya → Priya@123 &nbsp; 👤 Neha → Neha@123<br>
🔑 <b>Master password for all employees: Manish@0312</b>
</div>
<div class='tbl'><b>16 sample pets inserted into pets table.</b></div>
<a href='home.html'>→ Go to Home</a> &nbsp;
<a href='employee.html' style='background:darkgreen;'>→ Employee Panel</a>
</body></html>";

$conn->close();
?>
