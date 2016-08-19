<?php

include "util.php";
include "hegemon.php";
include "groups.php";


$file = "explore.conf";
$keys = array("" => 1);
$groups = "";
$colors = array(
    '#0000FF', '#558866',
    '#00EEEE', '#EE1000', '#000000',
    '#990000', '#00FF00', '#FF00FF', '#aaaa00',
    '#8A2BE2', '#A52A2A',
    '#DEB887', '#5F9EA0', '#7FFF00', '#D2691E', '#FF7F50',
    '#6495ED', '#FFF8DC', '#DC143C', '#00FFFF', '#00008B',
    '#008B8B', '#B8860B', '#A9A9A9', '#006400', '#BDB76B',
    '#8B008B', '#556B2F', '#FF8C00', '#9932CC', '#8B0000',
    '#E9967A', '#8FBC8F', '#483D8B', '#2F4F4F', '#00CED1');

if (array_key_exists("key", $_GET)) {
  $keys = array();
  foreach (split(":", trim($_GET["key"])) as $v) { $keys[$v] = 1; }
}
if (array_key_exists("groups", $_GET)) {
  $groups = urldecode($_GET['groups']);
}
if (array_key_exists("key", $_POST)) {
  $keys = array();
  foreach (split(":", trim($_POST["key"])) as $v) { $keys[$v] = 1; }
}
if (array_key_exists("groups", $_POST)) {
  $groups = urldecode($_POST['groups']);
}

if (array_key_exists("go", $_GET)) {
  if (strcmp($_GET["go"], "getids") == 0) {
    printAllIDs($file, $_GET['A'], $_GET['B'], $_GET['id']);
  }
  if (strcmp($_GET["go"], "getplots") == 0) {
    getPlots($file, $_GET['A'], $_GET['B'], $_GET['id']);
  }
  if (strcmp($_GET["go"], "plotids") == 0) {
    plotids($file, $_GET['file'], $_GET['id'], 
        $_GET['x'], $_GET['y'],$_GET['xn'],$_GET['yn'],
        $groups);
  }
  if (strcmp($_GET["go"], "plot") == 0) {
    plot($file, $_GET['file'], $_GET['id'], 
        $_GET['x'], $_GET['y'],$_GET['xn'],$_GET['yn'],
        $groups);
  }
  if (strcmp($_GET["go"], "getstats") == 0) {
    getStats($file, $_GET['A'], $_GET['B'], $_GET['id'], $_GET['sthr'],
        $_GET['pthr']);
  }
  if (strcmp($_GET["go"], "topgenes") == 0) {
    topGenes($file, $_GET['id'], $_GET['num']);
  }
  if (strcmp($_GET["go"], "midreg") == 0) {
    midreg($file, $_GET['A'], $_GET['B'], $_GET['id'], $_GET['sthr'],
        $_GET['pthr']);
  }
  if (strcmp($_GET["go"], "getcorr") == 0) {
    printCorrelation($file, $_GET['A'], $_GET['B'], $_GET['id'], $_GET['sthr'],
        $_GET['pthr']);
  }
  callGroupsCommands($file, $groups);
}
elseif (array_key_exists("go", $_POST)) {
  if (strcmp($_POST["go"], "plot") == 0) {
    plotDataUri($file, urldecode($_POST['file']), $_POST['id'], 
        $_POST['x'], $_POST['y'],$_POST['xn'],$_POST['yn'],
        $groups);
  }
  callGroupsPostCommands($file, $groups);
}
else {
  printSummary($file);
}

function getHegemon($file, $id) {
  $db = new Database($file);
  $d = $db->getDataset($id);
  $h = new Hegemon($d);
  $h->init();
  return $h;
}

