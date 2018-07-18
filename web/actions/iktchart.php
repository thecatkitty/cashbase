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

    $sql = "SELECT"
    . " SUBSTR(transakcja.ikt, 1, 2) AS `ik`,"
    . " ABS(SUM(CASE WHEN (transakcja.kwota<0 AND transakcja.usun<>'TRUE') THEN transakcja.kwota ELSE 0 END)) AS `out`,"
    . " ikt.opis AS `desc`"
    . " FROM transakcja INNER JOIN ikt"
    . " ON ik=ikt.id"
    . " WHERE transakcja.data>='$l_from'";
    if($l_to) $sql .= " AND transakcja.data<='$l_to'";
    $sql .= " GROUP BY ik";

    $data = array(
      'labels' => array(),
      'datasets' => array(
        array(
          'data' => array(),
          'backgroundColor' => array()
        )
      )
    );

    for($res = $db->query($sql), $i = 0;
        $row = $res->fetchArray(SQLITE3_ASSOC);
        $i++) {
      if($row['ik'][0] == '5') continue;
      $data['labels'][$i] = $row['ik'] . ' - ' . $row['desc'];
      $data['datasets'][0]['data'][$i] = $row['out'];
    }

    $iks = count($data['labels']);
    $hue_step = 360 / $iks;
    for($i = 0; $i < $iks; $i++)
      $data['datasets'][0]['backgroundColor'][$i] = 'hsl(' . round($i * $hue_step) . ', 60%, 70%)';

    $data = json_encode($data);

    ob_start();
?>
<h2>Wykres kategoryj</h2>
<form method="get" class="form-inline">
 <input type="hidden" name="action" value="iktchart" />
 
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
   type: 'pie',
   data: <?=$data?>,
   options: {
     legend: {
       display: false
     }
   }
 });

 ctx.onclick = function(evt) {
   var act_pts = myChart.getElementsAtEvent(evt);
   var x = act_pts[0]._model.label;

   $('input[name=action]').val('list');
   $('form').append('<input type="hidden" name="ikt" />');
   $('input[name=ikt]').val(x[0] + '' + x[1]);
   $('input[value=Filtruj]').click();
 }
</script>
<?php
    $content = ob_get_clean();
  }
?>