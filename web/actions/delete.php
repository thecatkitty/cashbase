<?php
  if(!Uprawnienia(2))
    Uciekaj('home');
  else {
    if(!isset($_GET['id'])) if($_POST['id'] != '') Uciekaj('list');
    if(!preg_match('/^\d+$/', $_GET['id'])) Uciekaj('list');
    else $id = $_GET['id'];

    $res = $db->query('SELECT usun FROM transakcja WHERE id=' . $id);
    $res = $res->fetchArray(SQLITE3_ASSOC);

    if($res['usun'] == 'FALSE')
      $db->exec("UPDATE transakcja SET usun='TRUE' WHERE id=$id");
    else
      $db->exec("UPDATE transakcja SET usun='FALSE' WHERE id=$id");
    
    Uciekaj('list');
  }
?>