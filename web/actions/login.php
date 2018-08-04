<?php
  if(!Zalogowany())
    if(isset($_POST['n']) && isset($_POST['p']))
      login_user($_POST['n'], $_POST['p']);
  Uciekaj('home');
?>
