<?php
  if(!Zalogowany())
    Uciekaj('home');
  else {
    if(!isset($_POST['date']))   if($_POST['date'] != '')   Uciekaj('list');
    if(!isset($_POST['amount'])) if($_POST['amount'] != '') Uciekaj('list');
    if(!isset($_POST['ikt']))    if($_POST['ikt'] != '')    Uciekaj('list');
    if(!isset($_POST['desc']))   if($_POST['desc'] != '')   Uciekaj('list');
    
    if(isset($_POST['id']) && $_POST['id'] != '') {
      if(!preg_match('/^\d+$/', $_POST['id'])) Uciekaj('list');
      update_operation(array(
        'id' => $_POST['id'],
        'date' => $_POST['date'],
        'category' => $_POST['ikt'],
        'description' => array(
          'caption' => $_POST['desc']
        ),
        'value' => str_replace(",", '.', $_POST['amount']),
        'bucket' => 1,
        'owner' => $_SESSION['user'],
        'strikeout' => 0
      ));
    } else {
      add_operation(array(
        'date' => $_POST['date'],
        'category' => $_POST['ikt'],
        'description' => array(
          'caption' => $_POST['desc']
        ),
        'value' => str_replace(",", '.', $_POST['amount']),
        'bucket' => 1,
        'owner' => $_SESSION['user'],
        'strikeout' => 0
      ));
    }

    Uciekaj('list');
  }
?>