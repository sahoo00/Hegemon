<?php

function callGroupsPostCommands($file, $groups, $param) {
  if (strcmp($_POST["go"], "survival") == 0) {
    survivalDataUri($file, $_POST['file'], $_POST['id'],
        $_POST['x'], $_POST['y'], $_POST['xn'],$_POST['yn'], 
        $_POST['CT'], $groups);
  }
  if (strcmp($_POST["go"], "getsurvival") == 0) {
    getSurvival($file, $_POST['file'], $_POST['id'],
        $_POST['x'], $_POST['y'], $_POST['xn'],$_POST['yn'], 
        $_POST['CT'], $groups);
  }
  if (strcmp($_POST["go"], "boxplot") == 0) {
    boxplotDataUri($file, $_POST['file'], $_POST['id'],
        $_POST['x'], $_POST['y'], $_POST['xn'],$_POST['yn'], 
        $groups);
  }
  if (strcmp($_POST["go"], "getgcorr") == 0) {
    getgcorr($file, $_POST['file'], $_POST['id'],
        $_POST['x'], $_POST['y'], $_POST['xn'],$_POST['yn'], 
        $groups);
  }
  if (strcmp($_POST["go"], "getgdiff") == 0) {
    getgdiff($file, $_POST['file'], $_POST['id'],
        $_POST['x'], $_POST['y'], $_POST['xn'],$_POST['yn'], 
        $groups);
  }
  if (strcmp($_POST["go"], "sga") == 0) {
    searchGroupArea($file, $_POST['file'], $_POST['id'],
        $_POST['x'], $_POST['y'], $_POST['xn'],$_POST['yn'], 
        $groups, $_POST['clinical'], $_POST['sga']);
  }
}

function callGroupsCommands($file, $groups, $param) {
  if (strcmp($_GET["go"], "explore") == 0) {
    explore($file, $_GET['A'], $_GET['B'], $_GET['id']);
  }
  if (strcmp($_GET["go"], "group") == 0) {
    group($file, $_GET['file'], $_GET['id'],
        $_GET['x'], $_GET['y'],$_GET['xn'],$_GET['yn'],
        $_GET['orix'], $_GET['oriy'], 
        $_GET['top'], $_GET['left'],$_GET['width'], $_GET['height']);
  }
  if (strcmp($_GET["go"], "selectPatientInfo") == 0) {
    getPatientInfo($file, $_GET['id'], $_GET['clinical']);
  }
  if (strcmp($_GET["go"], "getPatients") == 0) {
    getPatientGroup($file, $_GET['id'], $_GET['clinical'], $_GET['value']);
  }
  if (strcmp($_GET["go"], "survival") == 0) {
    survival($file, $_GET['file'], $_GET['id'],
        $_GET['x'], $_GET['y'], $_GET['xn'],$_GET['yn'], 
        $_GET['CT'], $groups);
  }
  if (strcmp($_GET["go"], "getsurvival") == 0) {
    getSurvival($file, $_GET['file'], $_GET['id'],
        $_GET['x'], $_GET['y'], $_GET['xn'],$_GET['yn'], 
        $_GET['CT'], $groups);
  }
  if (strcmp($_GET["go"], "getlstats") == 0) {
    getlstats($file, $_GET['file'], $_GET['id'],
        $_GET['x'], $_GET['y'], $_GET['xn'],$_GET['yn']);
  }
  if (strcmp($_GET["go"], "getrect") == 0) {
    getrect($file, $_GET['file'], $_GET['id'], 
        $_GET['x'], $_GET['y'],$_GET['xn'],$_GET['yn']);
  }
  if (strcmp($_GET["go"], "getrectgroup") == 0) {
    getrectgroup($file, $_GET['file'], $_GET['id'], 
        $_GET['x'], $_GET['y'],$_GET['xn'],$_GET['yn'], $_GET['value']);
  }
  if (strcmp($_GET["go"], "getthr") == 0) {
    getthr($file, $_GET['file'], $_GET['id'], 
        $_GET['x'], $_GET['y'],$_GET['xn'],$_GET['yn']);
  }
  if (strcmp($_GET["go"], "getthrgroup") == 0) {
    getthrgroup($file, $_GET['file'], $_GET['id'], 
        $_GET['x'], $_GET['y'],$_GET['xn'],$_GET['yn'], $_GET['value'],
        $groups, $param);
  }
  if (strcmp($_GET["go"], "boxplot") == 0) {
    boxplot($file, $_GET['file'], $_GET['id'],
        $_GET['x'], $_GET['y'], $_GET['xn'],$_GET['yn'], 
        $groups);
  }
  if (strcmp($_GET["go"], "getgcorr") == 0) {
    getgcorr($file, $_GET['file'], $_GET['id'],
        $_GET['x'], $_GET['y'], $_GET['xn'],$_GET['yn'], 
        $groups);
  }
  if (strcmp($_GET["go"], "getgdiff") == 0) {
    getgdiff($file, $_GET['file'], $_GET['id'],
        $_GET['x'], $_GET['y'], $_GET['xn'],$_GET['yn'], 
        $groups);
  }
  if (strcmp($_GET["go"], "sga") == 0) {
    searchGroupArea($file, $_GET['file'], $_GET['id'],
        $_GET['x'], $_GET['y'], $_GET['xn'],$_GET['yn'], 
        $groups, $_GET['clinical'], $_GET['sga']);
  }
}

