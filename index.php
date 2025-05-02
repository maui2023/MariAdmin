<?php
if (isset($_GET['phpinfo'])) {
  echo "<!DOCTYPE html><html><head><title>PHP Info</title></head><body style='background:#000;color:#fff;padding:10px'>";
  ob_start(); phpinfo(); $info = ob_get_clean();
  // Strip out <style> and <head> tags
  $info = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $info);
  $info = preg_replace('/.*<body>(.*)<\/body>.*/is', '$1', $info);
  echo $info;
  echo "</body></html>";
  exit;
}

session_start();
if (!isset($_SESSION['LAST_ACTIVITY'])) {
  $_SESSION['LAST_ACTIVITY'] = time();
} elseif (time() - $_SESSION['LAST_ACTIVITY'] > 900) {
  session_unset();
  session_destroy();
  header("Location: ?logout=1");
  exit;
}
$_SESSION['LAST_ACTIVITY'] = time();

if (isset($_GET['logout'])) {
  session_destroy();
  header("Location: ?");
  exit;
}

if (isset($_POST['login'])) {
  $_SESSION['host'] = $_POST['host'];
  $_SESSION['user'] = $_POST['user'];
  $_SESSION['pwd'] = $_POST['pwd'];
}

if (!isset($_SESSION['user'])) {
  echo <<<HTML
  <!DOCTYPE html><html lang="en" data-bs-theme="dark">
  <head><meta charset="UTF-8"><title>Login - MariAdmin</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>body{background:#121212;color:#f1f1f1}.form-control{background:#1e1e1e;color:#f1f1f1;border-color:#444}</style>
  </head><body><div class="container mt-5"><div class="card mx-auto" style="max-width:400px;"><div class="card-body">
  <h3 class="card-title text-center mb-4">MariAdmin Login</h3>
  <form method="post">
    <div class="mb-3"><label class="form-label">Host</label><input name="host" class="form-control" value="localhost"></div>
    <div class="mb-3"><label class="form-label">Username</label><input name="user" class="form-control"></div>
    <div class="mb-3"><label class="form-label">Password</label><input type="password" name="pwd" class="form-control"></div>
    <div class="d-grid"><button name="login" class="btn btn-primary">Login</button></div>
  </form></div></div></div></body></html>
HTML;
  exit;
}

$conn = new mysqli($_SESSION['host'], $_SESSION['user'], $_SESSION['pwd']);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$sql = $_POST['sql'] ?? $_GET['sql'] ?? '';
preg_match('/USE `([^`]+)`/i', $sql, $match);
$currentDb = $match[1] ?? '';
$_SESSION['last_table'] = $_GET['table'] ?? ($_SESSION['last_table'] ?? 'tablename');

if (!isset($_SESSION['query_history'])) $_SESSION['query_history'] = [];
if (!empty($sql) && !in_array($sql, $_SESSION['query_history'])) {
  array_unshift($_SESSION['query_history'], $sql);
  $_SESSION['query_history'] = array_slice($_SESSION['query_history'], 0, 10);
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
  <meta charset="UTF-8"><title>MariAdmin</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: #121212; color: #f1f1f1; }
    .form-control, .btn { color: #f1f1f1; }
    .form-control { background-color: #1e1e1e; border-color: #444; }
    .table { color: #f1f1f1; }
    .table th, .table td { background-color: #1e1e1e; border-color: #333; }
    .card, .alert { background-color: #1e1e1e; color: #f1f1f1; border-color: #444; }
    .table-responsive { overflow-x: auto; }
  </style>
</head>
<body>
<header class="navbar navbar-dark bg-primary px-3"><span class="navbar-brand mb-0 h1">MariAdmin v0.1</span></header>

<div class="d-flex flex-column flex-md-row">
  <div class="bg-secondary p-3 d-flex flex-column" style="width: 250px; min-height: 100vh;">
    <button onclick="window.open('?phpinfo=1','PHPInfo','width=1024,height=768');" class="btn btn-info btn-sm mb-2">
      PHP Info
    </button>
    <hr>
    <a href="?sql=CREATE+DATABASE+databasename;" class="btn btn-success btn-sm mb-3">Home</a>
    <a href="?logout=1" class="btn btn-danger btn-sm mb-3">Logout</a>
    <h5 class="text-white">Databases</h5>
    <ul class="nav flex-column">
      <?php
      $dbList = $conn->query("SHOW DATABASES");
      while ($db = $dbList->fetch_row()) {
        $active = strpos($sql, $db[0]) !== false ? 'active bg-dark' : '';
        echo "<li class='nav-item'><a class='nav-link text-white $active' href='?sql=" . urlencode("USE `{$db[0]}`; SHOW TABLE STATUS") . "'>" . htmlspecialchars($db[0]) . "</a></li>";
      }
      ?>
    </ul>
  </div>
  <div class="flex-fill p-4">
    <div class="card mb-4">
      <div class="card-header bg-dark text-white"><h5 class="m-0">SQL Console</h5></div>
      <div class="card-body">
        <form method="post">
          <div class="mb-3">
            <label class="form-label">Query History</label>
            <select class="form-select" onchange="document.querySelector('textarea[name=\'sql\']').value = this.value">
              <option value="">-- Select previous query --</option>
              <?php foreach ($_SESSION['query_history'] as $q): ?>
                <option value="<?= htmlspecialchars($q) ?>"><?= htmlspecialchars($q) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">SQL Query</label>
            <textarea name="sql" class="form-control" rows="5"><?= htmlspecialchars($sql) ?></textarea>
            <div class="btn-group mt-2">
              <button type="button" class="btn btn-outline-success btn-sm" onclick="insertTemplate('insert')">Insert</button>
              <button type="button" class="btn btn-outline-warning btn-sm" onclick="insertTemplate('update')">Update</button>
              <button type="button" class="btn btn-outline-danger btn-sm" onclick="insertTemplate('delete')">Delete</button>
            </div>
          </div>
          <button type="submit" class="btn btn-primary">Run</button>
          <button type="reset" class="btn btn-secondary">Clear</button>
        </form>
      </div>
    </div>
    <?php
    if ($sql) {
      $start = microtime(true);
      try {
        if ($conn->multi_query($sql)) {
          $shown = false;
          do {
            if ($res = @$conn->store_result()) {
              $allRows = [];
              $fields = $res->fetch_fields();
              while ($row = $res->fetch_row()) {
                $allRows[] = $row;
              }
              $totalRows = count($allRows);
              $perPage = 15;
              $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
              $totalPages = ceil($totalRows / $perPage);
              $offset = ($page - 1) * $perPage;
              $pagedRows = array_slice($allRows, $offset, $perPage);
    
              if (!$shown) {
                echo "<div class='alert alert-success'>Query executed in " . round(microtime(true) - $start, 4) . " sec</div>";
                $shown = true;
              }
    
              echo "<div class='card mb-4'><div class='card-header bg-dark text-white'>Result</div>";
              echo "<div class='table-responsive'><table class='table table-bordered table-striped table-sm'><thead><tr>";
              foreach ($fields as $f) echo "<th>" . htmlspecialchars($f->name) . "</th>";
              echo "</tr></thead><tbody>";
    
              foreach ($pagedRows as $row) {
                echo "<tr>";
                foreach ($row as $i => $cell) {
                  $cellText = htmlspecialchars((string) $cell);
                  if (stripos($sql, 'SHOW TABLE STATUS') !== false && $i === 0) {
                    $encodedSql = urlencode("USE `{$currentDb}`; SELECT * FROM `{$cell}` WHERE 1");
                    echo "<td><a href='?sql={$encodedSql}&table=" . urlencode($cell) . "' class='text-info'>$cellText</a></td>";
                  } else {
                    echo "<td>$cellText</td>";
                  }
                }
                echo "</tr>";
              }
    
              echo "</tbody></table></div>";
    
              // Pagination controls
              if ($totalPages > 1) {
                echo "<nav><ul class='pagination pagination-sm justify-content-end p-2'>";
                for ($p = 1; $p <= $totalPages; $p++) {
                  $active = $p == $page ? 'active' : '';
                  echo "<li class='page-item $active'><a class='page-link' href='?sql=" . urlencode($sql) . "&page=$p'>$p</a></li>";
                }
                echo "</ul></nav>";
              }
    
              echo "</div>"; // close card
              $res->free();
            } elseif (!$shown && $conn->error) {
              echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($conn->error) . "</div>";
              $shown = true;
            }
          } while ($conn->more_results() && $conn->next_result());
        } else {
          echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($conn->error) . "</div>";
        }
      } catch (Exception $e) {
        echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
      }
    }
    ?>
  </div>
</div>

<footer class="text-center text-muted py-2">Powered by Sabily Enterprise</footer>

<script>
function insertTemplate(action) {
  const table = <?= json_encode($_SESSION['last_table']) ?>;
  const db = <?= json_encode($currentDb) ?>;
  let sql = '';
  switch (action) {
    case 'insert':
      sql = `INSERT INTO \`${table}\` (\`column1\`, \`column2\`) VALUES ('value1', 'value2');`;
      break;
    case 'update':
      sql = `UPDATE \`${table}\` SET \`column1\` = 'value' WHERE 1;`;
      break;
    case 'delete':
      sql = `DELETE FROM \`${table}\` WHERE 1;`;
      break;
  }
  const textarea = document.querySelector('textarea[name="sql"]');
  textarea.value = `USE \`${db}\`;\n${sql}`;
  textarea.focus();
}
</script>
</body>
</html>
