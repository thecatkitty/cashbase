<?php
  if(!Zalogowany(1))
    Uciekaj('home');
  else {
    $p_date = '/^\d{4}-\d{2}-\d{2}$/';

    $filters = array();

    if(isset($_GET['from']) && preg_match($p_date, $_GET['from']))
      $filters['from'] = $_GET['from'];
    else
      $filters['from'] = date('Y-m-d', strtotime('-1 month'));
    if(isset($_GET['to']) && preg_match($p_date, $_GET['to']))
      $filters['to'] = $_GET['to'];
    else
      $filters['to'] = date('Y-m-d');

    $chart_data = get_barchart_data($filters);

    ob_start();
?>
<h2>Wykres wydatków</h2>
<form method="get" class="form-inline">
 <input type="hidden" name="action" value="time_chart" />
 
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
   <input type="date" name="to" value="<?=$filters['to']?>" class="form-control" />
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
   datasets: [
     {
       label: 'Przychody',
       backgroundColor: '#8f8',
       borderColor: 'green',
       borderWidth: 3,
       data: []
     },
     {
       label: 'Wydatki',
       backgroundColor: '#f88',
       borderColor: 'red',
       borderWidth: 3,
       data: []
     },
     {
       label: 'Suma',
       backgroundColor: '#aaa',
       borderColor: '#555',
       borderWidth: 3,
       data: []
     }
   ]
 };
 var periods = [];

 rawData.forEach(function(element) {
   chartData.labels.push(element.period.name);
   chartData.datasets[0].data.push(element.in);
   chartData.datasets[1].data.push(element.out);
   chartData.datasets[2].data.push(element.in + element.out);
   periods[element.period.name] = {
     from: element.period.from,
     to: element.period.to
   };
 });

 var myChart = new Chart(ctx, {
   type: 'bar',
   data: chartData,
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

 ctx.onclick = function(evt) {
   var actPts = myChart.getElementsAtEvent(evt);
   var x = actPts[0]._model.label;

   if(periods[x].from == periods[x].to)
     $('input[name=action]').val('list');
   $('input[name=from]').val(periods[x].from);
   $('input[name=to]').val(periods[x].to);
   $('input[value=Filtruj]').click();
 }
</script>
<?php
    $content = ob_get_clean();
  }
?>