function getSelectTool($file) {
  if ($file == null || ($fp = fopen($file, "r")) === FALSE) {
    return "";
  }
  $head = fgets($fp);
  fclose($fp);
  $head = chop($head, "\r\n");
  $headers = explode("\t", $head);
  $str = "<select id=\"clinical0\" name=\"clinical\" onchange=\"callSelectTool();\">\n";
  $str .= "<option value=\"0\"> Select Patient Information </option>\n";
  for ($i = 1; $i < count($headers); $i++) {
    $str .= "<option value=\"$i\">$headers[$i]</option>\n";
  }
  $str .= "</select>\n";
  $str .= "<div id=\"selectPatientInfo\"></div>\n";
  return $str;
}

function explore($file, $str1, $str2, $id) {
  $h = getHegemon($file, $id);
  $h->initPlatform();
  $id1 = null;
  $id2 = null;
  $ids = $h->getIDs("$str1");
  if (count($ids) > 0) {
    $id1 = array_keys($ids)[0];
  }
  $ids = $h->getIDs("$str2");
  if (count($ids) > 0) {
    $id2 = array_keys($ids)[0];
  }
  if ($id1 == null || $id2 == null) {
    return;
  }
  $url = getImgUrl($h, $id, $id1, $id2);
  $str1 = "$id1: ". $h->getName($id1) . " " . $h->getDesc($id1);
  $str2 = "$id2: ". $h->getName($id2) . " " . $h->getDesc($id2);
  $sfile = $h->getSurv();
  $selectTool = getSelectTool($sfile);
  echo "
    <table border=\"0\">
    <tr>
    <td style=\"vertical-align:top\">
    <img id=\"img0\" class=\"groupPlot\" width=\"640\" height=\"480\"
    src=\"$url\"/>
    <br/>
    <div id=\"rect\"></div>
    <div class=\"imginfo\">
    <a target=\"_blank\" id=\"img0link\" href=\"$url\">Image link</a> 
    <a target=\"_blank\" id=\"img1link\" style=\"visibility:hidden;\" href=\"\">Plot link</a> 
    <a target=\"_blank\" id=\"img2link\" style=\"visibility:hidden;\" href=\"\">Survival link</a> 
    <br/>
    <span id=\"gInfoX\"> $str1 </span> <br/>
    <span id=\"gInfoY\"> $str2 </span> <br/>
      <div id=\"lineresults\"> </div>
    </div>
    </td>
    <td style=\"vertical-align:top\">
    <div id=\"tools_display\">
      <input type=\"button\" name=\"Union\" value=\"Union\"
           onclick=\"callUnion();\"/>
      <input type=\"button\" name=\"Intersection\" value=\"Intersection\"
           onclick=\"callIntersection();\"/>
      <input type=\"button\" name=\"Reset\" value=\"Reset\"
           onclick=\"callReset();\"/>
      <input type=\"button\" name=\"Remove\" value=\"Remove\"
           onclick=\"callRemove();\"/>
      <input type=\"button\" name=\"D-AB\" value=\"D-AB\"
           onclick=\"callDiffAB();\"/>
      <input type=\"button\" name=\"D-BA\" value=\"D-BA\"
           onclick=\"callDiffBA();\"/>
      <input type=\"button\" name=\"Scr\" value=\"Scr\"
           onclick=\"callScr();\"/>
      <input type=\"button\" name=\"Rect1\" value=\"Rect1\"
           onclick=\"callRect();\"/>
      <input type=\"button\" name=\"Thr\" value=\"Thr\"
           onclick=\"callThr();\"/>
      <input type=\"button\" name=\"BoxP\" value=\"BoxP\"
           onclick=\"callBoxP();\"/>
      <input type=\"button\" name=\"Show\" value=\"Show\"
           onclick=\"callShow();\"/>
      <input type=\"button\" name=\"ShowIm\" value=\"ShowIm\"
           onclick=\"callShowIm();\"/>
      <input type=\"button\" name=\"Stats\" value=\"Stats\"
           onclick=\"callStats();\"/>
      <input type=\"button\" name=\"Survival\" value=\"Survival\"
           onclick=\"callSurvival();\"/>
      <input type=\"button\" name=\"Corr\" value=\"Corr\"
           onclick=\"callGCorr();\"/>
      <input type=\"button\" name=\"Diff\" value=\"Diff\"
           onclick=\"callGDiff();\"/>
      <input type=\"button\" name=\"Download\" value=\"Download\"
           onclick=\"callDownload();\"/>
      <input type=\"button\" name=\"Search\" value=\"Search\"
           onclick=\"callSearch();\"/>
       <br/>
      $selectTool
    </div>
    <div id=\"group_display\"></div>
    </td>
    </tr>
    <script>
        initDraw(document.getElementById('img0'), loadDisplay);
    </script>
  ";
}

