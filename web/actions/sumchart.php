<?php
  if(!Uprawnienia(1))
    Uciekaj('home');
  else {
    $l_from = isset($_GET['from']) ? $_GET['from'] : false;
    $l_to = isset($_GET['to']) ? $_GET['to'] : false;

    $p_date = '/^\d{4}-\d{2}-\d{2}$/';

    if(!$l_from || !preg_match($p_date, $l_from))
      $l_from = date('Y-m-d', strtotime('-1 month'));
    if($l_to) if(!preg_match($p_date, $l_to)) $l_to = false;

    if($l_to) $days = intval((
      date_diff(date_create($l_from), date_create($l_to))
    )->format('%a'));
    else $days = intval((
      date_diff(date_create($l_from), date_create('now'))
    )->format('%a'));

    if($days < 15) {
      $data_g = 'data';
      $xu = 'd';
    } else if($days < 62) {
      $data_g = "strftime('%Y W%W', data)";
      $xu = 'w';
    } else if($days < 186) {
      $data_g = "strftime('%Y-%m', data)";
      $xu = 'm';
    } else if($days < 731) {
      $data_g = "(strftime('%Y Q', data) || (strftime('%m', data)/4 + 1))";
      $xu = 'q';
    } else {
      $data_g = "strftime('%Y', data)";
      $xu = 'y';
    }

    $sql = "SELECT"
    . " " . $data_g ." AS data_g,"
    . " SUM(CASE WHEN (kwota>0 AND usun<>'TRUE') THEN kwota ELSE 0 END) AS `in`,"
    . " SUM(CASE WHEN (kwota<0 AND usun<>'TRUE') THEN kwota ELSE 0 END) AS `out`"
    . " FROM transakcja"
    . " WHERE data>='$l_from'";
    if($l_to) $sql .= " AND data<='$l_to'";
    $sql .= " GROUP BY data_g";

    $data = array(
      'labels' => array(),
      'datasets' => array(
        array(
          'label' => 'Przychody',
          'backgroundColor' => '#8f8',
          'borderColor' => 'green',
          'borderWidth' => 3,
          'data' => array()
        ),
        array(
          'label' => 'Wydatki',
          'backgroundColor' => '#f88',
          'borderColor' => 'red',
          'borderWidth' => 3,
          'data' => array()
        ),
        array(
          'label' => 'Suma',
          'backgroundColor' => '#aaa',
          'borderColor' => '#555',
          'borderWidth' => 3,
          'data' => array()
        ),
      )
    );

    for($res = $db->query($sql), $i = 0;
        $row = $res->fetchArray(SQLITE3_ASSOC);
        $i++) {
      $data['labels'][$i] = $row['data_g'];
      $data['datasets'][0]['data'][$i] = $row['in'];
      $data['datasets'][1]['data'][$i] = $row['out'];
      $data['datasets'][2]['data'][$i] = ($row['in'] + $row['out']);
      switch($xu) {
        case 'd':
          $from = new DateTime($row['data_g']);
          $to = clone $from;
          $to->modify('+1 day');
          break;
        case 'w': 
          $from = explode(' W', $row['data_g']);
          $from = (new DateTime($from[0] . '-01-01'))->modify('+' . ($from[1]-1) . ' weeks');
          $to = clone $from;
          $to->modify('+1 week');
          break;
        case 'm': 
          $from = new DateTime($row['data_g'] . '-01');
          $to = clone $from;
          $to->modify('+1 month');
          break;
        case 'q': 
          $from = explode(' Q', $row['data_g']);
          $month = (intval($from[1]) - 1) * 4;
          if($month < 10) $month = '0' . $month;
          $from = new DateTime($from[0] . '-' . $month .'-01');
          $to = clone $from;
          $to->modify('+3 months');
          break;
        case 'y': 
          $from = new DateTime($row['data_g'] . '-01-01');
          $to = clone $from;
          $to->modify('+1 year');
          break;
      }
      $to->modify('-1 day');
      $xs[$row['data_g']][0] = $from->format('Y-m-d');
      $xs[$row['data_g']][1] = $to->format('Y-m-d');
    }
    $data = json_encode($data);
    $xs = json_encode($xs);

    ob_start();
?>
<h2>Wykres wydatków</h2>
<form method="get" class="form-inline">
 <input type="hidden" name="action" value="sumchart" />
 
 <div class="form-group">
  <div class="input-group">
   <div class="input-group-addon">
    <label for="from">Od</label>
   </div>
   <input type="date" name="from" value="<?=$l_from?>" class="form-control" />
  </div>
 </div>
 
 <div class="form-group">
  <div class="input-group">
   <div class="input-group-addon">
    <label for="to">Do</label>
   </div>
   <input type="date" name="to" value="<?=date('Y-m-d')?>" class="form-control" />
  </div>
 </div>
 
 <input type="submit" class="btn btn-success" value="Filtruj" />
</form>

<canvas id="chart" width="500" height="400"></canvas>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.3.0/Chart.min.js"></script>
<script>
 var ctx = document.getElementById('chart');
 var myChart = new Chart(ctx, {
   type: 'bar',
   data: <?=$data?>,
   options: {
     scales: {
       yAxes: [{
         ticks: {
           beginAtZero: true
         }
       }]
     }
   }
 });

 var xs = <?=$xs?>;
 ctx.onclick = function(evt) {
   var act_pts = myChart.getElementsAtEvent(evt);
   var x = act_pts[0]._model.label;

   if(xs[x][0] == xs[x][1])
     $('input[name=action]').val('list');
   $('input[name=from]').val(xs[x][0]);
   $('input[name=to]').val(xs[x][1]);
   $('input[value=Filtruj]').click();
 }
</script>
<?php
    $content = ob_get_clean();
  }
?>