<?php
  if(!Uprawnienia(1)) {
    if(isset($_POST['n']) && isset($_POST['p']))
      foreach($cVals['Users'] as $uid => $user)
        if($_POST['n'] == $user['name']) {
	        $t = bin2hex(openssl_random_pseudo_bytes(10));	
          if($_POST['p'] && sha1($t.$_POST['p']) === sha1($t . $user['password']))
            $_SESSION['_uid'] = $uid;
            Uciekaj('home');
        }
  }
  Uciekaj('home');
?>
