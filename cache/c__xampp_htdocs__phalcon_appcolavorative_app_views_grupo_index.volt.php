<?= $this->getContent() ?>
 <style type="text/css">}
 .input-field input:focus + label {
    color: #0288d1 !important;
  }

  .row .input-field input:focus {
    border-bottom: 1px solid #0288d1 !important;
    box-shadow: 0 1px 0 0 #0288d1 !important
  }

   body{
     font-family: 'Lato', sans-serif;
     font-family: 'Quicksand', sans-serif;
   }

   #div_1{
     background-color: #ffffff;

   }

 </style>


<br>

<div class="container">
  <ul id="slide-out" class="sidenav">
    <li><div class="user-view">
      <div class="background">
        <img src="https://i.ibb.co/8gsDx2h/thunderstorm-3440450-1280.jpg" class="">
      </div>
      <a href="#user"><img class="circle" src="https://i.ibb.co/9TZWXnW/square-250.jpg"></a>

      <a href="#name"><span class="white-text name">¡Bienvenido!</span></a>
      <a href="#email"><span class="white-text email"><?php echo $this->session->get('user')['Nombre']?></span></a>
    </div></li>

    <li><a class="subheader">Grupos</a></li>
    <li><a href="#!"><i class="material-icons">add</i>Crear</a></li>
    <li><a href="#!"><i class="material-icons">device_hub</i>Unirse</a></li>
    <li><div class="divider"></div></li>

    <li><a class="subheader">Mis Grupos</a></li>
    <?php
    	if ($grupos->count() > 0) {
    		foreach ($grupos as $grupo) {
    ?>
    			<li><a href="#!"> <?php echo $grupo->Nombre_G;?> </a></li>
    <?php
    		}
    	}
    ?>
    

    <li><div class="divider"></div></li>

    <li><a class="subheader">Salir</a></li>
    
    <li>
    	<?php 
    	echo $this->tag->linkTo("grupo/logout","<i class=\"material-icons\">add</i>Cerrar Sessión");
    	?>
    </li>

  </ul>
  <a href="#" data-target="slide-out" class="sidenav-trigger"><i class="small material-icons grey-text text-darken-1">menu</i></a>

</div>






      <h4 ALIGN="center">Grupo de Tesistas</h4>
      <br>

      <div class="container">
        <div class="row">
          <h5 ALIGN="center">Integrantes: </h5>
          <div class="col m12" ALIGN="center">
            <div class="chip">
              <img src="https://i.ibb.co/x8r6dDC/106420-man-512x512.png" >
              Integrante 1
            </div>
            <div class="chip">
              <img src="https://i.ibb.co/x8r6dDC/106420-man-512x512.png" >
              Integrante 2
            </div>
            <div class="chip">
              <img src="https://i.ibb.co/x8r6dDC/106420-man-512x512.png" >
              Integrante 3
            </div>
            <div class="chip">
              <img src="https://i.ibb.co/x8r6dDC/106420-man-512x512.png" >
              Integrante 4
            </div>
            <div class="chip">
              <img src="https://i.ibb.co/x8r6dDC/106420-man-512x512.png" >
              Integrante 5
            </div>
            <div class="chip">
              <img src="https://i.ibb.co/x8r6dDC/106420-man-512x512.png" >
              Integrante 6
            </div>
            <div class="chip">
              <img src="https://i.ibb.co/x8r6dDC/106420-man-512x512.png" >
              Integrante 7
            </div>
          </div>

          <br><br><br>
            <div class="row">
              <div class="col m7" id="div_1">
                <br><br><br><br><br><br><br><br><br><br>
                <br><br><br><br><br><br>
              </div>
              <div class="col m4 offset-m1" id="div_2">
                <br><br><br><br><br><br><br><br><br><br>
                <br><br><br><br><br><br>
              </div>

            </div>

        </div>
      </div>



      <!--JavaScript at end of body for optimized loading-->
      <script type="text/javascript" src="js/materialize.min.js"></script>

    <script type="text/javascript">
      document.oncontextmenu =  function(){return false;}
    </script>

    <script type="text/javascript">
       M.AutoInit();
    </script>
