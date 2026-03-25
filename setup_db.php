<?php

define('DB_HOST','localhost');
define('DB_USER','root');
define('DB_PASS','');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
if ($conn->connect_error) die("Connection failed");

$conn->query("CREATE DATABASE IF NOT EXISTS pet_adoption CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$conn->select_db('pet_adoption');

$conn->query("
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  first_name VARCHAR(100) NOT NULL,
  last_name VARCHAR(100) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  flat_no VARCHAR(50) NOT NULL,
  building VARCHAR(150) NOT NULL,
  street VARCHAR(150) DEFAULT '',
  area VARCHAR(150) DEFAULT '',
  city VARCHAR(100) NOT NULL,
  pincode CHAR(6) NOT NULL,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
");

$conn->query("
CREATE TABLE IF NOT EXISTS playtime_bookings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  date DATE NOT NULL,
  slot VARCHAR(50) NOT NULL,
  pet_type VARCHAR(100) NOT NULL,
  people INT DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
");

$conn->query("
CREATE TABLE IF NOT EXISTS pest_bookings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT DEFAULT 0,
  flat_no VARCHAR(50) NOT NULL,
  building VARCHAR(150) NOT NULL,
  city VARCHAR(100) NOT NULL,
  pincode CHAR(6) NOT NULL,
  pests TEXT NOT NULL,
  date DATE NOT NULL,
  time_slot VARCHAR(80) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
");



$conn->query("
CREATE TABLE IF NOT EXISTS feedback (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  email VARCHAR(255) NOT NULL,
  service VARCHAR(100) NOT NULL,
  rating TINYINT NOT NULL,
  message TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE(email)
) ENGINE=InnoDB;
");

$conn->query("
CREATE TABLE IF NOT EXISTS pets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  type VARCHAR(50) NOT NULL,
  name VARCHAR(100) NOT NULL,
  img_url TEXT NOT NULL,
  description TEXT,
  food VARCHAR(100),
  allergy VARCHAR(100),
  offer VARCHAR(100),
  expert_phone VARCHAR(20),
  video_url TEXT,
  featured TINYINT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
");

// Sample data from catalogue
$pets_data = [
  ['dog', 'Bruno', 'https://images.unsplash.com/photo-1587300003388-59208cc962cb?w=500&auto=format&fit=crop', 'Friendly dog', 'Chicken rice', 'Dust', '10% discount', '98765432', 'https://youtube.com/watch?v=1'],
  ['dog', 'Max', 'https://images.unsplash.com/photo-1552053831-71594a27632d?w=500&auto=format&fit=crop', 'Active dog', 'Dry food', 'None', 'Free food card', '87654321', 'https://youtube.com/watch?v=2'],
  ['dog', 'Rocky', 'https://images.unsplash.com/photo-1589941013453-ec89f33b5e95?w=500&auto=format&fit=crop', 'Strong dog', 'Meat', 'None', 'None', '76543210', 'https://youtube.com/watch?v=3'],
  ['dog', 'Bella', 'https://images.unsplash.com/photo-1596492784531-6e6eb5ea9993?w=500&auto=format&fit=crop', 'Playful dog', 'Veg mix', 'Heat', 'Free toy', '65432109', 'https://youtube.com/watch?v=4'],
  ['cat', 'Luna', 'https://images.unsplash.com/photo-1533743983669-94fa5c4338ec?w=500&auto=format&fit=crop', 'Calm cat', 'Fish', 'Milk', 'None', '54321098', 'https://youtube.com/watch?v=5'],
  ['cat', 'Tiger', 'https://images.unsplash.com/photo-1555685812-4b8f286fd0f0?w=500&auto=format&fit=crop', 'Smart cat', 'Chicken', 'None', '10% discount', '43210987', 'https://youtube.com/watch?v=6'],
  ['cat', 'Milo', 'https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?w=500&auto=format&fit=crop', 'Cute cat', 'Fish', 'Dust', 'None', '32109876', 'https://youtube.com/watch?v=7'],
  ['cat', 'Simba', 'https://images.unsplash.com/photo-1574158622682-e40e69881006?w=500&auto=format&fit=crop', 'Lazy cat', 'Milk', 'Cold', 'Free food', '21098765', 'https://youtube.com/watch?v=8'],
  ['rabbit', 'Coco', 'https://images.unsplash.com/photo-1548199973-03cce0bbc87b?w=500&auto=format&fit=crop&q=60', 'Cute rabbit', 'Carrots', 'Heat', 'None', '10987654', 'https://youtube.com/watch?v=9'],
  ['rabbit', 'Snowy', 'https://images.unsplash.com/photo-1585110396000-c9ffd4e4b308?w=500&auto=format&fit=crop', 'Soft rabbit', 'Leaves', 'None', 'Free toy', '19876543', 'https://youtube.com/watch?v=10'],
  ['rabbit', 'Bunny', 'https://images.unsplash.com/photo-1568640347023-a616a30bc3bd?w=500&auto=format&fit=crop', 'Jumping rabbit', 'Veggies', 'Dust', 'None', '28765432', 'https://youtube.com/watch?v=11'],
  ['rabbit', 'Fluffy', 'https://images.unsplash.com/photo-1518791841217-8f162f1e1131?w=500&auto=format&fit=crop', 'Fluffy rabbit', 'Grass', 'None', '10% discount', '37654321', 'https://youtube.com/watch?v=12'],
  ['bird', 'Rio', 'https://images.unsplash.com/photo-1591198936750-16d8e15edb9e?w=500&auto=format&fit=crop', 'Talking bird', 'Seeds', 'Cold', 'Free toy', '46543210', 'https://youtube.com/watch?v=13'],
  ['bird', 'Tweety', 'https://images.unsplash.com/photo-1552728089-57bdde30beb3?w=500&auto=format&fit=crop', 'Happy bird', 'Seeds', 'Heat', 'None', '55432109', 'https://youtube.com/watch?v=14'],
  ['bird', 'Sky', 'https://images.unsplash.com/photo-1501706362039-c6e80948bb5c?w=500&auto=format&fit=crop', 'Flying bird', 'Fruits', 'None', '10% discount', '64321098', 'https://youtube.com/watch?v=15'],
  ['bird', 'Sunny', 'https://images.unsplash.com/photo-1502082553048-f009c37129b9?w=500&auto=format&fit=crop', 'Bright bird', 'Seeds', 'None', 'None', '73210987', 'https://youtube.com/watch?v=16']
];

foreach($pets_data as $p) {
  $stmt = $conn->prepare('INSERT IGNORE INTO pets (type, name, img_url, description, food, allergy, offer, expert_phone, video_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
  $stmt->bind_param('sssssssss', $p[0], $p[1], $p[2], $p[3], $p[4], $p[5], $p[6], $p[7], $p[8]);
  $stmt->execute();
}

echo "Database ready";



$conn->close();
?>