function group($file, $expr, $id, $x, $y, $xn, $yn, $ox, $oy,
    $top, $left, $width, $height) {
  $top = str_replace("px", "", $top);
  $left = str_replace("px", "", $left);
  $width = str_replace("px", "", $width);
  $height = str_replace("px", "", $height);
  $ox = str_replace("px", "", $ox);
  $oy = str_replace("px", "", $oy);
  list($x_arr, $y_arr, $h_arr) = U::getXandY($expr, $x, $y, 0);
  list($x_min, $x_max) = U::getMinMax($x_arr, 2, count($x_arr)-2);
  list($y_min, $y_max) = U::getMinMax($y_arr, 2, count($y_arr)-2);
  $x_min -= 0.5;
  $y_min -= 0.5;
  $x_max += 0.5;
  $y_max += 0.5;
  $x_name = $xn;
  $y_name = $yn;
  $o_x = 70 + $ox; $o_y = 480 - 54 + $oy;
  $f_x = 640 - 70 + $ox; $f_y = 54 + $oy;
  $r_o_x = $x_min + ($x_max - $x_min) * ($left - $o_x)/($f_x - $o_x);
  $r_o_y = $y_min + ($y_max - $y_min) * ($o_y - $top - $height) /($o_y - $f_y);
  $r_f_x = $x_min + ($x_max - $x_min) * ($left + $width - $o_x)/($f_x - $o_x);
  $r_f_y = $y_min + ($y_max - $y_min) * ($o_y - $top) /($o_y - $f_y);
  $num = count($x_arr);
  $gsm_arr = array();
  for($i=2; $i < $num; $i++){       
    if (preg_match('/^\s*$/', $x_arr[$i])) {
      continue;
    }
    if (preg_match('/^\s*$/', $y_arr[$i])) {
      continue;
    }
    if ($x_arr[$i] >= $r_o_x && $x_arr[$i] <= $r_f_x && 
        $y_arr[$i] >= $r_o_y && $y_arr[$i] <= $r_f_y) {
      array_push($gsm_arr, $i);
    }
  }
  echo count($gsm_arr), "\n";
  echo "ArrayID\t$x_name\t$y_name\n";
  foreach ($gsm_arr as $i) {
    $str = $h_arr[$i];
    echo sprintf("%s\t%.2f\t%.2f\n", $str, $x_arr[$i], $y_arr[$i]);
  }
}

function getPatientGroup($file, $id, $clinical, $value) {
  $h = getHegemon($file, $id);
  $sfile = $h->getSurv();
  if ($sfile == null || ($fp = fopen($sfile, "r")) === FALSE) {
    return;
  }
  $head = fgets($fp);
  $head = chop($head, "\r\n");
  $headers = explode("\t", $head);
  $res = array();
  if ($clinical > 0 && $clinical < count($headers)) {
    $type = $headers[$clinical];
    while (!feof($fp))
    {
      $line = fgets($fp);
      if ($line == "") {
        continue;
      }
      $line = chop($line, "\r\n");
      $list = explode("\t", $line);
      $v = "";
      if ($clinical < count($list)) {
        $v = $list[$clinical];
      }
      if (strcmp($type, "status") == 0 || strncmp($type, "c ", 2) == 0) {
        if ($v === $value) {
          $res[$list[0]] = $value;
        }
      }
      if (strcmp($type, "time") == 0 || strncmp($type, "n ", 2) == 0) {
        list($min, $max) = explode(":", $value);
        if (is_numeric($v) && $v >= $min && $v <= $max) {
          $res[$list[0]] = $v;
        }
      }
    }
  }
  fclose($fp);
  echo count($res), "\n";
  echo "ArrayID\tValue\n";
  foreach ($res as $id => $v) {
    printf("%s\t%.2f\n", $id, $v);
  }
  return;
}

