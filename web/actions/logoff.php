<?php
  if(Uprawnienia(1))
    $_SESSION['_uid'] = 0;
  Uciekaj('home');
?>