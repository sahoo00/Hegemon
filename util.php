<?php

class U {

  public function intersection($a, $b) {
    $hash = array();
    $res = array();
    foreach ($a as $k) {
      if (!array_key_exists($k, $hash)) {
        $hash[$k] = 1;
      }
    }
    foreach ($b as $k) {
      if (array_key_exists($k, $hash)) {
        array_push($res, $k);
      }
    }
    return $res;
  }

  public function union($a, $b) {
    $hash = array();
    $res = array();
    foreach ($a as $k) {
      if (!array_key_exists($k, $hash)) {
        $hash[$k] = 1;
        array_push($res, $k);
      }
    }
    foreach ($b as $k) {
      if (!array_key_exists($k, $hash)) {
        $hash[$k] = 1;
        array_push($res, $k);
      }
    }
    return $res;
  }

  public function diff($a, $b) {
    $hash = array();
    $res = array();
    foreach ($b as $k) {
      if (!array_key_exists($k, $hash)) {
        $hash[$k] = 1;
      }
    }
    foreach ($a as $k) {
      if (!array_key_exists($k, $hash)) {
        $hash[$k] = 1;
        array_push($res, $k);
      }
    }
    return $res;
  }

  public function intersectHash($a, $b) {
    $hash = array();
    foreach ($a as $k => $v) {
      if (array_key_exists($k, $b)) {
        $hash[$k] = $v;
      }
    }
    return $hash;
  }
  public function unionHash($a, $b) {
    $hash = array();
    foreach ($a as $k => $v) {
      $hash[$k] = $v;
    }
    foreach ($b as $k => $v) {
      $hash[$k] = $v;
    }
    return $hash;
  }

  public function diffHash($a, $b) {
    $hash = array();
    foreach ($a as $k => $v) {
      if (!array_key_exists($k, $b)) {
        $hash[$k] = $v;
      }
    }
    return $hash;
  }

  public function sum($arr, $start = FALSE, $end = FALSE) {
    if ($start == FALSE) {
      $start = 0;
    }
    if ($end == FALSE) {
      $end = count($arr) - 1;
    }
    $result = 0;
    if ($start > $end) {
      return $result;
    }
    for ($i = $start; $i <= $end; $i++) {
      $result += $arr[$i];
    }
    return $result;
  }

  public function mean($arr, $start = FALSE, $end = FALSE) {
    if ($start == FALSE) {
      $start = 0;
    }
    if ($end == FALSE) {
      $end = count($arr) - 1;
    }
    $result = 0;
    if ($start > $end) {
      return $result;
    }
    for ($i = $start; $i <= $end; $i++) {
      $result += $arr[$i];
    }
    return $result / ($end - $start + 1);
  }

  public function covariance($array1ref, $array2ref) {
    $result = 0;
    for ($i = 0; $i < count($array1ref); $i++) {
      $result += $array1ref[$i] * $array2ref[$i];
    }
    $result /= count($array1ref);
    $result -= self::mean($array1ref) * self::mean($array2ref);
    return $result;
  }

  public function correlation($array1ref, $array2ref) {
    $s_sum = array(0, 0);
    $sum_squared = array(0, 0);
    foreach ($array1ref as $k) { $s_sum[0] += $k;  $sum_squared[0] += $k*$k; }
    foreach ($array2ref as $k) { $s_sum[1] += $k;  $sum_squared[1] += $k*$k; }
    $n = count($array1ref);
    $numerator = ($n * $n) * self::covariance($array1ref, $array2ref);
    $denominator = sqrt((($n * $sum_squared[0]) - ($s_sum[0]*$s_sum[0])) *
        (($n * $sum_squared[1]) - ($s_sum[1]*$s_sum[1])));
    $r = 0;
    if ($denominator == 0) {
      die("The denominator is 0.\n");
    } else {
      $r = $numerator / $denominator;
    }
    return $r;
  }

  public function mse($arrayref, $start = FALSE, $end = FALSE) {
    if ($start == FALSE) {
      $start = 0;
    }
    if ($end == FALSE) {
      $end = count($arr) - 1;
    }
    $result = 0;
    if ($start > $end) {
      return $result;
    }
    $m = self::mean($arrayref, $start, $end);
    $result = 0;
    for ($i = $start; $i <= $end; $i++) {
      $result += ($arrayref[$i] - $m) * ($arrayref[$i] - $m);
    }
    return $result;
  }

