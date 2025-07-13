<?php
// File: about.php

require_once 'includes/config.php';
require_once 'includes/functions.php';
$page_title = 'Tentang Kami';
require_once 'includes/header.php';
?>

<div class="page-header">
    <h1>Tentang Rental Mobil Keren</h1>
    <p>Mengenal lebih dekat visi, misi, dan tim di belakang layar kami.</p>
</div>

<section class="about-us-section">
    <div class="container">
        <div class="about-content-row">
            <div class="about-text">
                <h2>Misi Kami</h2>
                <p>Misi kami adalah menyediakan solusi transportasi yang andal, aman, dan terjangkau bagi setiap pelanggan. Kami percaya bahwa perjalanan yang nyaman adalah kunci untuk pengalaman yang tak terlupakan. Oleh karena itu, kami berkomitmen untuk selalu memberikan pelayanan terbaik, armada yang terawat, dan proses penyewaan yang transparan.</p>
                
                <h2>Visi Kami</h2>
                <p>Menjadi perusahaan rental mobil terdepan di Indonesia yang dikenal karena kualitas pelayanan, inovasi teknologi, dan kepuasan pelanggan yang tinggi.</p>
            </div>
            <div class="about-image">
                <img src="assets/img/team-photo.jpg" alt="Tim Rental Mobil Keren">
            </div>
        </div>
    </div>
</section>

<?php
require_once 'includes/footer.php';
?>