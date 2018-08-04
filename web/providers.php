<?php
  function login_user($login, $password) {
    global $db;
    $res = $db->query("SELECT login, passhash FROM User WHERE login='" . SQLite3::escapeString($login) . "'");
    if($row = $res->fetchArray(SQLITE3_ASSOC)) {
      if(password_verify($password, $row['passhash'])) {
        $_SESSION['user'] = $login;
      }
    }
  }

  function logoff_user() {
    unset($_SESSION['user']);
    session_destroy();
  }

  function get_ikts() {
    global $db;
    
    $res = $db->query("SELECT id, description FROM Category");
    $ret = array();
    while($row = $res->fetchArray(SQLITE3_ASSOC))
      $ret[] = $row;
    return $ret;
  }

  function get_operations($filters) {
    global $db;
    
    $sql = "SELECT id, date, category, description, value, strikeout FROM Operation WHERE";
    if(isset($filters['from']))
      $sql .= " date>='" . $filters['from'] . "'";
    if(isset($filters['to']))
      $sql .= " AND date<='" . $filters['to'] . "'";
    if(isset($filters['category']))
      $sql .= " AND category LIKE '" . $filters['category'] . "%'";
    if(isset($filters['type'])) {
      if($filters['type'] == 'in')
        $sql .= " AND value>0";
      else
        $sql .= " AND value<0";
    }

    $res = $db->query($sql);
    $ret = array();
    while($row = $res->fetchArray(SQLITE3_ASSOC)) {
      $row['description'] = json_decode($row['description'], true);
      $ret[] = $row;
    }
    return $ret;
  }

  function add_operation($operation) {
    global $db;
    
    $sql = "INSERT INTO Operation(date, category, description, value, bucket, owner, strikeout) VALUES("
         . "'" . SQLite3::escapeString($operation['date']) . "', "
         . "'" . SQLite3::escapeString($operation['category']) . "', "
         . "'" . SQLite3::escapeString(json_encode($operation['description'])) . "', "
         . "'" . SQLite3::escapeString($operation['value']) . "', "
         . "'" . SQLite3::escapeString($operation['bucket']) . "', "
         . "'" . SQLite3::escapeString($operation['owner']) . "', "
         . "'" . SQLite3::escapeString($operation['strikeout']) . "'"
         . ")";
    $db->exec($sql);
  }

  function update_operation($operation) {
    global $db;
    
    if(!isset($operation['id']))
      throw new Exception('ID not set.');

    $sql = "UPDATE Operation SET ";
    $first = true;
    foreach($operation as $key => $value) {
      if($key == 'id')
        continue;

      if(!$first)
        $sql .= ", ";

      if(is_array($value))
        $sql .= $key . "='" . SQLite3::escapeString(json_encode($value)) . "'";
      else
        $sql .= $key . "='" . SQLite3::escapeString($value) . "'";

      $first = false;
    }
    $sql .= " WHERE id='" . SQLite3::escapeString($operation['id']) . "'";
    $db->exec($sql);
  }

  function is_operation_struckout($id) {
    global $db;
    
    $res = $db->query("SELECT strikeout FROM Operation WHERE id='" . $id . "'");
    $res = $res->fetchArray(SQLITE3_ASSOC);
    return $res['strikeout'] == 1;
  }

  function get_total() {
    global $db;
    
    $res = $db->query("SELECT SUM(value) AS total FROM Operation WHERE strikeout=0");
    $res = $res->fetchArray(SQLITE3_ASSOC);
    return $res['total'];
  }

  function get_piechart_data($filters) {
    global $db;
    
    $comparer = ($filters['type'] == 'in' ? '>' : '<');
    $sql = "SELECT"
         . " SUBSTR(Operation.category, 1, 2) AS supercategory,"
         . " ABS(SUM(CASE WHEN Operation.strikeout<>1 THEN Operation.value ELSE 0 END)) AS value,"
         . " Category.description as description"
         . " FROM Operation INNER JOIN Category"
         . " ON supercategory=Category.id";
    $sql .= " WHERE value${comparer}0";
    if(isset($filters['from']))
      $sql .= " AND date>='" . $filters['from'] . "'";
    if(isset($filters['to']))
      $sql .= " AND date<='" . $filters['to'] . "'";
    $sql .= " GROUP BY supercategory";


    $res = $db->query($sql);
    $ret = array();

    while($row = $res->fetchArray(SQLITE3_ASSOC)) {
      $ret[] = array(
        'label' => $row['supercategory'] . ' - ' . $row['description'],
        'value' => $row['value']
      );
    }
    
    return $ret;
  }

  function get_barchart_data($filters) {
    global $db;
    
    if(isset($filters['to'])) $days = intval((
      date_diff(date_create($filters['from']), date_create($filters['to']))
    )->format('%a'));
    else $days = intval((
      date_diff(date_create($filters['from']), date_create('now'))
    )->format('%a'));

    if($days < 15) {
      $period = 'date';
      $xu = 'd';
    } else if($days < 62) {
      $period = "strftime('%Y W%W', date)";
      $xu = 'w';
    } else if($days < 186) {
      $period = "strftime('%Y-%m', date)";
      $xu = 'm';
    } else if($days < 731) {
      $period = "(strftime('%Y Q', date) || (strftime('%m', date)/4 + 1))";
      $xu = 'q';
    } else {
      $period = "strftime('%Y', date)";
      $xu = 'y';
    }

    $sql = "SELECT"
         . " " . $period ." AS period,"
         . " SUM(CASE WHEN (value>0 AND strikeout<>1) THEN value ELSE 0 END) AS 'in',"
    . " SUM(CASE WHEN (value<0 AND strikeout<>1) THEN value ELSE 0 END) AS 'out'"
    . " FROM Operation"
    . " WHERE date>='" . $filters['from'] . "'";
    if(isset($filters['to']))
      $sql .= " AND date<='" . $filters['to'] . "'";
    $sql .= " GROUP BY period";

    $res = $db->query($sql);

    $ret = array();
    while($row = $res->fetchArray(SQLITE3_ASSOC)) {
      switch($xu) {
        case 'd':
          $from = new DateTime($row['period']);
          $to = clone $from;
          $to->modify('+1 day');
          break;
        case 'w': 
          $from = explode(' W', $row['period']);
          $from = (new DateTime($from[0] . '-01-01'))->modify('+' . ($from[1]-1) . ' weeks');
          $to = clone $from;
          $to->modify('+1 week');
          break;
        case 'm': 
          $from = new DateTime($row['period'] . '-01');
          $to = clone $from;
          $to->modify('+1 month');
          break;
        case 'q': 
          $from = explode(' Q', $row['period']);
          $month = (intval($from[1]) - 1) * 4;
          if($month < 10) $month = '0' . $month;
          $from = new DateTime($from[0] . '-' . $month .'-01');
          $to = clone $from;
          $to->modify('+3 months');
          break;
        case 'y': 
          $from = new DateTime($row['period'] . '-01-01');
          $to = clone $from;
          $to->modify('+1 year');
          break;
      }
      $to->modify('-1 day');

      $ret[] = array(
        'period' => array(
          'name' => $row['period'],
          'from' => $from->format('Y-m-d'),
          'to' => $to->format('Y-m-d')
        ),
        'in' => $row['in'],
        'out' => $row['out']
      );
    }
    return $ret;
  }
?>