function getPatientInfo($file, $id, $clinical) {
  $h = getHegemon($file, $id);
  $sfile = $h->getSurv();
  if ($sfile == null || ($fp = fopen($sfile, "r")) === FALSE) {
    return;
  }
  $head = fgets($fp);
  $head = chop($head, "\r\n");
  $headers = explode("\t", $head);
  if ($clinical > 0 && $clinical < count($headers)) {
    $values = array();
    while (!feof($fp))
    {
      $line = fgets($fp);
      if ($line == "") {
        continue;
      }
      $line = chop($line, "\r\n");
      $list = explode("\t", $line);
      $v = "";
      if ($clinical < count($list)) {
        $v = $list[$clinical];
      }
      array_push($values, $v);
    }
    $type = $headers[$clinical];
    if (strcmp($type, "status") == 0 || strncmp($type, "c ", 2) == 0) {
      $hash = array();
      foreach ($values as $v) { $hash[$v] = 1; }
      echo "<select id=\"PiC\" name=\"PiC\" onchange=\"callPatientGroup('PiC', $clinical);\">";
      echo "<option value=\"\"> Select value </option> ";
      $keys = array_keys($hash);
      if (count($keys) > 20) {
        asort($keys);
        foreach ($keys as $k) {
          echo "<option value=\"$k\"> $k </option> ";
        }
      }
      else {
        foreach ($hash as $k => $v) {
          echo "<option value=\"$k\"> $k </option> ";
        }
      }
      echo "</select>";
    }
    if (strcmp($type, "time") == 0 || strncmp($type, "n ", 2) == 0) {
      echo "
        <input type=\"text\" size=\"10\" id=\"PiN\" 
        name=\"PiN\" value=\"\" alt=\"Patient Information\"/>
        ";
      printf("%.1f:%.1f", min_mod($values), max_mod($values));
      echo "
      <input type=\"button\" name=\"GO\" value=\"GO\"
           onclick=\"callPatientGroup('PiN', $clinical);\"/>
           ";
    }
  }
  fclose($fp);
  return;
}

function survivalImage($file, $expr, $id, $x, $y, $xn, $yn, $ct, $groups) {
  if (!$groups) {
    echo "No groups\n";
    return;
  }
  $h = getHegemon($file, $id);
  $sfile = $h->getSurv();
  $better_token = md5(uniqid(rand(), true));
  $outprefix = "tmpdir/tmp$better_token";
  if (($fp = fopen("$outprefix.R", "w")) === FALSE) {
    echo "Can't open file tmp.R <br>";
  }
  $res = U::getSurvivalScript($outprefix, $sfile, $ct, $groups);
  fwrite($fp, $res);
  fclose($fp);
  if (($fp = fopen("$outprefix.sh", "w")) === FALSE) {
    echo "Can't open file tmp.sh <br>";
  }
  fwrite($fp, "/usr/bin/R --slave < $outprefix.R\n");
  fclose($fp);

  $cmd = "bash $outprefix.sh";
  if ( ($fh = popen($cmd, 'r')) === false )
    die("Open failed: ${php_errormsg}\n");
  pclose($fh);
  return $outprefix;
}

function survivalDataUri($file, $expr, $id, $x, $y, $xn, $yn, $ct, $groups) {
  if (!$groups) {
    echo "No groups\n";
    return;
  }
  $outprefix = survivalImage($file, $expr, $id, $x, $y, $xn, $yn, $ct, $groups);
  echo data_uri("$outprefix.png", "image/png");
  U::cleanup($outprefix);
}

function survival($file, $expr, $id, $x, $y, $xn, $yn, $ct, $groups) {
  if (!$groups) {
    echo "No groups\n";
    return;
  }
  $outprefix = survivalImage($file, $expr, $id, $x, $y, $xn, $yn, $ct, $groups);
  header("Content-type: image/png");
  $im     = imagecreatefrompng("$outprefix.png");
  imagepng($im);
  imagedestroy($im);
  U::cleanup($outprefix);
}

function getSurvival($file, $expr, $id, $x, $y, $xn, $yn, $ct, $groups) {
  if (!$groups) {
    echo "No groups\n";
    return;
  }
  $h = getHegemon($file, $id);
  $sfile = $h->getSurv();
  $better_token = md5(uniqid(rand(), true));
  $outprefix = "tmpdir/tmp$better_token";
  $res = U::getSurvivalScript($outprefix, $sfile, $ct, $groups);
  echo "<pre>\n";
  echo $res;
  echo "</pre>\n";
}

function getlstats($file, $f, $id, $x, $y, $xn, $yn) {
  list($x_arr, $y_arr, $h_arr) = U::getXandY($f, $x, $y, 0);
  list($thrx0, $thrx1, $thrx2) = U::getThreshold($x_arr, 2, count($x_arr)-2);
  list($thry0, $thry1, $thry2) = U::getThreshold($y_arr, 2, count($y_arr)-2);
  $rhash = U::getXYStats($x_arr, $y_arr);
  echo "<br clear=\"all\"/>\n";
  echo "<table border=\"0\">\n";
  echo "<tr> <td> SThrX </td> <td> $thrx1 </td> </tr>\n";
  echo "<tr> <td> SThrY </td> <td> $thry1 </td> </tr>\n";
  foreach ($rhash as $k => $v) {
    echo "<tr> <td> $k </td> <td> $v </td> </tr>\n";
  }
  echo "</table>\n";
}

