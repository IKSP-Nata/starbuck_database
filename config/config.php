<?php
date_default_timezone_set('Asia/Makassar');
$koneksi = mysqli_connect("localhost", "root", "", "starbuck") or die("Could not connect to database");
mysqli_query($koneksi, "SET time_zone = '+08:00'");