  public function max($arrayref, $start = FALSE, $end = FALSE) {
    if ($start == FALSE) {
      $start = 0;
    }
    if ($end == FALSE) {
      $end = count($arr) - 1;
    }
    $result = $arrayref[$start];
    if ($start > $end) {
      return $result;
    }
    for ($i = $start; $i <= $end; $i++) {
      if (!isset($arrayref[$i]) || strlen($arrayref[$i]) == 0) {
        continue;
      }
      if ($result < $arrayref[$i]) {
        $result = $arrayref[$i];
      }
    }
    return $result;
  }

  public function min($arrayref, $start = FALSE, $end = FALSE) {
    if ($start == FALSE) {
      $start = 0;
    }
    if ($end == FALSE) {
      $end = count($arr) - 1;
    }
    $result = $arrayref[$start];
    if ($start > $end) {
      return $result;
    }
    for ($i = $start; $i <= $end; $i++) {
      if (!isset($arrayref[$i]) || strlen($arrayref[$i]) == 0) {
        continue;
      }
      if ($result > $arrayref[$i]) {
        $result = $arrayref[$i];
      }
    }
    return $result;
  }

  public function fitStep($data, $start=FALSE, $end=FALSE) {
    if ($start == FALSE) {
      $start = 0;
    }
    if ($end == FALSE) {
      $end = count($data) - 1;
    }
    $count = $end - $start + 1;
    if ($count <= 0) {
      return array(0, 0, 0, 0, 0, 0, 0, 0);
    }
    $sseArray = array_map(create_function(null,"return 0;") , range(0, ($count - 1)));
    $sum = self::sum($data, $start, $end);
    $mean = self::mean($data, $start, $end);
    $sstot = self::mse($data, $start, $end);
    $sum1 = 0.0;
    $count1 = 0;
    $m1 = 0.0;
    $sum2 = $sum;
    $count2 = $count;
    $m2 = ($sum/$count);
    $sum1sq = 0.0;
    $sum2sq = $sstot;
    $sse = $sum1sq + $sum2sq;
    for ($i = 0; $i < $count; $i++) {
      $entry = $data[$i + $start];
      if (!isset($entry) || strlen($entry) == 0) {
        $sseArray[$i] = $sse;
        continue;
      }
      $count1 ++;
      $count2 --;
      if ($count2 == 0) {
        $sseArray[$i] = $sstot;
        continue;
      }
      $tmp = ($mean - ($entry + $sum1)/$count1);
      $sum1sq = $sum1sq + ($entry - $mean) * ($entry - $mean) - 
        $tmp * $tmp * $count1 + ($count1 - 1) * ($mean - $m1) * ($mean - $m1);
      $tmp = ($mean - ($sum2 - $entry)/$count2);
      $sum2sq = $sum2sq - ($entry - $mean) * ($entry - $mean) - 
        $tmp * $tmp * $count2 + ($count2 + 1) * ($mean - $m2) * ($mean - $m2);
      $sum1 += $entry;
      $sum2 -= $entry;
      $m1 = $sum1/$count1;
      $m2 = $sum2/$count2;
      $sse = $sum1sq + $sum2sq;
      $sseArray[$i] = $sse;
    }
    $bestSse = null;
    $bestIndex = 0;
    for ($i = 0; $i < $count ; $i++) {
      $index = $i + $start;
      if (!isset($bestSse)) {
        $bestSse = $sseArray[$i];
        $bestIndex = $index;
      }
      if ($sseArray[$i] < $bestSse) {
        $bestSse = $sseArray[$i];
        $bestIndex = $index;
      }
    }
    $m1 = self::mean($data, $start, $bestIndex);
    $m2 = self::mean($data, $bestIndex + 1, $end);
    $thr = ($m1 + $m2)/2.0;
    $label = 0;
    if ($m1 < $m2) {
      $label = 1;
    }
    else {
      $label = 2;
    }
    $statistic = 0;
    if ($bestSse > 0) {
      if ($count > 4) {
        $statistic = ($sstot - $bestSse)/3/($bestSse/($count - 4));
      }
      else {
        $statistic = ($sstot - $bestSse)/2/$bestSse;
      }
    }
    return array($bestIndex, $bestSse, $sstot, $statistic, 
        $m1, $m2, $thr, $label);
  }

