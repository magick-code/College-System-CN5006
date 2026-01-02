<html>
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Metropolitan College</title>

 <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
crossorigin=""
/>

<link rel="stylesheet" href="style.css">

</head>
<body>


<?php
//ξεκίναει η session για να μπορεί το header να αλλάξει αν ο χρήστης είναι συνδεδεμένος
session_start();
include "header.php";?>


<h1>Καλωσορίσατε στο Metropolitan College</h1>

<section class="container">
<div class="box">
<h2>Το Campus μας</h2>
<img src="photos/mc-campus1.png" alt="Picture of the campus in Rhodes" >
<p>Το Μητροπολιτικό Κολλέγιο διαθέτει 8 κτίρια σε όλη την Ελλάδα.</p>
</div>
<div class="box">
<h2>Σπουδές στο Κολλέγιο</h2>
<img src="photos/mc-campus2.png" alt="Picture from inside the campus in Rhodes">
<p>Το Μητροπολιτικό Κολλέγιο προσφέρει σπουδές υψηλού ακαδημαϊκού επιπέδου.</p>

<br>
</div>
</section>



<h2>Η τοποθεσία του κολλεγίου μας:</h2>
<div class ="map-container">
<div id="map"></div>
</div>


<script
  src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
  integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
  crossorigin="">
</script>

<script>


//αρχικοποίει την τοποθεσία πάνω στον χάρτη και ρυθμίζει το zoom

var map = L.map('map').setView([36.449995, 28.222962], 19);

L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
}).addTo(map);



//ορίζει την εικόνα "mc-marker.png" ώς το marker του χάρτη
var mcIcon = L.icon({
  iconUrl: 'photos/mc-marker.png',
  shadowUrl: 'photos/mc-marker-shadow.png',


  //ρυθμίζει τις ιδιότητες του marker όπως το μέγεθος
    iconSize:     [50, 50],
    shadowSize:   [69, 40],
    iconAnchor:   [25, 50],
    shadowAnchor: [15, 40],
    popupAnchor:  [20, -100]
});

L.marker([36.449995, 28.222962], {icon: mcIcon}).addTo(map)
    .bindPopup('Μητροπολιτικό Κολλεγίο Ρόδου.')
    .openPopup();
</script>
<br>
<?php include "footer.php";?>



</body>
</html>
