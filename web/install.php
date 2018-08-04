<?php
  // Read timezone configuration
  $cVals = json_decode(file_get_contents("config.json"), true);
  date_default_timezone_set($cVals['TimeZone']);
  
  include_once('plugins.php');

  $show_form = true;

  // Connect to the database
  try {
    $db = new SQLite3('../data/cash.sl3');

    // Has been already configured?
    if(!isset($_SERVER['QUERY_STRING']))
      $_SERVER['QUERY_STRING'] = '';
    if($db->query("SELECT id FROM Bucket")) {
      if($_SERVER['QUERY_STRING'] != 'thanks') {
        $show_form = false;
        throw new Exception('Celones CashBase has already been configured!');
      }
    }
  
    // Execute a proper action
    if($_SERVER['QUERY_STRING'] == 'thanks')
      $show_form = false;
    if($_SERVER['QUERY_STRING'] == 'submit') {
      if(!isset($_POST['login']) || $_POST['login'] == '')
        throw new Exception('The login cannot be empty!');
      if(strlen($_POST['login']) > 32)
        throw new Exception('The login must be at most 32 characters long!');
      if(!isset($_POST['password']) || $_POST['password'] == '')
        throw new Exception('The password cannot be empty!');
      if(!isset($_POST['repassword']) || $_POST['password'] != $_POST['repassword'])
        throw new Exception('The passwords must match!');
      if(!isset($_POST['name']) || $_POST['name'] == '')
        throw new Exception('A man needs a name!');
      if(strlen($_POST['login']) > 80)
        throw new Exception('The preferred name must be at most 80 characters long!');

      $database_sql = file_get_contents('../data/database.sql');
      if(!$database_sql)
        throw new Exception(error_get_last()['message']);
      if(!$db->exec($database_sql))
        throw new Exception($db->lastErrorMsg());

      $currencies_sql = file_get_contents('../data/currencies.sql');
      if(!$currencies_sql)
        throw new Exception(error_get_last()['message']);
      if(!$db->exec($currencies_sql))
        throw new Exception($db->lastErrorMsg());

      $languages_sql = file_get_contents('../data/languages.sql');
      if(!$languages_sql)
        throw new Exception(error_get_last()['message']);
      if(!$db->exec($languages_sql))
        throw new Exception($db->lastErrorMsg());

      if(!$db->exec(
        "INSERT INTO User(login, passhash, name, language, currency) VALUES("
        . "'" . SQLite3::escapeString($_POST['login']) . "', "
        . "'" . password_hash($_POST['password'], PASSWORD_DEFAULT) . "', "
        . "'" . SQLite3::escapeString($_POST['name']) . "', 'pl-PL', 'PLN'"
        . ")"))
        throw new Exception($db->lastErrorMsg());
      if(!$db->exec(
        "INSERT INTO Bucket(name, currency, owner) VALUES('Total balance', 'PLN', "
        . "'" . SQLite3::escapeString($_POST['login']) . "'"
        . ")"))
        throw new Exception($db->lastErrorMsg());

      $sql_dir = opendir('../data');
      if(!$sql_dir)
        throw new Exception(error_get_last()['message']);
      while($file = readdir($sql_dir)) {
        if(preg_match('/^categories\..*\.sql$/', $file)) {
          $sql = file_get_contents('../data/' . $file);
          if(!$sql)
            throw new Exception(error_get_last()['message']);
          if(!$db->exec($sql))
            throw new Exception($db->lastErrorMsg());
        }
      }
      closedir($sql_dir);
      
      header('Location: ?thanks');
      exit();
    }
  
    $db->close();
  } catch(Exception $e) {
    $error = '<b>Something happened. :(</b><br/>' . $e->getMessage();
    $show_form = true;
  }
  
?>

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
	   <span class="navbar-text hidden-xs">Celones CashBase income and spending management system</span>
    </div>
   </div>
  </nav>
  <header class="container-fluid">
   <i></i>
  </header>
  <section class="container text-center">
   <h2>Celones CashBase Installer</h2>
   <?php if(isset($error)) echo '<p class="error">' . $error . '</p>'; ?>

<?php if($show_form) { ?>
   <form name="login" method="post" action="?submit" class="form-horizontal">
    <div class="form-group">
      <label for="n" class="col-sm-4 control-label">Set login:</label>
      <div class="col-sm-6">
      <input class="form-control" name="login" placeholder="ifiorair">
      </div>
    </div>
    <div class="form-group">
      <label for="password" class="col-sm-4 control-label">Set password:</label>
      <div class="col-sm-6">
      <input type="password" class="form-control" name="password" placeholder="Password">
      </div>
    </div>
    <div class="form-group">
      <label for="repassword" class="col-sm-4 control-label">Retype password:</label>
      <div class="col-sm-6">
      <input type="password" class="form-control" name="repassword" placeholder="Retype password">
      </div>
    </div>
    <div class="form-group">
      <label for="name" class="col-sm-4 control-label">Your preferred name:</label>
      <div class="col-sm-6">
      <input class="form-control" name="name" placeholder="Iovain Fiorair">
      </div>
    </div>
    <div class="form-group">
      <div class="col-sm-offset-4 col-sm-6 text-right">
      <input type="submit" name="submit" class="btn btn-success" value="Install" />
      </div>
    </div>
   </form>
<?php } else if($_SERVER['QUERY_STRING'] == 'thanks') { ?>
    <div class="row">
     <h3>Congratulations! :)</h3>
     <p>Remember to delete the <code>install.php</code> file.</p>
    </div>
<?php } ?>
  </section>
  <footer class="container-fluid">
   <p>© <?=OdRoku(2016)?> <a href="https://github.com/thecatkitty/cashbase">Mateusz Karcz</a>. Shared under the MIT License.</p>
  </footer>
 </body>
</html>