  public function getStepMinerThr($data, $start=FALSE, $end=FALSE) {
    if ($start == FALSE) {
      $start = 0;
    }
    if ($end == FALSE) {
      $end = count($data) - 1;
    }
    $array = array();
    for ($i = $start; $i <= $end; $i++) {
      if (!isset($data[$i]) || strlen($data[$i]) == 0) {
        continue;
      }
      array_push($array, $data[$i]);
    }
    sort($array);
    return self::fitStep($array);
  }

  public function getXYstats($x_arr, $y_arr) {
    $better_token = md5(uniqid(rand(), true));
    $outprefix = "tmpdir/tmp$better_token";
    if (($fp = fopen("$outprefix.R", "w")) === FALSE) {
      echo "Can't open file tmp.R <br>";
    }
    $num = count($x_arr);
    $xa = array();
    $ya = array();
    for($i=2; $i < $num; $i++){       
      if (ereg("^\s*$", $x_arr[$i])) {
        continue;
      }
      if (ereg("^\s*$", $y_arr[$i])) {
        continue;
      }
      array_push($xa, $x_arr[$i]);
      array_push($ya, $y_arr[$i]);
    }
    $res = "x <- c(" . join(",", $xa) . ");\n";
    $res .= "y <- c(" . join(",", $ya) . ");\n";
    $res .= "
      f.lm <- lm(y ~ x)
      st <- summary(f.lm)
      f <- st\$fstatistic
      p <- pf(f[1], f[2], f[3], lower=FALSE)
      pf <- format.pval(p)
      cf <- coef(f.lm)
      r <- cor(x, y)
      cat(\"BEGIN\\n\")
      cat(sprintf(\"R^2 = %.3f\\n\", st\$r.squared))
      cat(sprintf(\"n = %d\\n\", length(x)))
      cat(sprintf(\"a0 = %f\\n\", cf[1]))
      cat(sprintf(\"a1 = %f\\n\", cf[2]))
      cat(sprintf(\"r = %f\\n\", r))
      cat(sprintf(\"pvalue = %s\\n\", pf))
      cat(sprintf(\"m1 = %f\\n\", mean(x)))
      cat(sprintf(\"sd1 = %f\\n\", sd(x)))
      cat(sprintf(\"m2 = %f\\n\", mean(y)))
      cat(sprintf(\"sd2 = %f\\n\", sd(y)))
      cat(\"box1 =\", quantile(x, probs=c(0, 0.25, 0.5, 0.75, 1)), \"\\n\")
      cat(\"box2 =\", quantile(y, probs=c(0, 0.25, 0.5, 0.75, 1)), \"\\n\")
      cat(\"END\\n\")
      ";
    fwrite($fp, $res);
    fclose($fp);
    if (($fp = fopen("$outprefix.sh", "w")) === FALSE) {
      echo "Can't open file tmp.sh <br>";
    }
    fwrite($fp, "/usr/bin/R --slave < $outprefix.R\n");
    fclose($fp);

    $rhash = array();
    $cmd = "bash $outprefix.sh";
    if ( ($fh = popen($cmd, 'r')) === false )
      die("Open failed: ${php_errormsg}\n");
    while (!feof($fh))
    {
      $line = fgets($fh);
      $line = chop($line, "\r\n");
      $arr = explode("=", $line);
      if (count($arr) == 2) {
        $rhash[$arr[0]] = $arr[1];
      }
    }
    pclose($fh);
    self::cleanup($outprefix);
    return $rhash;
  }

  function cleanup($prefix) {
    if (file_exists("$prefix.data")) {
      unlink("$prefix.data");
    }
    if (file_exists("$prefix.py")) {
      unlink("$prefix.py");
    }
    if (file_exists("$prefix.png")) {
      unlink("$prefix.png");
    }
    if (file_exists("$prefix.plot")) {
      unlink("$prefix.plot");
    }
    if (file_exists("$prefix.jpg")) {
      unlink("$prefix.jpg");
    }
    if (file_exists("$prefix.sh")) {
      unlink("$prefix.sh");
    }
    if (file_exists("$prefix.R")) {
      unlink("$prefix.R");
    }
  }

  function my_fseek($fp, $x, $debug) {
    fseek($fp, $x);
    $pos = ftell($fp);
    if ($pos != $x) {
      $off = $x - $pos;
      if ($off > 0) {
        $chunk = 1000000000;
        $num = (int) ($off / $chunk);
        if ($debug == 1) {
          echo "Num : $num <br/>\n";
        }
        for ($i = 0; $i < $num; $i++) {
          fseek($fp, $chunk, SEEK_CUR);
        }
        $off = $off - $num * $chunk;
        fseek($fp, $off, SEEK_CUR);
      }
      else {
        echo "Error in seek $x < $pos\n";
      }
    }
  }

  function getXandY($file, $x, $y, $debug) {
    if (($fp = fopen($file, "r")) === FALSE) {
      echo "Can't open file $file <br>";
      exit;
    }
    /*
       $in_x = fgets($fp, 5000);
       $in_x = fgets($fp, 5000);
       echo "Line 3:", ftell($fp), "<br>";
       $in_x = fgets($fp, 10000);
       echo "Line 4:", ftell($fp), "<br>";
       $in_x = fgets($fp, 10000);
     */
    $header = fgets($fp);
    $header = chop($header, "\r\n");
    self::my_fseek($fp, $x, $debug);
    $in_x = fgets($fp);
    $in_x = chop($in_x, "\r\n");
    self::my_fseek($fp, $y, $debug);
    $in_y = fgets($fp);
    $in_y = chop($in_y, "\r\n");
    if ($debug == 1) {
      echo "Line 1:<br/>",$in_x,":<br>";
      echo "Line 2:<br/>",$in_y,":<br>";
    }
    fclose($fp);
    $x_arr = explode("\t", $in_x);
    $y_arr = explode("\t", $in_y);
    $h_arr = explode("\t", $header);

    return array($x_arr, $y_arr, $h_arr);
  }

  function getH($file, $debug) {
    if (($fp = fopen($file, "r")) === FALSE) {
      echo "Can't open file $file <br>";
      exit;
    }
    $header = fgets($fp);
    $header = chop($header, "\r\n");
    if ($debug == 1) {
      echo "Line 1:<br/>",$header,":<br>";
    }
    fclose($fp);
    $h_arr = explode("\t", $header);

    return $h_arr;
  }

  function getX($file, $x, $debug) {
    if (($fp = fopen($file, "r")) === FALSE) {
      echo "Can't open file $file <br>";
      exit;
    }
    $header = fgets($fp);
    $header = chop($header, "\r\n");
    self::my_fseek($fp, $x, $debug);
    $in_x = fgets($fp);
    $in_x = chop($in_x, "\r\n");
    if ($debug == 1) {
      echo "Line 1:<br/>",$in_x,":<br>";
    }
    fclose($fp);
    $x_arr = explode("\t", $in_x);
    $h_arr = explode("\t", $header);

    return array($x_arr, $h_arr);
  }

  function getMinMax($arr, $start, $len) {
    $min = 20000;
    $max = 0;
    for($i=$start; $i < ($start+$len); $i++){       
      if (ereg("^\s*$", $arr[$i])) {
        continue;
      }
      if ($min > $arr[$i]) {
        $min = $arr[$i];
      }
      if ($max < $arr[$i]) {
        $max = $arr[$i];
      }
    }
    return array($min, $max);
  }

function writeMatAbsolute($fp, $x_id, $x_name, $y_id, $y_name, 
    $minx, $maxx, $miny, $maxy, $outprefix) {
  fwrite($fp, "
import matplotlib
matplotlib.use('agg')
import re
from pylab import *
    
data = {}
f = open('$outprefix.data', 'r')
for line in f:
    t = line.split(' ');
    t[2] = t[2].strip();
    if (t[2] not in data):
      data[t[2]] = [[], []]
    data[t[2]][0] += [float(t[0])];
    data[t[2]][1] += [float(t[1])];
fig = figure(figsize=(6.4,4.8))
ax = fig.add_axes([70.0/640, 54.0/480, 1-2*70.0/640, 1-2*54.0/480])
colors = data.keys()
colors.sort(key=lambda t:-len(data[t][0]))
for c in colors:
    ax.plot(data[c][0],data[c][1], color=c, ls='None', marker='+', mew=1.1, ms=4, mec=c)
ax.axis([$minx, $maxx, $miny, $maxy])
ax.set_xlabel('$x_id:  $x_name', fontsize=10)
ax.set_ylabel('$y_id:  $y_name', fontsize=10)
fig.savefig('$outprefix.png', dpi=100)
");
}

  function getColor($x) {
    global $colors;
    return $colors[$x % count($colors)];
  }

  function setupMatData($x_arr, $y_arr, $p_arr, $outprefix) {
    $num = count($x_arr);
    if (($fp = fopen("$outprefix.data", "w")) === FALSE) {
      echo "Can't open file tmp.data <br>";
    }
    for($i=2; $i < $num; $i++){       
      if (ereg("^\s*$", $x_arr[$i])) {
        continue;
      }
      if (ereg("^\s*$", $y_arr[$i])) {
        continue;
      }
      $str = $x_arr[$i]." ".$y_arr[$i]." ".self::getColor($p_arr[$i])."\n";
      fwrite($fp, $str);
    }
    fclose($fp);
  }

  function readSurvival($file) {
    $timehash = array();
    $statushash = array();
    if (!$file) {
      return array($timehash, $statushash);
    }
    if (($fp = fopen($file, "r")) === FALSE) {
      echo "Can't open file $file <br>";
      exit;
    }
    $line = fgets($fp);
    while (!feof($fp))
    {
      $line = fgets($fp);
      $line = chop($line, "\r\n");
      $items = split("\t", $line);
      if (count($items) < 3) {
        continue;
      }
      list($id, $time, $status) = array($items[0], $items[1], $items[2]);
      $time = preg_replace('/\s/i', '', $time);
      $status = preg_replace('/\s/i', '', $status);
      if ($time != '' && $status != '') {
        $timehash[$id] = $time;
        $statushash[$id] = $status;
      }
    }
    fclose($fp);
    return array($timehash, $statushash);
  }

  function getPArray($sfile, $h_arr, $groups) {
    global $colors;
    $hashTable = self::readSurvival($sfile);
    $p_arr = array();
    $a_hash = array();
    for ($i = 0; $i < count($h_arr); $i++) {
      $a_hash[$h_arr[$i]] = $i;
      if (array_key_exists($h_arr[$i], $hashTable[0])) {
        array_push($p_arr, 0);
      }
      else {
        array_push($p_arr, 1);
      }
    }
    if ($groups) {
      $list = split(";", $groups);
      foreach ($list as $g) {
        if ($g != '') {
          list($i, $nm, $v) = split("=", $g, 3);
          $nmps = split(",", $nm);
          if (count($nmps) > 1) {
            $colors[($i+2) % count($colors)] = $nmps[1];
            $nm = $nmps[0];
          }
          foreach (split(":", $v) as $a) {
            $p_arr[$a_hash[$a]] = $i + 2;
          }
        }
      }
    }
    return $p_arr;
  }

  function generateMatPlot($file, $sfile, $x, $y, $x_name, $y_name, $groups, 
      $debug, $outprefix) {

    list($x_arr, $y_arr, $h_arr) = self::getXandY($file, $x, $y, $debug);
    $x_id = $x_arr[0];
    $y_id = $y_arr[0];
    list($x_min, $x_max) = self::getMinMax($x_arr, 2, count($x_arr)-2);
    list($y_min, $y_max) = self::getMinMax($y_arr, 2, count($y_arr)-2);
    $p_arr = self::getPArray($sfile, $h_arr, $groups);
    if ($debug == 1) {
      echo "x:", $x, "<br>";
      echo "y:", $y, "<br>";
      echo "P Arr:<br/>";
      foreach ($p_arr as $p) {
        echo "$p,";
      }
      echo "<br/>";
      echo "Name x:", $x_name, "<br>";
      echo "Name y:", $y_name, "<br>";
      echo "Num :", count($x_arr), "<br>\n";
      echo "Min-Max x:", "($x_min, $x_max) <br>\n";
      echo "Min-Max y:", "($y_min, $y_max) <br>\n";
    }

    $outfile = "$outprefix.py";

    self::setupMatData($x_arr, $y_arr, $p_arr, $outprefix);

    if (($fp = fopen($outfile, "w")) === FALSE) {
      echo "Can't open file $outfile <br>";
    }
    self::writeMatAbsolute($fp, $x_id, $x_name, $y_id, $y_name, 
        $x_min - 0.5 , $x_max + 0.5, $y_min - 0.5, $y_max + 0.5, $outprefix);
    fclose($fp);

    $cmd = "HOME=tmpdir /usr/bin/python $outfile";

    if ( ($fh = popen($cmd, 'w')) === false )
      die("Open failed: ${php_errormsg}\n");
    pclose($fh);
    // echo "Done <br>\n";
  }

function getThrData($arr, $start = null, $len = null) {
  if (!isset($start)) { $start = 0; }
  if (!isset($len)) { $len = count($arr); }
  $s_thr = U::getStepMinerThr($arr, $start, $start+$len-1);
  return array($s_thr[6], $s_thr[3], $s_thr[6]-0.5, $s_thr[6]+0.5);
}

function getThreshold($arr, $start = null, $len = null) {
  if (!isset($start)) { $start = 0; }
  if (!isset($len)) { $len = count($arr); }
  $s_thr = U::getStepMinerThr($arr, $start, $start+$len-1);
  $max = U::max($arr, $start, $start+$len-1);
  $min = U::min($arr, $start, $start+$len-1);
  return array($min, $s_thr[6], $max);
}

function countQs($x_arr, $y_arr, $thrx0, $thrx2, $thry0, $thry2) {
  $res = [0, 0, 0, 0, 0];
  $num = count($x_arr);
  for ($i = 2; $i < $num; $i++) {
    if (ereg("^\s*$", $x_arr[$i])) {
      continue;
    }
    if (ereg("^\s*$", $y_arr[$i])) {
      continue;
    }
    if ($x_arr[$i] < $thrx0  && $y_arr[$i] < $thry0) { $res[0] ++; }
    if ($x_arr[$i] < $thrx0  && $y_arr[$i] >= $thry2) { $res[1] ++; }
    if ($x_arr[$i] >= $thrx2 && $y_arr[$i] < $thry0) { $res[2] ++; }
    if ($x_arr[$i] >= $thrx2 && $y_arr[$i] >= $thry2) { $res[3] ++; }
    $res[4] ++;
  }
  return $res;
}

function convertBooleanStats($c0, $c1, $c2, $c3) {
   $total = $c0 + $c1 + $c2 + $c3;
   if ($total <= 0) {
     return [[0,0,0,0], [0,0,0,0], [0,0,0,0]];
   }
   $e0 = ($c0 + $c1) * ($c0 + $c2) /$total;
   $e1 = ($c1 + $c0) * ($c1 + $c3) /$total;
   $e2 = ($c2 + $c0) * ($c2 + $c3) /$total;
   $e3 = ($c3 + $c1) * ($c3 + $c2) /$total;
   $s0 = (intval($e0) + 1 - $c0)/sqrt(intval($e0) + 1);
   $s1 = (intval($e1) + 1 - $c1)/sqrt(intval($e1) + 1);
   $s2 = (intval($e2) + 1 - $c2)/sqrt(intval($e2) + 1);
   $s3 = (intval($e3) + 1 - $c3)/sqrt(intval($e3) + 1);
   $p0 = ($c0/($c0 + $c1 + 1) + $c0/($c0 + $c2 + 1))/2;
   $p1 = ($c1/($c1 + $c0 + 1) + $c1/($c1 + $c3 + 1))/2;
   $p2 = ($c2/($c2 + $c0 + 1) + $c2/($c2 + $c3 + 1))/2;
   $p3 = ($c3/($c3 + $c1 + 1) + $c3/($c3 + $c2 + 1))/2;
   return [[$e0, $e1, $e2, $e3], [$s0, $s1, $s2, $s3], [$p0, $p1, $p2, $p3]];
}

function getBooleanStats($x_arr, $y_arr, $thrx0, $thrx2, $thry0, $thry2) {
  $res = self::countQs($x_arr, $y_arr, $thrx0, $thrx2, $thry0, $thry2);
  $res1 = self::convertBooleanStats($res[0], $res[1], $res[2], $res[3]);
  return array_merge([$res], $res1);
}

function getBooleanRelations($bs, $sthr, $pthr) {
  $rel = 0;
  for ($i = 0; $i < 4; $i++) {
    if ($bs[2][$i] > $sthr && $bs[3][$i] < $pthr) {
      if ($rel == 0) { $rel = $i + 1; }
      if ($rel == 2 && $i == 2) {
        $rel = 5;
      }
      if ($rel == 1 && $i == 3) {
        $rel = 6;
      }
    }
  }
  return $rel;
}

function getFormula($xn, $yn, $rel) {
  if ($rel == 1) { return "$xn low =&gt; $yn high"; }
  if ($rel == 2) { return "$xn low =&gt; $yn low"; }
  if ($rel == 3) { return "$xn high =&gt; $yn high"; }
  if ($rel == 4) { return "$xn high =&gt; $yn low"; }
  if ($rel == 5) { return "$xn equivalent $yn"; }
  if ($rel == 6) { return "$xn opposite $yn"; }
  return "No relation";
}

function getSurvivalScript($outprefix, $sfile, $ct, $groups) {
  global $colors;
  $hashTable = self::readSurvival($sfile);
  $list = split(";", $groups);
  $parameters = split(",", $ct); 
  $ct = $parameters[0];
  $xlimit = "";
  if (count($parameters) > 1) {
    $xlimit = ", xlim=c(0, ".$parameters[1].")";
  }
  $surv = array();
  $status = array();
  $group = array();
  $names = array();
  $clrs = array();
  foreach ($list as $g) {
    if ($g != '') {
      list($i, $nm, $v) = split("=", $g, 3);
      $nmps = split(",", $nm);
      if (count($nmps) > 1) {
        $colors[($i+2) % count($colors)] = $nmps[1];
        $nm = $nmps[0];
      }
      foreach (split(":", $v) as $a) {
        if (array_key_exists($a, $hashTable[0]) && $hashTable[1][$a] != "") {
          if ($ct != "" && $hashTable[0][$a] > $ct) {
            array_push($surv, $ct);
            array_push($status, 0);
          }
          else {
            array_push($surv, $hashTable[0][$a]);
            array_push($status, $hashTable[1][$a]);
          }
          array_push($group, $i);
          $names[$i] = $nm;
          $clrs[$i] = self::getColor($i+2);
        }
      }
    }
  }
  $res = "
png(filename=\"$outprefix.png\", width=640, height=480, pointsize=15)
library(survival)
";
  $res .= "surv <- c(" . join(",", $surv) . ");\n";
  $res .= "status <- c(" . join(",", $status) . ");\n";
  $res .= "group <- c(" . join(",", $group) . ");\n";
  $res .= "nm <- c(\"" . implode("\",\"", $names) . "\");\n";
  $res .= "colors <- c(\"" . implode("\",\"", $clrs) . "\");\n";
  $res .= "
d <- data.frame(surv, status, group)
s <- survfit(Surv(surv, status) ~ group, data = d)
l <- names(s\$strata)
x <- (max(s\$time) - min(s\$time)) * 0.7
par(mar=c(3, 3, 3, 3))
plot(s, lty = 2:(length(l)+1), col=colors, lwd=3$xlimit)
st <- survdiff(Surv(surv, status) ~ group, data = d)
p <- 1 - pchisq(st\$chisq, length(l)-1)
nm <- paste(nm, \"(\", st\$obs, \"/\", st\$n, \")\", 
    sprintf(rep(\"%.2f%%\", length(nm)), s\$surv[cumsum(s\$strata)]*100))
legend(\"topright\", nm, lty = 2:(length(l)+1), col=colors, lwd=3, inset=0.02)
legend(\"topright\", sprintf(\"pvalue = %.4f\", p), inset=c(0.02, length(l)*0.042+0.12))
";
 return $res;
}

}

?>
