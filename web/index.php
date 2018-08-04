<?php
  $t_start = microtime(true);
  
  // Wczytaj konfigurację.
  $cVals = json_decode(file_get_contents("config.json"), true);
  date_default_timezone_set($cVals['TimeZone']);
  session_start();
  
  include_once('plugins.php');

  // Połącz z bazą
  $db = new SQLite3('../data/cash.sl3');

  require_once('providers.php');

  // Wykonaj akcję
  if(!isset($_GET['action']))
    $_GET['action'] = 'home';
  $_GET['action'] = basename($_GET['action']);
  $cVals['ActionFile'] = 'actions/' . $_GET['action'] . '.php';
  if(file_exists($cVals['ActionFile']))
    include_once($cVals['ActionFile']);
  else {
    include_once('actions/home.php');
    $error = '<p>Niepoprawna akcja!</p>';
  }
  $db->close();
?>
<!DOCTYPE html>
<html lang="pl-PL">
 <head>
  <meta charset="utf-8"/>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Celones CashBase</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous" />
  <!--[if lt IE 9]>
   <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
   <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->
  <link rel="stylesheet" type="text/css" href="skin/screen.css"/>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8/jquery.min.js"></script>
 </head>
 <body>
  <nav class="navbar navbar-inverted navbar-fixed-top">
   <div class="container">
    <div class="navbar-header">
     <a class="navbar-brand emoji" href="?action=home">&#x1F4B8;</a>
	   <span class="navbar-text visible-xs">Celones CashBase</span>
	   <span class="navbar-text hidden-xs">System zarządzania przychodami i wydatkami Celones CashBase</span>
    </div>
   </div>
  </nav>
  <header class="container-fluid">
   <i></i>
  </header>
  <section class="container text-center">
   <?php
     if(isset($error)) echo '<p class="error">' . $error . '</p>';
     echo $content;
   ?>
  </section>
<?php if(Zalogowany()) { ?>
  <aside class="container text-center">
   <nav>
    <h2>Menu</h2>
    <ul>
     <li><a href="?action=home"><span class="emoji">&#x1F3E0;</span><span>Strona główna</span></a></li>
     <li><a href="?action=list"><span class="emoji">&#x1F4DC;</span><span>Lista transakcyj</span></a></li>
     <li><a href="?action=time_chart"><span class="emoji">&#x1F4C8;</span><span>Wykres wydatków</span></a></li>
     <li><a href="?action=spending_chart"><span class="emoji">&#x1F4CA;</span><span>Wykres kategoryj</span></a></li>
     <li><a href="?action=income_chart"><span class="emoji">&#x1F4B8;</span><span>Wykres źródeł przychodu</span></a></li>
     <li><a href="?action=logoff"><span class="emoji">&#x1F511;</span><span>Wyloguj</span></a></li>
    </ul>
   </nav>
  </aside>
  <script>
   $(function() {
     $('nav li a').each(function(i) {
       $(this).attr('title', $(this.children[1]).text());
     });
   });
  </script>
<?php } ?>
  <footer class="container-fluid">
   <p>© <?=OdRoku(2016)?> <a href="https://github.com/thecatkitty/cashbase">Mateusz Karcz</a>. Udostępniane na zasadach Licencji MIT.</p>
  </footer>
 </body>
</html>
<?php
  $t_stop = microtime(true);
  echo '<!-- Wykonano w ' . round($t_stop - $t_start, 5) . ' milisekund. -->';
?>