function getOutprefix() {
  $better_token = md5(uniqid(rand(), true));
  $outprefix = "tmpdir/tmp$better_token";
  return $outprefix;
}

function getPlotType($value) {
  $list = explode(",", $value);
  $num = count($list);
  if ($num >= 3) {
    return $list[2];
  }
  else {
    return 'scatterplot';
  }
}

function getThresholdValues($value) {
  list($thrx0, $thrx1, $thrx2) = array("", "", "");
  list($thry0, $thry1, $thry2) = array("", "", "");
  $list = explode(",", $value);
  $num = count($list);
  if ($num >= 1) {
    $l = explode(":", $list[0]);
    if (count($l) == 1) { $thrx1 = $l[0]; }
    if (count($l) == 2) { $thrx0 = $l[0]; $thrx2 = $l[1]; }
    if (count($l) == 3) { $thrx0 = $l[0]; $thrx1 = $l[1]; $thrx2 = $l[2];}
  }
  if ($num >= 2) {
    $l = explode(":", $list[1]);
    if (count($l) == 1) { $thry1 = $l[0]; }
    if (count($l) == 2) { $thry0 = $l[0]; $thry2 = $l[1]; }
    if (count($l) == 3) { $thry0 = $l[0]; $thry1 = $l[1]; $thry2 = $l[2];}
  }
  return array($thrx0, $thrx1, $thrx2, $thry0, $thry1, $thry2);
}

function plotLine($fp, $type, $thr, $w, $c) {
  if ($thr != "") {
    fwrite($fp, "ax.ax$type"."line($thr, linewidth=$w, color='$c')\n");
  }
}

