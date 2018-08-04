<?php
  if(!Zalogowany())
    Uciekaj('home');
  else {
    $p_date = '/^\d{4}-\d{2}-\d{2}$/';

    $filters = array('type' => 'out');

    if(isset($_GET['from']) && preg_match($p_date, $_GET['from']))
      $filters['from'] = $_GET['from'];
    else
      $filters['from'] = date('Y-m-d', strtotime('-1 month'));
    if(isset($_GET['to']) && preg_match($p_date, $_GET['to']))
      $filters['to'] = $_GET['to'];

    $chart_data = get_piechart_data($filters);

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
   <input type="date" name="from" value="<?=$filters['from']?>" class="form-control" />
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
 var rawData = <?=json_encode($chart_data)?>;
 var chartData = {
   labels: [],
   datasets: [{
     data: [],
     backgroundColor: []
   }]
 };

 var hueStep = 360 / rawData.length;
 rawData.forEach(function(element, index) {
   chartData.labels.push(element.label);
   chartData.datasets[0].data.push(element.value);
   chartData.datasets[0].backgroundColor.push('hsl(' + Math.round(index * hueStep) + ', 60%,  70%)');
 });

 var myChart = new Chart(ctx, {
   type: 'pie',
   data: chartData,
   options: {
     legend: {
       display: false
     }
   }
 });

 ctx.onclick = function(evt) {
   var actPts = myChart.getElementsAtEvent(evt);
   var x = actPts[0]._model.label;

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