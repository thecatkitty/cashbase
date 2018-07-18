<?php
  if(!Uprawnienia(2))
    Uciekaj('home');
  else {
    if(!isset($_POST['date']))   if($_POST['date'] != '')   Uciekaj('list');
    if(!isset($_POST['amount'])) if($_POST['amount'] != '') Uciekaj('list');
    if(!isset($_POST['ikt']))    if($_POST['ikt'] != '')    Uciekaj('list');
    if(!isset($_POST['desc']))   if($_POST['desc'] != '')   Uciekaj('list');
    
    if(isset($_POST['id']) && $_POST['id'] != '') {
      if(!preg_match('/^\d+$/', $_POST['id'])) Uciekaj('list');
      else $id = $_POST['id'];
      $res = $db->query('SELECT id AS nxt FROM transakcja WHERE id=' . $id);
      $res = $res->fetchArray(SQLITE3_ASSOC);
    } else {
      $res = $db->query('SELECT (MAX(id) + 1) AS nxt FROM transakcja');
      $res = $res->fetchArray(SQLITE3_ASSOC);
    }

    $id = $res['nxt'];
    $date = str_replace("'", '&#39;', $_POST['date']);
    $amount = str_replace("'", '&#39;', $_POST['amount']);
    $amount = str_replace(",", '.', $amount);
    $ikt = str_replace("'", '&#39;', $_POST['ikt']);
    $desc = str_replace("'", '&#39;', $_POST['desc']);
    $doc = str_replace("'", '&#39;', $_POST['doc']);

    if(isset($_POST['id']) && $_POST['id'] != '')
      $db->exec("UPDATE transakcja SET data='$date', kwota=$amount, ikt='$ikt', opis='$desc', dokument='$doc' WHERE id=$id");
    else
      $db->exec("INSERT INTO transakcja VALUES($id, '$date', $amount, '$ikt', '$desc', '$doc', 'FALSE')");

    Uciekaj('list');
  }
?>