function plotStep($fp, $thr0, $thr1, $thr2, $w) {
  if ($thr1 != "") {
    fwrite($fp, "
i = len([x for x,c in tuples if x < $thr1])
m1 = mean([x for x,c in tuples if x < $thr1])
m2 = mean([x for x,c in tuples if x >= $thr1])
ax.plot([0, i], [m1, m1], linewidth=$w, color='b')
ax.plot([i, len(tuples)], [m2, m2], linewidth=$w, color='b')
ax.plot([i, i], [m1, m2], linewidth=$w, color='b')
ax.plot([0, len(tuples)], [$thr1, $thr1], linewidth=$w, color='r')
");
  }
  if ($thr0 != "") {
    fwrite($fp, "
ax.plot([0, len(tuples)], [$thr0, $thr0], linewidth=$w, color='c')
");
  }
  if ($thr2 != "") {
    fwrite($fp, "
ax.plot([0, len(tuples)], [$thr2, $thr2], linewidth=$w, color='c')
");
  }
}

function getthrgroup($file, $f, $id, $x, $y, $xn, $yn, $value, $groups, $param) {
  $value = str_replace(" ", "", $value);
  if ($value == "") {
    return;
  }
  list($thrx0, $thrx1, $thrx2, $thry0, $thry1, $thry2) = 
    getThresholdValues($value);
  $plotType = getPlotType($value);
  $h = getHegemon($file, $id);
  $sfile = $h->getSurv();
  $outprefix = getOutprefix();
  list($x_arr, $y_arr, $h_arr) = U::getXandY($f, $x, $y, 0);
  list($minx, $maxx) = U::getMinMax($x_arr, 2, count($x_arr)-2);
  list($miny, $maxy) = U::getMinMax($y_arr, 2, count($y_arr)-2);
  $p_arr = U::getPArray($sfile, $h_arr, $groups);
  U::setupMatData($x_arr, $y_arr, $p_arr, $outprefix, $param);
  $outfile = "$outprefix.py";
  if (($fp = fopen($outfile, "w")) === FALSE) {
    echo "Can't open file $outfile <br>";
  }
  $x_id = $x_arr[0];
  $y_id = $y_arr[0];
  fwrite($fp, "
import matplotlib
matplotlib.use('agg')
import re
from pylab import *
from numpy import *
    
data = {}
f = open('$outprefix.data', 'r')
index = 0
for line in f:
    t = line.split(' ');
    t[2] = t[2].strip();
    if (t[2] not in data):
      data[t[2]] = [[], [], []]
    data[t[2]][0] += [float(t[0])];
    data[t[2]][1] += [float(t[1])];
    data[t[2]][2] += [index];
    index = index + 1
fig = figure(figsize=(6.4,4.8))
");
  if ($plotType == "scatterplot") {
    fwrite($fp, "
ax = fig.add_axes([70.0/640, 54.0/480, 1-2*70.0/640, 1-2*54.0/480])
for c in data:
    ax.plot(data[c][0],data[c][1], color=c, ls='None', marker='+', mew=1.1, ms=4, mec=c)
ax.set_xlabel('$x_id:  $xn', fontsize=10)
ax.set_ylabel('$y_id:  $yn', fontsize=10)
");
    plotLine($fp, 'v', $thrx0, 0.5, 'c');
    plotLine($fp, 'v', $thrx1, 0.5, 'r');
    plotLine($fp, 'v', $thrx2, 0.5, 'c');
    plotLine($fp, 'h', $thry0, 0.5, 'c');
    plotLine($fp, 'h', $thry1, 0.5, 'r');
    plotLine($fp, 'h', $thry2, 0.5, 'c');
    fwrite($fp, "ax.axis([$minx-0.5, $maxx+0.5, $miny-0.5, $maxy+0.5])\n");
  }
  if ($plotType == "step") {
    fwrite($fp, "
ax = fig.add_subplot(221)
tuples = []
for c in data:
    tuples = tuples + [[x, c] for x in data[c][0]]
tuples.sort()
for i,(x,c) in enumerate(tuples):
    ax.plot(i,x, color=c, ls='None', marker='+', mew=1.1, ms=4, mec=c)
ax.set_xlabel('Sorted arrays', fontsize=10)
ax.set_ylabel('$xn expression', fontsize=10)
");
    plotStep($fp, $thrx0, $thrx1, $thrx2, 0.5);
    fwrite($fp, "
ax.set_xlim([-0.05*len(tuples), 1.05*len(tuples)])
ax = fig.add_subplot(224)
tuples = []
for c in data:
    tuples = tuples + [[x, c] for x in data[c][1]]
tuples.sort()
for i,(x,c) in enumerate(tuples):
    ax.plot(i,x, color=c, ls='None', marker='+', mew=1.1, ms=4, mec=c)
ax.set_xlabel('Sorted arrays', fontsize=10)
ax.set_ylabel('$yn expression', fontsize=10)
");
    plotStep($fp, $thry0, $thry1, $thry2, 0.5);
    fwrite($fp, "ax.set_xlim([-0.05*len(tuples), 1.05*len(tuples)])\n");
  }
  fwrite($fp, "fig.savefig('$outprefix.png', dpi=100)");
  fclose($fp);
  $cmd = "HOME=tmpdir /usr/bin/python $outfile";
  if ( ($fh = popen($cmd, 'w')) === false )
    die("Open failed: ${php_errormsg}\n");
  pclose($fh);
  header("Content-type: image/png");
  $im     = imagecreatefrompng("$outprefix.png");
  imagepng($im);
  imagedestroy($im);
  U::cleanup($outprefix);
}

function getthr($file, $f, $id, $x, $y, $xn, $yn) {
  list($x_arr, $y_arr, $h_arr) = U::getXandY($f, $x, $y, 0);
  list($thrx0, $thrx1, $thrx2) = U::getThreshold($x_arr, 2, count($x_arr)-2);
  list($thry0, $thry1, $thry2) = U::getThreshold($y_arr, 2, count($y_arr)-2);
  echo "
    <input type=\"text\" size=\"20\" id=\"rt\" 
    name=\"rt\" value=\"";
  printf("%.1f:%.1f:%.1f,%.1f:%.1f:%.1f", $thrx0, $thrx1, $thrx2, 
        $thry0, $thry1, $thry2);
  echo "\" alt=\"Rect Information\"/>
    <input type=\"button\" name=\"GO\" value=\"GO\"
    onclick=\"callThrGroup('rt');\"/>
    ";
}

function getrectgroup($file, $f, $id, $x, $y, $xn, $yn, $value) {
  list($x_arr, $y_arr, $h_arr) = U::getXandY($f, $x, $y, 0);
  $res = array();
  $value = str_replace(" ", "", $value);
  if ($value != "") {
    $list = explode(",", $value);
    $num = count($h_arr);
    if (count($list) >= 1) {
      if ($list[0] == "") {
        for($i=2; $i < $num; $i++){       
          $res[$h_arr[$i]] = 1;
        }
      }
      else {
        $l = explode(":", $list[0]);
        for($i=2; $i < $num; $i++){       
          if (preg_match('/^\s*$/', $x_arr[$i])) {
            continue;
          }
          if ($x_arr[$i] >= $l[0] && $x_arr[$i] <= $l[1]) {
            $res[$h_arr[$i]] = 1;
          }
        }
      }
    }
    if (count($list) >= 2) {
      if ($list[1] != "") {
        $l = explode(":", $list[1]);
        for($i=2; $i < $num; $i++){       
          if (preg_match('/^\s*$/', $y_arr[$i])) {
            unset($res[$h_arr[$i]]);
            continue;
          }
          if ($y_arr[$i] < $l[0] || $y_arr[$i] > $l[1]) {
            unset($res[$h_arr[$i]]);
          }
        }
      }
    }
  }
  echo count($res), "\n";
  echo "ArrayID\tValue\n";
  foreach ($res as $id => $v) {
    printf("%s\t%.2f\n", $id, $v);
  }
}

function getrect($file, $f, $id, $x, $y, $xn, $yn) {
  list($x_arr, $y_arr, $h_arr) = U::getXandY($f, $x, $y, 0);
  list($x_min, $x_max) = U::getMinMax($x_arr, 2, count($x_arr)-2);
  list($y_min, $y_max) = U::getMinMax($y_arr, 2, count($y_arr)-2);
  echo "
    <input type=\"text\" size=\"10\" id=\"rt\" 
    name=\"rt\" value=\"\" alt=\"Rect Information\"/>
    ";
  printf("%.2f:%.2f,%.2f:%.2f", $x_min, $x_max, $y_min, $y_max);
  echo "
    <input type=\"button\" name=\"GO\" value=\"GO\"
    onclick=\"callRectGroup('rt');\"/>
    ";
}

function boxplotImage($file, $expr, $id, $x, $y, $xn, $yn, $groups) {
  global $colors;
  if (!$groups) {
    echo "No groups\n";
    return;
  }
  $better_token = md5(uniqid(rand(), true));
  $outprefix = "tmpdir/tmp$better_token";
  if (($fp = fopen("$outprefix.R", "w")) === FALSE) {
    echo "Can't open file tmp.R <br>";
  }
  list($z_arr, $h_arr) = U::getX($expr, $y, 0);
  $z_n = $yn;
  $z_id = $z_arr[0];
  $a_hash = array();
  for ($i = 0; $i < count($h_arr); $i++) {
    $a_hash[$h_arr[$i]] = $i;
  }
  $list = explode(";", $groups);
  $data = array();
  $labels = array();
  $names = array();
  $clrs = array();
  foreach ($list as $g) {
    if ($g != '') {
      list($i, $nm, $v) = explode("=", $g, 3);
      $nmps = explode(",", $nm);
      if (count($nmps) > 1) {
        $colors[($i+2) % count($colors)] = trim($nmps[1]);
        $nm = $nmps[0];
      }
      foreach (explode(":", $v) as $a) {
        if (array_key_exists($a, $a_hash)) {
          if (!preg_match('/^\s*$/', $z_arr[$a_hash[$a]])) {
            array_push($data, $z_arr[$a_hash[$a]]);
            array_push($labels, $nm);
            $names[$i] = $nm;
            $clrs[$i] = U::getColor($i+2);
          }
        }
      }
    }
  }
  $res = "
png(filename=\"$outprefix.png\", width=640, height=480, pointsize=15)
d <-  c(" . join(",", $data) . ")
l <-  c(\"" . join("\",\"", $labels) . "\")
n <-  c(\"" . join("\",\"", $names) . "\")
c <-  c(\"" . join("\",\"", $clrs) . "\")
par(font.lab=2)
boxplot(d ~ l, col=c, ylab=\"$z_id : $z_n Gene Expression\")
#boxplot(d ~ l, col=c, names=n, xaxt=\"n\",
#    ylab=\"Normalized Log2 Expression values\")
#axis(1, at=1:length(n), labels=n, padj=1, font=2)
";
  #echo "<pre>$res</pre>";
  fwrite($fp, $res);
  fclose($fp);
  if (($fp = fopen("$outprefix.sh", "w")) === FALSE) {
    echo "Can't open file tmp.sh <br>";
  }
  fwrite($fp, "/usr/bin/R --slave < $outprefix.R\n");
  fclose($fp);

  $cmd = "bash $outprefix.sh";
  if ( ($fh = popen($cmd, 'r')) === false )
    die("Open failed: ${php_errormsg}\n");
  pclose($fh);
  return $outprefix;
}

function boxplotDataUri($file, $expr, $id, $x, $y, $xn, $yn, $groups) {
  if (!$groups) {
    echo "No groups\n";
    return;
  }
  $outprefix = boxplotImage($file, $expr, $id, $x, $y, $xn, $yn, $groups);
  echo data_uri("$outprefix.png", "image/png");
  U::cleanup($outprefix);
}

function boxplot($file, $expr, $id, $x, $y, $xn, $yn, $groups) {
  if (!$groups) {
    echo "No groups\n";
    return;
  }
  $outprefix = boxplotImage($file, $expr, $id, $x, $y, $xn, $yn, $groups);
  header("Content-type: image/png");
  $im     = imagecreatefrompng("$outprefix.png");
  imagepng($im);
  imagedestroy($im);
  U::cleanup($outprefix);
}

function getgcorr($file, $expr, $id, $x, $y, $xn, $yn, $groups) {
  $h = getHegemon($file, $id);
  $id1 = $h->readID($x);
  $id2 = $h->readID($y);
  $res = null;
  if (!$groups) {
    $h->printJSONCorrelation2($id1, $id2, null);
  }
  else {
    $better_token = md5(uniqid(rand(), true));
    $outprefix = "tmpdir/tmp$better_token";
    U::setupArrayListData($groups, $outprefix);
    $h->printJSONCorrelation2($id1, $id2, "$outprefix.data");
    U::cleanup($outprefix);
  }
}

function getgdiff($file, $expr, $id, $x, $y, $xn, $yn, $groups) {
  $h = getHegemon($file, $id);
  $res = null;
  if ($groups) {
    $better_token = md5(uniqid(rand(), true));
    $outprefix = "tmpdir/tmp$better_token";
    U::setupArrayListData($groups, $outprefix);
    $h->printJSONDiff("$outprefix.data");
    U::cleanup($outprefix);
  }
}

function searchGroupArea($file, $expr, $id, $x, $y, $xn, $yn, 
  $groups, $clinical, $sga) {
  $h = getHegemon($file, $id);
  $id1 = $h->readID($x);
  $id2 = $h->readID($y);
  $e1 = $h->getExprData($id1);
  $e2 = $h->getExprData($id2);
  if ($sga != '') {
    $list = preg_split("/\s+/", $sga);
    $res = array();
    if (count($list) > 0 && $list[0] === "Boolean") {
      $f = $h->getExprFile();
      $x_t = FALSE;
      $y_t = FALSE;
      if (count($list) > 1) { $x_t = $list[1]; }
      if (count($list) > 2) { $y_t = $list[2]; }
      list($x_arr, $y_arr, $h_arr) = U::getXandY($f, $x, $y, 0);
      $gr = U::generateBooleanGroups($file, $xn, $yn, $x_arr, $y_arr, $h_arr,
        $x_t, $y_t);
      foreach ($gr as $g) {
        echo count($g[2])."\n";
        echo "$g[1]\n";
        foreach ($g[2] as $i) {
          echo "$i\n";
        }
      }
    }
    else {
      if (count($list) > 0 && $list[0] === "search") {
        for ($i = $h->start; $i <= $h->end; $i++) {
          $hdr = strtolower($h->headers[$i]);
          for ($j = 1; $j < count($list); $j++) {
            $k = strtolower($list[$j]);
            if (strpos($hdr, $k) !== false) {
              array_push($res, $i);
              break;
            }
          }
        }
      }
      else {
        $hh = array();
        for ($i = $h->start; $i <= $h->end; $i++) {
          $hdr = $h->headers[$i];
          $hh[strtolower($hdr)] = $i;
        }
        foreach ($list as $k) {
          if (array_key_exists(strtolower($k), $hh)) {
            array_push($res, $hh[strtolower($k)]);
          }
        }
      }
      echo count($res)."\n";
      echo "ArrayID\t$xn\t$yn\n";
      foreach ($res as $i) {
        echo $h->headers[$i]."\t".$e1[$i]."\t".$e2[$i]."\n";
      }
    }
  }
  elseif ($groups != '') {
    $ahash = U::joinGroupsArray($groups);
    for ($i = $h->start; $i <= $h->end; $i++) {
      $hdr = $h->headers[$i];
      if (array_key_exists($hdr, $ahash)) {
        $ahash[$hdr] = $i;
      }
    }
    $sfile = $h->getSurv();
    if ($sfile == null || ($fp = fopen($sfile, "r")) === FALSE) {
      return;
    }
    echo count($ahash)."\n";
    echo "ArrayID\tValue\t$xn\t$yn\n";
    $head = fgets($fp);
    $head = chop($head, "\r\n");
    $headers = explode("\t", $head);
    if ($clinical > 0 && $clinical < count($headers)) {
      $type = $headers[$clinical];
      while (!feof($fp)) {
        $line = fgets($fp);
        if ($line == "") {
          continue;
        }
        $line = chop($line, "\r\n");
        $list = explode("\t", $line);
        $arr = $list[0];
        $v = "";
        if (array_key_exists($arr, $ahash) && $clinical < count($list)) {
          $v = $list[$clinical];
          $i = $ahash[$arr];
          echo "$arr\t$v\t".$e1[$i]."\t".$e2[$i]."\n";
        }
      }
    }
    fclose($fp);
  }
}

?>