function getImgUrl($h, $id, $id1, $id2) {
  $exprFile = $h->getExprFile();
  $ptr1 = $h->getPtr($id1);
  $ptr2 = $h->getPtr($id2);
  $n1 = $h->getName($id1);
  $n2 = $h->getName($id2);
  $src = "explore.php?go=plot&file=$exprFile&id=$id&xn=$n1&yn=$n2" . 
    "&x=$ptr1&y=$ptr2";
  return $src;
}
  
function setupDisplay($h, $id, $sthr, $pthr, $bestid1, $bestid2, $head,
    $values, $idlist) {
  echo "
    <script type=\"text/javascript\">
    function updateRel(i) {
      $('#rel' + i).toggle();
      $('#res' + i).html('');
    }
  function updateResult(i, k) {
    var url = 'explore.php?go=getstats&id=$id&A=$bestid1%20$bestid2';
    url += '&B=' + k + '&sthr=$sthr&pthr=$pthr';
    $('#res' + i).load(url);
  }
  </script>
    ";
  echo "<table border=\"0\">\n";
  echo "<tr><td>".join("</td><td>", $head) . "</td></tr>\n";
  echo "<tr>";
  for ($i = 0; $i < count($values); $i++) {
    echo "<td> <a href=\"#\" onclick=\"updateRel($i);\">". 
      $values[$i] . "</a></td>\n";
  }
  echo "</tr>";
  echo "</table>\n";
  for ($i = 0; $i < count($idlist); $i++) {
    echo "<div id=\"rel$i\" style=\"display:none;\">\n";
    echo "<table border=\"0\"><tr>\n";
    $index = 0;
    foreach ($idlist[$i] as $k) {
      $n = $h->getName($k);
      echo "<td> <a href=\"#\" onclick=\"updateResult($i, '$k');\">". 
        "$k</a></td>\n";
      echo "<td> <a href=\"#\" onclick=\"updateResult($i, '$k');\">". 
        "$n</a></td>\n";
      if ($index >= 4) {
        echo "</tr><tr>\n";
        $index = 0;
      }
      $index++;
    }
    echo "</tr></table>\n";
    echo "<div id=\"res$i\">\n";
    echo "</div>\n";
    echo "</div>\n";
  }
}

