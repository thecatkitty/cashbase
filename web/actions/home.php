<?php
  ob_start();
  if(!Uprawnienia(1)) {
?>
<h2>Wpisz hasło</h2>

<form name="login" method="post" action="?action=login" class="form-horizontal">
 <div class="form-group">
  <label for="n" class="col-sm-4 control-label">Nazwa użytkownika:</label>
  <div class="col-sm-6">
   <input type="n" class="form-control" name="n" placeholder="Login">
  </div>
 </div>
 <div class="form-group">
  <label for="p" class="col-sm-4 control-label">Hasło:</label>
  <div class="col-sm-6">
   <input type="password" class="form-control" name="p" placeholder="Hasło">
  </div>
 </div>
 <div class="form-group">
  <div class="col-sm-offset-4 col-sm-6 text-right">
   <input type="submit" name="submit" class="btn btn-success" value="Zaloguj" />
  </div>
 </div>
</form>
<?php
  } else { 
    $res = $db->query("SELECT SUM(kwota) AS saldo FROM transakcja WHERE usun=='FALSE'");
    $res = $res->fetchArray(SQLITE3_ASSOC);
?>
<div class="panel panel-primary">
 <div class="panel-heading">
  <h2 class="panel-title">Stan środków</h2>
 </div>
 <div class="panel-body" id="saldo">
  <?=number_format($res['saldo'], 2, ',', ' ')?> zł
 </div>
</div>
<?php
  }
  $content = ob_get_clean();
?>