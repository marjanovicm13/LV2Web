<?php
	$dbname = 'diplomskiradovi';
	$dir = "D:/Fakultet/NPW-LV2/$dbname";
	if (!is_dir($dir)) {
		if (!@mkdir($dir)) {
			die("<p>This directory can't be created.</p>");
		}
	}
	$time = time();
	$dbc = @mysqli_connect('localhost', 'root', '', $dbname, 3307 ) OR die("<p>Can't connect to this database</p>");
	$r = mysqli_query($dbc, 'SHOW TABLES');
	if (mysqli_num_rows($r) > 0) {
		echo "<p>Backup of '$dbname'</p>";
		while (list($table) = mysqli_fetch_array($r, MYSQLI_NUM)) {
			$query = "SELECT * FROM $table";
			$r2 = mysqli_query($dbc, $query);
			$columns = $r2->fetch_fields();
			if (mysqli_num_rows($r2) > 0) {
				if ($fp = fopen ("$dir/{$table}_{$time}.txt", 'w9')) {
					while ($row = mysqli_fetch_array($r2, MYSQLI_NUM)) {
						fwrite($fp, "INSERT INTO $dbname (");
						foreach($columns as $column) {
							fwrite($fp, "$column->name");
							if ($column != end($columns)) {
								fwrite($fp, ", ");
							}
						}
						fwrite($fp, ")\r\nVALUES (");
						foreach ($row as $value) {
							$value = addslashes($value);
							fwrite ($fp, "'$value'");
							if ($value != end($row)) {
								fwrite($fp, ", ");
							} else {
								fwrite($fp, ")\";");
							}
						}
						fwrite ($fp, "\r\n");
					}
					fclose($fp);
					echo "<p>Table $table saved.</p>";
					if ($fp2 = gzopen("$dir/{$table}_{$time}.sql.gz", 'w9')) {
						gzwrite($fp2, file_get_contents("$dir/{$table}_{$time}.txt"));
						gzclose($fp2);
					} else {
						echo "<p>File $dir/{$table}_{$time}.sql.gz can't be open</p>";
						break;						
					}
				} else {
					echo "<p>File $dir/{$table}_{$time}.txt can't be open</p>";
					break;
				}
			}
		}
	} else {
		echo "<p>Db doesn't have any tables.</p>";
	}
	
?>