function midreg($file, $str1, $str2, $id, $sthr, $pthr) {
  $h = getHegemon($file, $id);
  $h->initPlatform();
  if ($str1 == "" || $str2 == "") {
    $ids = $h->getIDs("$str1 $str2");
    $bestid = $h->getBestID(array_keys($ids));
    echo "<table border=\"0\">\n";
    foreach ($ids as $k => $v) {
      echo "<tr>\n";
      if (strcmp($bestid, $k) == 0) {
        echo "<td><b>$k</b></td><td><b>". $h->getName($k) .
          "</b></td><td><b>$v -- Best ID</b></td>\n";
      }
      else {
        echo "<td>$k</td><td>". $h->getName($k) ."</td><td>$v</td>\n";
      }
      echo "</tr>\n";
    }
    echo "</table>\n";
    $res = $h->getBooleanRelations($bestid, $sthr, $pthr);
    $arr = array_count_values($res);
    $head = ["norel", "lohi", "lolo", "hihi", "hilo", "eqv", "opo"];
    $values = [0, 0, 0, 0, 0, 0, 0];
    for ($i = 0; $i < 7; $i++) {
      if (array_key_exists($i, $arr)) {
        $values[$i] = $arr[$i];
      }
    }
    $idlist = [[], [], [], [], [], [], []];
    foreach ($res as $k => $v) {
      if ($v > 0) {
        array_push($idlist[$v], $k);
      }
    }
    setupDisplay($h, $id, $sthr, $pthr, $bestid,"", $head,
        $values, $idlist);
  }
  else {
    $ids = $h->getIDs($str1);
    $bestid1 = $h->getBestID(array_keys($ids));
    echo "<table border=\"0\">\n";
    foreach ($ids as $k => $v) {
      echo "<tr>\n";
      if (strcmp($bestid1, $k) == 0) {
        echo "<td><b>$k</b></td><td><b>". $h->getName($k) .
          "</b></td><td><b>$v -- Best ID for Gene A</b></td>\n";
      }
      else {
        echo "<td>$k</td><td>". $h->getName($k) ."</td><td>$v</td>\n";
      }
      echo "</tr>\n";
    }
    $ids = $h->getIDs($str2);
    $bestid2 = $h->getBestID(array_keys($ids));
    foreach ($ids as $k => $v) {
      echo "<tr>\n";
      if (strcmp($bestid2, $k) == 0) {
        echo "<td><b>$k</b></td><td><b>". $h->getName($k) .
          "</b></td><td><b>$v -- Best ID for Gene B</b></td>\n";
      }
      else {
        echo "<td>$k</td><td>". $h->getName($k) ."</td><td>$v</td>\n";
      }
      echo "</tr>\n";
    }
    echo "</table>\n";
    $res1 = $h->getBooleanRelations($bestid1, $sthr, $pthr);
    $res2 = $h->getBooleanRelations($bestid2, $sthr, $pthr);
    $head = ["A lolo B hilo", "A equ B hilo", "A hihi B hilo", "A hilo B hihi", "A hilo B eqv", "A hilo B lolo"];
    $values = [0, 0, 0, 0, 0, 0];
    foreach ($res1 as $k => $v) {
      if ($v == 2 && ($res2[$k] == 4 || $res2[$k] == 6)) { $values[0]++; }
      if ($v == 5 && ($res2[$k] == 4 || $res2[$k] == 6)) { $values[1]++; }
      if ($v == 3 && ($res2[$k] == 4 || $res2[$k] == 6)) { $values[2]++; }
      if (($v == 4 || $v == 6) && $res2[$k] == 3 ) { $values[3]++; }
      if (($v == 4 || $v == 6) && $res2[$k] == 5 ) { $values[4]++; }
      if (($v == 4 || $v == 6) && $res2[$k] == 2 ) { $values[5]++; }
    }
    $idlist = [[], [], [], [], [], []];
    foreach ($res1 as $k => $v) {
      $i = -1;
      if ($v == 2 && ($res2[$k] == 4 || $res2[$k] == 6)) { $i = 0; }
      if ($v == 5 && ($res2[$k] == 4 || $res2[$k] == 6)) { $i = 1; }
      if ($v == 3 && ($res2[$k] == 4 || $res2[$k] == 6)) { $i = 2; }
      if (($v == 4 || $v == 6) && $res2[$k] == 3 ) { $i = 3; }
      if (($v == 4 || $v == 6) && $res2[$k] == 5 ) { $i = 4; }
      if (($v == 4 || $v == 6) && $res2[$k] == 2 ) { $i = 5; }
      if ($i >= 0) {
        array_push($idlist[$i], $k);
      }
    }
    setupDisplay($h, $id, $sthr, $pthr, $bestid1, $bestid2, $head,
        $values, $idlist);
  }
}

function printCorrelation($file, $str1, $str2, $id, $sthr, $pthr) {
  $h = getHegemon($file, $id);
  $h->initPlatform();
  $ids = $h->getIDs("$str1 $str2");
  $bestid = $h->getBestID(array_keys($ids));
  $res = $h->getCorrelation($bestid);
  $head = ["1.0 to 0.8", "0.8 to 0.5", "0.5 to -0.5", "-0.5 to -1.0"];
  $values = [0, 0, 0, 0];
  $idlist = [[], [], [], []];
  foreach ($res as $k => $v) {
    if ($v >= 0.8) { array_push($idlist[0], $k); $values[0] ++;}
    else if ($v >= 0.5) { array_push($idlist[1], $k); $values[1] ++;}
    else if ($v >= -0.5) { array_push($idlist[2], $k); $values[2] ++;}
    else { array_push($idlist[3], $k); $values[3] ++;}
  }
  setupDisplay($h, $id, $sthr, $pthr, $bestid,"", $head,
      $values, $idlist);
}

