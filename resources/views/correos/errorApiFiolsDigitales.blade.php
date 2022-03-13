<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>correo</title>
   <style>

   .titulo {
    color: #1e80b6;
    padding-top: 20px;
    padding-bottom: 10px;
    padding-left: 20px;
    padding-right: 20px;
    }

    .body{
     background-color: #ECECEC;
    }


    .div_contenido{
    color: #1e80b6;
    padding-top: 10px;
    padding-bottom: 10px;
    padding-left: 10px;
    padding-right: 10px;
    background-color: #ffffff !important;
   }

   </style>

</head>

<body class="body">

<div class="titulo" > <h3>Alumno que genero mensaje:  <?= $nombre;   ?> </h3></div>
<hr>
<div class=".div_contenido" > <?= $contenido;   ?></div>
<hr>
<p>
    Puede ponerse en contacto al siguiente correo electronico: <?= $correo ?>
</p>

</body>
</html>
