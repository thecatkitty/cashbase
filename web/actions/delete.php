<?php
  if(!Zalogowany())
    Uciekaj('home');
  else {
    if(!isset($_GET['id'])) if($_POST['id'] != '') Uciekaj('list');
    if(!preg_match('/^\d+$/', $_GET['id'])) Uciekaj('list');
    else $id = $_GET['id'];

    update_operation(array(
      'id' => $id,
      'strikeout' => is_operation_struckout($id) ? 0 : 1
    ));
    
    Uciekaj('list');
  }
?>