function topGenes($file, $id, $num) {
  $h = getHegemon($file, $id);
  $ids = $h->topGenes($num);
  echo "<table border=\"0\">\n";
  foreach ($ids as $k => $v) {
    echo "<tr>\n";
    echo "<td>$k</td><td>". $h->getName($k) ."</td><td>$v</td>\n";
    echo "</tr>\n";
  }
  echo "</table>\n";
}

function getStats($file, $str1, $str2, $id, $sthr, $pthr) {
  $h = getHegemon($file, $id);
  $h->initPlatform();
  $exprFile = $h->getExprFile();
  echo "<table border=0>\n";
  foreach ($h->getIDs($str1) as $v1 => $n1) {
    $ptr1 = $h->getPtr($v1);
    foreach ($h->getIDs($str2) as $v2 => $n2) {
      $ptr2 = $h->getPtr($v2);
      $nm1 = $h->getName($v1);
      $nm2 = $h->getName($v2);
      $src = "explore.php?go=plot&file=$exprFile&id=$id&xn=$n1&yn=$n2" . 
        "&x=$ptr1&y=$ptr2";
      echo "<tr> <td> $nm1 and $nm2 ($v1, $v2) <a href=\"$src\"> p</a>\n";
      echo "<br/> ";
      echo '<img height=240 width=320 src="' . $src . '"  ';
      echo ' onclick="updateTextBox(\'' . $v1 . '\',\'' . $nm1 . '\',\'' 
        . $v2 . '\',\'' . $nm2 . '\');"/>';
      echo '</td><td>';
      list($x_arr, $y_arr, $h_arr) = U::getXandY($exprFile, $ptr1, $ptr2, 0);
      list($minx, $thrx1, $maxx) = U::getThreshold($x_arr, 2, count($x_arr)-2);
      list($miny, $thry1, $maxy) = U::getThreshold($y_arr, 2, count($y_arr)-2);
      $rhash = U::getXYStats($x_arr, $y_arr);
      $bs = U::getBooleanStats($x_arr, $y_arr,
          $thrx1-0.5, $thrx1+0.5, $thry1-0.5, $thry1+0.5);
      $rel = U::getBooleanRelations($bs, $sthr, $pthr);
      $formula = U::getFormula($nm1, $nm2, $rel);
      $r = $rhash['r '];
      echo "<b> $formula </b> <br/>\n";
      echo "<b> Correlation = $r </b> <br/>\n";
      echo "<table style=\"font-size:0.8em;line-height:0.8em;\" border=\"0\">\n";
      echo "<tr> <td> SThrX </td> <td> $thrx1 </td>\n";
      echo "<td> SThrY </td> <td> $thry1 </td> </tr>\n";
      $i = 0;
      $fun = function($x) { return sprintf("%.2f", $x); };
      foreach ($rhash as $k => $v) {
        if (strncmp($k, "box", 3) == 0) {
          $vs = array_map($fun, split(" ", trim($v)));
          $v = join("|", $vs);
        }
        if ($i == 0) {
          echo "<tr> <td> $k </td> <td> $v </td>\n";
        }
        else {
          echo "<td> $k </td> <td> $v </td> </tr>\n";
        }
        $i = 1 - $i;
      }
      if ( $i == 1) {
        echo "<td>  </td> <td>  </td> </tr>\n";
      }
      echo "<tr> <td> bnum </td> <td>". join("|", $bs[0]) ."</td>\n";
      echo "<td> enum </td> <td>". join("|", array_map($fun, $bs[1])) ."</td> </tr>\n";
      echo "<tr> <td> snum </td> <td>". join("|", array_map($fun, $bs[2])) ."</td>\n";
      echo "<td> pnum </td> <td>". join("|", array_map($fun, $bs[3])) ."</td> </tr>\n";
      echo "</table>\n";
      echo '</td></tr>';
    }
  }
  echo "</table>\n";
}

