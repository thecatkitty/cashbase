<?php
  if(!Uprawnienia(2))
    Uciekaj('home');
  else {
    $l_from = isset($_GET['from']) ? $_GET['from'] : false;
    $l_to = isset($_GET['to']) ? $_GET['to'] : false;
    $l_ikt = isset($_GET['ikt']) ? $_GET['ikt'] : false;
    $l_type = isset($_GET['type']) ? $_GET['type'] : false;

    $p_date = '/^\d{4}-\d{2}-\d{2}$/';
    $p_ikt = '/^\d{2}(-\d{2})?$/';
    $p_type = '/^(in|out)$/';

    if(!$l_from || !preg_match($p_date, $l_from))
      $l_from = date('Y-m-d', strtotime('-1 month'));
    if($l_to) if(!preg_match($p_date, $l_to)) $l_to = false;
    if($l_ikt) if(!preg_match($p_ikt, $l_ikt)) $l_ikt = false;
    if($l_type) if(!preg_match($p_type, $l_type)) $l_type = false;

    $sql = "SELECT * FROM transakcja WHERE data>='$l_from'";
    if($l_to) $sql .= " AND data<='$l_to'";
    if($l_ikt) $sql .= " AND ikt LIKE '$l_ikt%'";
    if($l_type)
      if($l_type == 'in') $sql .= " AND kwota>0";
      else $sql .= " AND kwota<0";

    $res = $db->query($sql);

    ob_start();
?>
<h2>Lista transakcyj</h2>
<form method="get" class="form-inline">
 <input type="hidden" name="action" value="list" />
	
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
 
 <div class="form-group">
  <div class="input-group">
   <div class="input-group-addon">
    <label for="ikt">IKT</label>
   </div>
   <input name="ikt" list="ikts" autocomplete="off" style="width: 65px" class="form-control" />
  </div>
 </div>
 
 <div class="form-group">
  <div class="input-group">
   <div class="input-group-addon">
    <label for="type">Typ</label>
   </div>
   <select name="type" class="form-control">
     <option>Wszystkie</option>
     <option value="in">Przychody</option>
     <option value="out">Wydatki</option>
    </select>
  </div>
 </div>
 <input type="submit" class="btn btn-success" value="Filtruj" />
</form>

<table id="transaction-list">
 <thead><tr><th>ID</th><th>Data</th><th>Kwota</th><th>IKT</th><th>Opis</th><th>Dokument</th></tr></thead>
 <tbody>
<?php
  $i = 0;
  $suma = 0;
  while($row = $res->fetchArray(SQLITE3_ASSOC)) {
    $s = ($row['usun'] == 'TRUE' ? '<del>' : '');
    $se = ($s == '' ? '' : '</del>');
?>
  <tr class="<?=($row['kwota'] > 0 ? 'in' : 'out')?>">
   <td>
    <span><?=$s?><?=$row['id']?><?=$se?></span>
    <?php if($s == '') { ?><a href="#edit" class="emoji" title="Edytuj">&#x270F;</a><?php } ?>
    <a href="?action=delete&amp;id=<?=$row['id']?>" class="emoji" title="<?=($s == '' ? 'Usuń' : 'Przywróć')?>">&#x<?=($s == '' ? '274C' : '2714')?>;</a>
   </td>
   <td data-v="<?=$row['data']?>"><?=$s?><?=FormatujDate($row['data'])?><?=$se?></td>
   <td><?=$s?><?=number_format($row['kwota'], 2, ',', ' ')?><?=$se?></td>
   <td><?=$s?><abbr class="emoji"><?=$row['ikt']?></abbr><?=$se?></td>
   <td><?=$s?><?=$row['opis']?><?=$se?></td>
   <td>
<?php
  echo $s;
  switch($row['dokument']){
    case '': echo 'b/d'; break;
    case 0: echo 'n/d'; break;
    default:
      echo '<a href="docs/' . substr($row['data'], 2) . ' ' . $row['dokument'] . '.jpg">' . $row['dokument'] . '</a>';
  }
  echo $se;
?>
   </td>
  </tr>
<?php $i++; if($s == '') $suma += $row['kwota']; } ?>
 </tbody>
 <tfoot>
  <tr>
   <td>+</td>
   <td colspan="5"><a href="#insert_form" rel="leanModal">Dodaj nowy</a></td>
  </tr>
  <tr>
   <th colspan="2">Suma</th>
   <td><?=number_format($suma, 2, ',', ' ')?></td>
   <td></td><td></td><td></td>
  </tr>
 </tfoot>
</table>

<div id="insert_form" class="dialog">
 <form method="post" action="?action=add" class="form-horizontal">
  <input type="hidden" name="id" />
  
  <div class="form-group">
   <label for="date" class="col-sm-3 control-label">Data</label>
   <div class="col-sm-9">
    <input type="date" name="date" class="form-control" />
   </div>
  </div>

  <div class="form-group">
   <label for="amount" class="col-sm-3 control-label">Kwota</label>
   <div class="col-sm-9">
    <input name="amount" autocomplete="off" class="form-control" />
   </div>
  </div>
  
  <div class="form-group">
   <label for="ikt" class="col-sm-3 control-label">IKT</label>
   <div class="col-sm-9">
    <input name="ikt" list="ikts" autocomplete="off" class="form-control" />
   </div>
  </div>
  
  <div class="form-group">
   <label for="desc" class="col-sm-3 control-label">Opis</label>
   <div class="col-sm-9">
    <input name="desc" placeholder="Tu wpisz opis transakcji" autocomplete="off" class="form-control" />
   </div>
  </div>
  
  <div class="form-group">
   <label for="doc" class="col-sm-3 control-label">Dokument</label>
   <div class="col-sm-9">
    <input name="doc" placeholder="Tu wpisz numer dokumentu" autocomplete="off" class="form-control" />
   </div>
  </div>
  
  <div class="form-group">
   <div class="col-sm-12 text-right">
    <input type="submit" class="btn btn-success" value="Zapisz" />
	<input type="button" class="btn btn-danger" value="Anuluj" onclick="close_modal('#insert_form'); return false" />
   </div>
  </div>
 </form>
</div>

<datalist id="ikts">
<?php
 $res = $db->query("SELECT * FROM ikt");
 while($row = $res->fetchArray(SQLITE3_ASSOC))
   echo '<option value="'. $row['id'] . '">'
   . $row['id']
   . ' - '
   . $row['opis']
   . '</option>';
?>
</datalist>
<script src="skin/jquery.leanModal.min.js"></script>
<script>
 var ikt_icons = { '00':'2754', '01':'1F37D', '02':'1F4A6', '03':'1F3E0', '04':'1F455', '05':'26A1', '06':'1F4FA', '07':'1F4D6', '08':'1F4BE', '09':'2702', '10':'1F331', '11':'2708', '12':'1F3D7', '13':'271D', '14':'1F48A', '15':'1F4B6', '16':'1F37C', '17':'1F63A', '18':'1F6E1', '50':'1F4B8', '51':'1F4B3', '52':'1F454' };
 $(function() {
   $('a[href=#insert_form]').click(function() {
     $('#insert_form input[name=id]').removeAttr('value');
     $('#insert_form input[name=date]').val("<?=date('Y-m-d')?>");
     $('#insert_form input[name=amount]').val("0.00");
     $('#insert_form input[name=ikt]').val('00-00');
     $('#insert_form input[name=desc]').removeAttr('value');
     $('#insert_form input[name=doc]').removeAttr('value');
   });

   $('#transaction-list a[href*="#edit"]').each(function(i) {
     $(this).click(function() {
       var row = $(this).parent().parent()[0];
       $('#insert_form input[name=id]').val($(row.cells[0].children[0]).text());
       $('#insert_form input[name=date]').val($(row.cells[1]).attr('data-v'));
       $('#insert_form input[name=amount]').val($(row.cells[2]).text().replace(',', '.'));
       $('#insert_form input[name=ikt]').val($(row.cells[3].children[0]).attr('title').substr(0, 5));
       $('#insert_form input[name=desc]').val($(row.cells[4]).text());
       $('#insert_form input[name=doc]').val($(row.cells[5]).text());
     });
     $(this).attr('href', '#insert_form');
   });
   $('a[href=#insert_form]').leanModal({top: 200});

   $('#transaction-list abbr.emoji').each(function(i) {
     var ikt = $(this).text();
     $(this).attr('title', $('#ikts option[value*="' + ikt + '"]').text());
     $(this).html('&#x' + ikt_icons[ikt[0] + ikt[1]] + ';');
   });
 });
</script>
<?php
    $content = ob_get_clean();
  }
?>