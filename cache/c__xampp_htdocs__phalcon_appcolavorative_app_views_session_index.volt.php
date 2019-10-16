<?= $this->getContent() ?>


  <style type="text/css">
    }

    .input-field input:focus+label {
      color: #0288d1 !important;
    }

    .row .input-field input:focus {
      border-bottom: 1px solid #0288d1 !important;
      box-shadow: 0 1px 0 0 #0288d1 !important
    }

    body {
      font-family: 'Lato', sans-serif;
      font-family: 'Quicksand', sans-serif;
    }


    #inputcentrado {
      text-align: center
    }
  </style>

  <style media="screen">
    .centrado-porcentual {
      position: absolute;
      left: 50%;
      top: 50%;
      transform: translate(-50%, -50%);
      -webkit-transform: translate(-50%, -50%);
    }
  </style>


  <br>

  <h3 class="grey-text text-darken-2 m12 s12" ALIGN="center">Espacio compartido</h3>
  <div class="container row centrado-porcentual">
    <div class="col m6 s12" ALIGN="center">
      <img src="https://i.ibb.co/GtSj6kh/share.png" width="300" height="300" class="responsive-img">
    </div>

    <div class="col m6 s12" ALIGN="center">
      <?php echo ($this->session->get('user'))? var_dump($this->session->get('user')):"";?>
           

      <?php echo $this->tag->form("Session/login"); ?>

        <h4 class="grey-text text-darken-3">Inicio de sesión</h4>
        <br><br>


        <div class="input-field">
          <?php echo $this->tag->textField("Matricula"); ?>
          <label for="Matricula">Matrícula</label>
          
          <?php 
            if(isset($form_error)){
          ?>
            <span class="helper-text"style="color:red;">
              <?php echo $form_error;?>
            </span>
          <?php 
            }
          ?>

        </div>

        <?php echo $this->tag->submitButton([
                      "type"=>"submit",
                      "value"=>"Entrar",
                      "class" => "btn waves-input-wrapper waves-light light-blue darken-4 "
                    ]); 
        ?>
        
      
      <?php echo $this->tag->endForm(); ?>
    </div>
    
  </div>



  <!--JavaScript at end of body for optimized loading-->
  <script type="text/javascript" src="js/materialize.min.js"></script>

<!--script type="text/javascript">
  document.oncontextmenu = function() {
    return false;
  }
</script-->

<script type="text/javascript">
  M.AutoInit();
</script>