function printAllIDs($file, $str1, $str2, $id) {
  $h = getHegemon($file, $id);
  $h->initPlatform();
  $ids = $h->getIDs($str1);
  $bestid1 = $h->getBestID(array_keys($ids));
  echo "<table border=\"0\">\n";
  foreach ($ids as $k => $v) {
    echo "<tr>\n";
    if (strcmp($bestid1, $k) == 0) {
      echo "<td><b>$k</b></td><td><b>". $h->getName($k) .
        "</b></td><td><b>$v -- Best ID for Gene A</b></td>\n";
    }
    else {
      echo "<td>$k</td><td>". $h->getName($k) ."</td><td>$v</td>\n";
    }
    echo "</tr>\n";
  }
  $ids = $h->getIDs($str2);
  $bestid2 = $h->getBestID(array_keys($ids));
  foreach ($ids as $k => $v) {
    echo "<tr>\n";
    if (strcmp($bestid2, $k) == 0) {
      echo "<td><b>$k</b></td><td><b>". $h->getName($k) .
        "</b></td><td><b>$v -- Best ID for Gene B</b></td>\n";
    }
    else {
      echo "<td>$k</td><td>". $h->getName($k) ."</td><td>$v</td>\n";
    }
    echo "</tr>\n";
  }
  echo "</table>\n";
}

function getPlots($file, $str1, $str2, $id) {
  $h = getHegemon($file, $id);
  $h->initPlatform();
  $exprFile = $h->getExprFile();
  foreach ($h->getIDs($str1) as $v1 => $n1) {
    $ptr1 = $h->getPtr($v1);
    foreach ($h->getIDs($str2) as $v2 => $n2) {
      $ptr2 = $h->getPtr($v2);
      echo $v1 . "\t" . $h->getName($v1) . "\t";
      echo $v2 . "\t" . $h->getName($v2) . "\t";
      echo "explore.php?go=plot&file=$exprFile&id=$id&xn=$n1&yn=$n2" . 
        "&x=$ptr1&y=$ptr2\n";
    }
  }
}

function plotids($file, $f, $id, $x, $y, $xn, $yn, $groups) {
  $h = getHegemon($file, $id);
  $id1 = $h->readID($x);
  $id2 = $h->readID($y);
  $res = [[$id1, $h->getName($id1)], [$id2, $h->getName($id2)]];
  echo json_encode($res);
}

function plot($file, $f, $id, $x, $y, $xn, $yn, $groups) {
  $h = getHegemon($file, $id);
  $sfile = $h->getSurv();
  $better_token = md5(uniqid(rand(), true));
  $outprefix = "tmpdir/tmp$better_token";
  U::generateMatPlot($f, $sfile, $x, $y, $xn, $yn, $groups, 0, $outprefix);
  header("Content-type: image/png");
  $im     = imagecreatefrompng("$outprefix.png");
  imagepng($im);
  imagedestroy($im);
  U::cleanup($outprefix);
}

function data_uri($file, $mime) 
{
  $contents = file_get_contents($file);
  $base64   = base64_encode($contents); 
  return ('data:' . $mime . ';base64,' . $base64);
}

function plotDataUri($file, $f, $id, $x, $y, $xn, $yn, $groups) {
  $h = getHegemon($file, $id);
  $sfile = $h->getSurv();
  $better_token = md5(uniqid(rand(), true));
  $outprefix = "tmpdir/tmp$better_token";
  U::generateMatPlot($f, $sfile, $x, $y, $xn, $yn, $groups, 0, $outprefix);
  echo data_uri("$outprefix.png", "image/png");
  U::cleanup($outprefix);
}

function printSummary($file) {
  $db = new Database($file);
  printHeader();
  printBody($db);
  printFooter();
}

