<?
  if(isset($_POST) && isset($_POST['action']))
  {
    if ($_POST['action'] == 'get' && isset($_POST['name']))
    {
      $file_data = fopen($_POST['name'], 'r');

      $material_file = str_replace('obj', 'mtl', $_POST['name']);

      if (file_exists($material_file))
      {
        $materials = read_materials($material_file);
      }

      $vertices = array();
      $indexes = array();
      $normals = array();
      $textures = array();

      $vertices_12 = array();
      $normals_12 = array();

      $vertex_indexes = array();
      $normal_indexes = array();
      $textures_indexes = array();

      $materials_intervals = array();

      $face_count = 0;

      global $vertex_normals;
      $vertex_normals = array();

      $j = 0;
      while($line = fgets($file_data))
      {
        if ($line[0] == 'v' && $line[1] == ' ')
        {
          $line_parts = explode(" ", $line);

          $vertices[] = array((float)$line_parts[1], (float)$line_parts[2], (float)$line_parts[3]);

        }

        if ($line[0] == 'v' && $line[1] == 'n')
        {
          $line_parts = explode(" ", $line);
          $normals[] = array((float)$line_parts[1], (float)$line_parts[2], (float)$line_parts[3]);
        }

        if ($line[0] == 'v' && $line[1] == 't')
        {
          $line_parts = explode(" ", $line);
          $textures[] = array((float)$line_parts[1], (float)$line_parts[2], (float)$line_parts[3]);
        }

        if ($line[0] == 'f')
        {
          $line_parts = explode(" ", $line);

          $face_count++;

          for ($i = 1; $i < count($line_parts); $i++)
          {

            $vertex_parts = explode("/", $line_parts[$i]);

            $vertex_indexes[] = $vertex_parts[0] - 1;
            $textures_indexes[] = $vertex_parts[1] - 1;
            $normal_indexes[] = $vertex_parts[2] - 1;;

            $j++;
          }
        }

        if (strpos($line, 'usemtl') !== false)
        {
          $line_parts = explode(" ", $line);
          $materials_intervals[] = array('position' => $face_count, 'name' => trim($line_parts[1]));
        }

      }

      fclose($file_data);

      $new_vertices = array();
      $new_normals = array();

      header('Content-Type: application/json');

      if ($_POST['part'] == 'vertices_info')
      {
        echo json_encode(array('status' => 'success' , 'vertices' => $vertices, 'normals' => $normals, 'textures' => $textures));
        exit;
      }

      if ($_POST['part'] == 'model_info')
      {
        echo json_encode(array('status' => 'success' , 'vertixes_indexes' => $vertex_indexes, 'normals_indexes' => $normal_indexes, 'textures_indexes' => $textures_indexes, 'materials' => $materials, 'material_intervals' => $materials_intervals));
        exit;
      }

      echo json_encode(array('status' => 'success' , 'vertices' => $vertices, 'normals' => $normals, 'vertixes_indexes' => $vertex_indexes, 'normals_indexes' => $normal_indexes, 'textures_indexes' => $textures_indexes, 'materials' => $materials, 'material_intervals' => $materials_intervals, 'textures' => $textures));
    }
  }

  function insert_normal_vertex($vertex, $normal)
  {
    $i = 0;
    global $vertex_normals;
    foreach($vertex_normals as $vertex_normal)
    {
      if ($vertex_normal[0][0] == $vertex[0] && $vertex_normal[0][1] == $vertex[2] && $vertex_normal[0][2] == $vertex[2] && $vertex_normal[1][0] == $normal[0] && $vertex_normal[1][0] == $normal[2] && $vertex_normal[1][2] == $normal[2]) return $i;
      $i++;
    }

    $vertex_normals[] = array($vertex, $normal);

    return $i;
  }

  function read_materials($file_name)
  {
    $file_data = file_get_contents($file_name);

    $lines = explode("\n", $file_data);

    $material = array();
    $material_name = '';

    $materials = array();
    foreach($lines as $line)
    {
        $values = explode(' ', $line);

        if (strpos($line, 'newmtl') !== false)
        {
          if ($material_name) $materials[$material_name] = $material;

          $material = array();
          $material_name = trim($values[1]);
        }
        elseif($line[0] == 'N' && $line[1] == 's')
        {
          $material['Ns'] = $values[1];
        }
        elseif($line[0] == 'K' && $line[1] == 'a')
        {
          $material['Ka'] = array($values[1], $values[2], $values[3]);
        }
        elseif($line[0] == 'K' && $line[1] == 'd')
        {
          $material['Kd'] = array($values[1], $values[2], $values[3]);
        }
        elseif($line[0] == 'K' && $line[1] == 's')
        {
          $material['Ks'] = array($values[1], $values[2], $values[3]);
        }
        elseif($line[0] == 'K' && $line[1] == 'e')
        {
          $material['Ke'] = array($values[1], $values[2], $values[3]);
        }
        elseif($line[0] == 'N' && $line[1] == 'i')
        {
          $material['Ni'] = $values[1];
        }
        elseif($line[0] == 'd')
        {
          $material['d'] = $values[1];
        }
        elseif(strpos($line, 'illum') === 0)
        {
          $material['illum'] = $values[1];
        }
        elseif (strpos($line, 'map_Kd') === 0)
        {
          $material['map_Kd'] = $values[1];
        }
    }

    if ($material_name) $materials[$material_name] = $material;

    if (!$materials) return false;

    return $materials;
  }
?>
