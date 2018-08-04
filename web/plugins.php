<?php
  function OdRoku($rok) {
	  if($rok == date('Y')) return $rok;
	  return $rok . '-' . date('Y');
  }

  function FormatujDate($data) {
    $data = explode('-', $data);
    if(count($data) != 3)
      return '<strong>Niepoprawna data!</strong>';
    
    $r = $data[0];
    $d = intval($data[2]);

    switch($data[1]) {
      case 1: $m = 'sty'; break;
      case 2: $m = 'lut'; break;
      case 3: $m = 'mar'; break;
      case 4: $m = 'kwi'; break;
      case 5: $m = 'maj'; break;
      case 6: $m = 'cze'; break;
      case 7: $m = 'lip'; break;
      case 8: $m = 'sie'; break;
      case 9: $m = 'wrz'; break;
      case 10: $m = 'paź'; break;
      case 11: $m = 'lis'; break;
      case 12: $m = 'gru'; break;
      default: return '<strong>Niepoprawna data!</strong>';
    }

    return $d . ' ' . $m . ' ' . $r;
  }

  function Uciekaj($akcja) {
    header('Location: ?action=' . $akcja . '');
    exit();
  }

  function Zalogowany() {
    return isset($_SESSION['user']);
  }
?>