function printHeader() {
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\"
     \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\" lang=\"en\">
  <head>
  <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />
  <title>Exploring Gene Expression Dataset</title>
  <script src=\"https://code.jquery.com/jquery-3.1.0.min.js\" type=\"text/javascript\"></script>
  <script src=\"https://d3js.org/d3.v4.min.js\"></script>
  <link href=\"explore.css\" media=\"screen\" rel=\"Stylesheet\" type=\"text/css\"/>
  <script src=\"explore.js\" type=\"text/javascript\"></script>
  <script src=\"Mouse.js\" type=\"text/javascript\"></script>
  <script src=\"Groups.js\" type=\"text/javascript\"></script>
  </head>
  <body>
";
}

function printBody($db) {
  global $keys;
echo "
    <div id=\"exploreAll\">
      <form name=\"exploreForm\" action=\"\">
      Select Dataset: <select id=\"dataset\" name=\"dataset\">
";
  foreach ($db->getListKey($keys) as $n) {
    $id = $n->getID();
    $h = new Hegemon($n);
    $num = $h->getNum();
    if ($num > 0) {
echo "
      <option value=\"$id\"> ".$n->getName()." (n = $num) </option>
";
    }
  }
echo "
      </select>
      <input type=\"button\" name=\"topGenes\" value=\"topGenes\"
              onclick=\"callTopGenes();\"/>
      nG: <input type=\"text\" size=\"3\" id=\"arg1\"/>
      <div id=\"box\">
      Gene A: <input type=\"text\" size=\"10\" id=\"Ab\" 
              name=\"Ab\" value=\"\" alt=\"Gene A\" />
      Gene B: <input type=\"text\" size=\"10\" id=\"Bb\"
              name=\"Bb\" value=\"\" alt=\"Gene B\" />
          <input type=\"button\" name=\"getIDs\" value=\"getIDs\"
              onclick=\"callGetIDs();\"/>
          <input type=\"button\" name=\"getPlots\" value=\"getPlots\"
              onclick=\"callGetPlots();\"/>
          <input type=\"button\" name=\"getStats\" value=\"getStats\"
              onclick=\"callGetStats();\"/>
          <input type=\"button\" name=\"Explore\" value=\"Explore\"
              onclick=\"callExplore();\"/> <br/>
     SThr: <input type=\"text\" size=\"3\" id=\"sthr\" value=\"3\"/>
     PThr: <input type=\"text\" size=\"3\" id=\"pthr\" value=\"0.1\"/>
          <input type=\"button\" name=\"MiDReG\" value=\"MiDReG\"
              onclick=\"callMiDReG();\"/> 
    CT: <input type=\"text\" size=\"3\" id=\"CT\"
              name=\"CT\" value=\"\" alt=\"Censor Time\"/>
          <input type=\"button\" name=\"getCorr\" value=\"getCorr\"
              onclick=\"callCorr();\"/>
          <input type=\"button\" name=\"Clear\" value=\"Clear\"
              onclick=\"callClear();\"/>
";
echo "
      </div> <!-- end id box -->
      <br clear=\"all\"/>
      <div id=\"results\"> </div>
      <div id=\"lineresults\"> </div>
      </form>
    </div> <!-- end id exploreAll -->
";
}

function printFooter() {
echo "
    <br clear=\"all\"/>
    <div id=\"ref\">
      References:
      <ol>
        <li>
        Debashis Sahoo, David L. Dill, Andrew J. Gentles, Rob Tibshirani,
Sylvia K. Plevritis. Boolean implication networks derived from large scale,
whole genome microarray datasets. Genome Biology, 9:R157, Oct 30 2008.
        </li>
      </ol>
    </div>
    <div id=\"footer\">
      <p>Copyright &copy; 2016 <strong> Author: Debashis Sahoo </strong> 
      &mdash; All rights reserved.</p>
    </div>
  </body>
</html>
";
}

?>
