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
      $end = count($arrayref) - 1;
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
      $end = count($arrayref) - 1;
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
      $end = count($arrayref) - 1;
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

  public function getXstats($x_arr, $start = 2) {
    $better_token = md5(uniqid(rand(), true));
    $outprefix = "tmpdir/tmp$better_token";
    if (($fp = fopen("$outprefix.R", "w")) === FALSE) {
      echo "Can't open file tmp.R <br>";
    }
    $num = count($x_arr);
    $xa = array();
    for($i=$start; $i < $num; $i++){       
      if (preg_match('/^\s*$/', $x_arr[$i])) {
        continue;
      }
      array_push($xa, $x_arr[$i]);
    }
    if (count($xa) <= 1) {
      $rhash = array("n" => count($xa), "m" => "", "sd" => "", "box" => "");
      if (count($xa) == 1) { 
        $rhash["m1"] = $xa[0];
      }
      return $rhash;
    }
    $res = "x <- c(" . join(",", $xa) . ");\n";
    $res .= "
      cat(\"BEGIN\\n\")
      cat(sprintf(\"n=%d\\n\", length(x)))
      cat(sprintf(\"m=%.3f\\n\", mean(x)))
      cat(sprintf(\"sd=%.3f\\n\", sd(x)))
      cat(\"box=\", quantile(x, probs=c(0, 0.25, 0.5, 0.75, 1)), \"\\n\")
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

  public function getXYstats($x_arr, $y_arr, $start = 2) {
    $better_token = md5(uniqid(rand(), true));
    $outprefix = "tmpdir/tmp$better_token";
    if (($fp = fopen("$outprefix.R", "w")) === FALSE) {
      echo "Can't open file tmp.R <br>";
    }
    $num = count($x_arr);
    $xa = array();
    $ya = array();
    for($i=$start; $i < $num; $i++){       
      if (preg_match('/^\s*$/', $x_arr[$i])) {
        continue;
      }
      if (preg_match('/^\s*$/', $y_arr[$i])) {
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

  function cleanupfile($file) {
    if (file_exists("$file")) {
      unlink("$file");
    }
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
      if (preg_match('/^\s*$/', $arr[$i])) {
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

  function setupIDListData($ids, $outprefix) {
    if (($fp = fopen("$outprefix.data", "w")) === FALSE) {
      echo "Can't open file tmp.data <br>";
    }
    foreach ($ids as $id => $n) {
      fwrite($fp, "$id\t$n\n");
    }
    fclose($fp);
  }

  function setupArrayListData($groups, $outprefix) {
    if (($fp = fopen("$outprefix.data", "w")) === FALSE) {
      echo "Can't open file tmp.data <br>";
    }
    if ($groups) {
      $list = explode(";", $groups);
      foreach ($list as $g) {
        if ($g != '') {
          list($i, $nm, $v) = explode("=", $g, 3);
          $nmps = explode(",", $nm);
          if (count($nmps) > 1) {
            $nm = $nmps[0];
          }
          foreach (explode(":", $v) as $a) {
            $str = join("\t", [$a, $i, $nm])."\n";
            fwrite($fp, $str);
          }
        }
      }
    }
    fclose($fp);
  }

  function setupMatData($x_arr, $y_arr, $p_arr, $outprefix, $param) {
    $num = count($x_arr);
    if (($fp = fopen("$outprefix.data", "w")) === FALSE) {
      echo "Can't open file tmp.data <br>";
    }
    for($i=2; $i < $num; $i++){       
      if (preg_match('/^\s*$/', $x_arr[$i])) {
        continue;
      }
      if (preg_match('/^\s*$/', $y_arr[$i])) {
        continue;
      }
      $c = self::getColor($p_arr[$i]);
      if (array_key_exists("color", $param)) {
        $c = $param["color"];
      }
      $str = $x_arr[$i]." ".$y_arr[$i]." $c\n";
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
      $items = explode("\t", $line);
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
      $list = explode(";", $groups);
      foreach ($list as $g) {
        if ($g != '') {
          list($i, $nm, $v) = explode("=", $g, 3);
          $nmps = explode(",", $nm);
          if (count($nmps) > 1) {
            $colors[($i+2) % count($colors)] = $nmps[1];
            $nm = $nmps[0];
          }
          foreach (explode(":", $v) as $a) {
            $p_arr[$a_hash[$a]] = $i + 2;
          }
        }
      }
    }
    return $p_arr;
  }

  function generateMatPlot($file, $sfile, $x, $y, $x_name, $y_name, $groups, 
      $debug, $outprefix, $param) {

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

    self::setupMatData($x_arr, $y_arr, $p_arr, $outprefix, $param);

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
    if ($len <= 0) {
      return array("", "", "");
    }
    $s_thr = U::getStepMinerThr($arr, $start, $start+$len-1);
    $max = U::max($arr, $start, $start+$len-1);
    $min = U::min($arr, $start, $start+$len-1);
    return array($min, $s_thr[6], $max);
  }

  function countQs($x_arr, $y_arr, $thrx0, $thrx2, $thry0, $thry2) {
    $res = [0, 0, 0, 0, 0];
    $num = count($x_arr);
    for ($i = 2; $i < $num; $i++) {
      if (preg_match('/^\s*$/', $x_arr[$i])) {
        continue;
      }
      if (preg_match('/^\s*$/', $y_arr[$i])) {
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
    $list = explode(";", $groups);
    $parameters = explode(",", $ct); 
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
        list($i, $nm, $v) = explode("=", $g, 3);
        $nmps = explode(",", $nm);
        if (count($nmps) > 1) {
          $colors[($i+2) % count($colors)] = $nmps[1];
          $nm = $nmps[0];
        }
        foreach (explode(":", $v) as $a) {
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

  function getSurvivalData($outprefix, $sfile, $ct, $groups) {
    global $colors;
    $hashTable = self::readSurvival($sfile);
    $list = explode(";", $groups);
    $ct = str_replace(" ", "", $ct);
    $parameters = explode(",", $ct); 
    $ct = $parameters[0];
    $maxt = "";
    if (count($parameters) > 1) {
      $maxt = $parameters[1];
    }
    $surv = array();
    $status = array();
    $group = array();
    $names = array();
    $clrs = array();
    foreach ($list as $g) {
      if ($g != '') {
        list($i, $nm, $v) = explode("=", $g, 3);
        $nmps = explode(",", $nm);
        if (count($nmps) > 1) {
          $colors[($i+2) % count($colors)] = $nmps[1];
          $nm = $nmps[0];
        }
        foreach (explode(":", $v) as $a) {
          if (array_key_exists($a, $hashTable[0]) && $hashTable[1][$a] != "") {
            if ($ct != "" && $hashTable[0][$a] > $ct) {
              array_push($surv, $ct);
              array_push($status, 0);
            }
            else {
              array_push($surv, $hashTable[0][$a]);
              array_push($status, $hashTable[1][$a]);
            }
            array_push($group, $i + 1);
            $names[$i] = $nm;
            $clrs[$i] = self::getColor($i+2);
          }
        }
      }
    }
    if (count($surv) < 1 || count($group) <= 0) {
      return array();
    }
    $res = "
pdf(file=\"$outprefix-2.pdf\", width=6.4, height=4.8, pointsize=11)
library(survival)
";
    $res .= "surv <- c(" . join(",", $surv) . ");\n";
    $res .= "status <- c(" . join(",", $status) . ");\n";
    $res .= "groups <- c(" . join(",", $group) . ");\n";
    $res .= "nm <- c(\"" . implode("\",\"", $names) . "\");\n";
    $res .= "clr <- c(\"" . implode("\",\"", $clrs) . "\");\n";
    if ($maxt != "") {
      $res .= "maxt <- $maxt\n";
    }
    else {
      $res .= "maxt <- max(surv)\n";
    }
    $res .= "
idx <- !is.na(groups)
surv <- surv[idx]
status <- status[idx]
groups <- groups[idx]

d <- data.frame(surv, status, groups)
s <- survfit(Surv(surv, status) ~ groups, data = d)
l <- names(s\$strata)
x <- (max(s\$time) - min(s\$time)) * 0.7
st <- survdiff(Surv(surv, status) ~ groups, data = d)
g <- as.numeric(gsub(\"groups=\", \"\", names(st\$n)))
p <- 1 - pchisq(st\$chisq, length(l)-1)
nm <- paste(nm, \"(\", st\$obs, \"/\", st\$n, \")\",
    sprintf(rep(\"%.2f%%\", length(st\$n)), s\$surv[cumsum(s\$strata)]*100))
plot(s, col=clr, lwd=3, xlim=c(0,maxt), xaxt=\"n\")
t.ticks <- axTicks(1)
s.ticks <- axTicks(2)
axis(1, t.ticks, labels=t.ticks)
legend(\"topright\", nm, lty = 2:(length(l)+1), col=clr, lwd=3, inset=0.02)
legend(\"topright\", sprintf(\"pvalue = %.4f\", p), inset=c(0.02, length(l)*0.042+0.12))
dev.off()
cat(\"pvalue\", p, length(l), \"\\n\", sep=\"\\t\")
cat(\"groups\", nm, \"\\n\", sep=\"\\t\")
cat(\"colors\", clr, \"\\n\", sep=\"\\t\")
cat(\"strata\", s\$strata, \"\\n\", sep=\"\\t\")
cat(\"time\", s\$time, \"\\n\", sep=\"\\t\")
cat(\"n.risk\", s\$n.risk, \"\\n\", sep=\"\\t\")
cat(\"n.event\", s\$n.event, \"\\n\", sep=\"\\t\")
cat(\"n.censor\", s\$n.censor, \"\\n\", sep=\"\\t\")
cat(\"surv\", s\$surv, \"\\n\", sep=\"\\t\")
cat(\"t.ticks\", t.ticks, \"\\n\", sep=\"\\t\")
cat(\"s.ticks\", s.ticks, \"\\n\", sep=\"\\t\")

x <- coxph(Surv(surv, status) ~ groups, data=d)
s <- summary(x)
codes <- c(\"0\", \"***\", \"**\", \"*\", \".\", \" \")
p <- s\$coefficients[,5]
pg <- as.numeric(p <= 0) + 2 * as.numeric(p > 0 & p <= 0.001) +
    3 * as.numeric(p > 0.001 & p <= 0.01) +
    4 * as.numeric(p > 0.01 & p <= 0.05) +
    5 * as.numeric(p > 0.05 & p <= 0.1) +
    6 * as.numeric(p > 0.1)
cat(\"names\", rownames(s\$conf.int), \"\\n\", sep=\"\\t\")
cat(\"exp(coef)\", s\$conf.int[1], \"\\n\", sep=\"\\t\")
cat(\"lower .95\", s\$conf.int[3], \"\\n\", sep=\"\\t\")
cat(\"upper .95\", s\$conf.int[4], \"\\n\", sep=\"\\t\")
cat(\"cp\", p, \"\\n\", sep=\"\\t\")
cat(\"c\", codes[pg], \"\\n\", sep=\"\\t\")
cat(\"end\", \"\\n\",  sep=\"\\t\")
";
    if (($fp = fopen("$outprefix.R", "w")) === FALSE) {
      echo "Can't open file tmp.R <br>";
    }
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
      $list = explode("\t", $line, 2);
      if (count($list) > 0 && strcmp($list[0], "end") == 0) {
        break;
      }
      if (count($list) > 1) {
        $rhash[$list[0]] = $list[1];
      }
    }
    pclose($fh);
    //echo "<pre>";
    //print_r($res);
    //print_r($rhash);
    //echo "</pre>";
    return $rhash;
    
  }

  function data_uri($file, $mime) 
  {
    $contents = file_get_contents($file);
    $base64   = base64_encode($contents); 
    return ('data:' . $mime . ';base64,' . $base64);
  }

  public function plotHistogram($x_arr, $start = 2, $params = null) {
    $breaks = "\"Sturges\"";
    if ($params && array_key_exists("breaks", $params)) {
      $breaks = $params["breaks"];
    }
    $better_token = md5(uniqid(rand(), true));
    $outprefix = "tmpdir/tmp$better_token";
    if (($fp = fopen("$outprefix.R", "w")) === FALSE) {
      echo "Can't open file tmp.R <br>";
    }
    $num = count($x_arr);
    $xa = array();
    for($i=$start; $i < $num; $i++){       
      if (preg_match('/^\s*$/', $x_arr[$i])) {
        continue;
      }
      array_push($xa, $x_arr[$i]);
    }
    if (count($xa) <= 0) {
      return null;
    }
    $res = "x <- c(" . join(",", $xa) . ");\n";
    $res .= "
png(filename=\"$outprefix.png\", width=640, height=480, pointsize=15)
h <- hist(x, freq=TRUE, main=\"Histogram\", xlab=\"value\", 
    ylab=\"count\", breaks=$breaks)
n <- length(h\$counts)
plot(h\$breaks[1:n], h\$counts, type=\"h\",
    main=\"Histogram\", xlab=\"value\",
    ylab=\"count\")
lines(h\$breaks[1:n], h\$counts, type=\"o\",
    main=\"Histogram\", xlab=\"value\",
    ylab=\"count\")
dev.off()
cat(\"BEGIN\\n\")
cat(h\$breaks, \"\\n\")
cat(h\$counts, \"\\n\")
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
      if (strcmp($line, "BEGIN") == 0) {
        $line = fgets($fh);
        $line = chop($line, "\r\n");
        $rhash["breaks"] = $line;
        $line = fgets($fh);
        $line = chop($line, "\r\n");
        $rhash["counts"] = $line;
        break;
      }
    }
    pclose($fh);
    if (file_exists("$outprefix.png")) {
      $rhash["img"] = self::data_uri("$outprefix.png", "image/png");
    }
    self::cleanup($outprefix);
    return $rhash;
  }

  /**
   * Series of substitutions to sanitise text for use in LaTeX.
   *
   * http://stackoverflow.com/questions/2627135/how-do-i-sanitize-latex-input
   * Target document should \usepackage{textcomp}
   */
  public static function myescape($text) {
    // Prepare backslash/newline handling
    $text = str_replace("\n", "\\\\", $text); // Rescue newlines
    $text = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $text); // Strip all non-printables
    $text = str_replace("\\\\", "\n", $text); // Re-insert newlines and clear \\
    $text = str_replace("\\", "\\\\", $text); // Use double-backslash to signal a backslash in the input (escaped in the final step).

    // Symbols which are used in LaTeX syntax
    $text = str_replace("{", "\\{", $text);
    $text = str_replace("}", "\\}", $text);
    $text = str_replace("$", "\\$", $text);
    $text = str_replace("&", "\\&", $text);
    $text = str_replace("#", "\\#", $text);
    $text = str_replace("^", "\\textasciicircum{}", $text);
    $text = str_replace("_", "\\_", $text);
    $text = str_replace("~", "\\textasciitilde{}", $text);
    $text = str_replace("%", "\\%", $text);

    // Brackets & pipes
    $text = str_replace("<", "\\textless{}", $text);
    $text = str_replace(">", "\\textgreater{}", $text);
    $text = str_replace("|", "\\textbar{}", $text);

    // Quotes
    $text = str_replace("\"", "\\textquotedbl{}", $text);
    $text = str_replace("'", "\\textquotesingle{}", $text);
    $text = str_replace("`", "\\textasciigrave{}", $text);

    // Clean up backslashes from before
    $text = str_replace("\\\\", "\\textbackslash{}", $text); // Substitute backslashes from first step.
    $text = str_replace("\n", "\\\\", trim($text)); // Replace newlines (trim is in case of leading \\)
    return $text;
  }

  function writeTikzHeader($fp) {
    $res = "
\\documentclass[tikz,border=2mm]{standalone}
\\usepackage{graphicx}
\\usepackage{pgfplots}
\\usepackage{pgfplotstable}
\\pgfplotsset{compat=1.11} 

\\begin{document}
";
    fwrite($fp, $res);
  }

  function writeTikzFooter($fp) {
    $res = "
\\end{document}
";
    fwrite($fp, $res);
  }

  function writeTikzData($fp, $x_arr, $y_arr, $p_arr, $h_arr) {
    global $colors;
    $a_hash = array();
    for ($i = 2; $i < count($h_arr); $i++) {
      if (!array_key_exists($p_arr[$i], $a_hash)) {
        $a_hash[$p_arr[$i]] = array();
      }
      array_push($a_hash[$p_arr[$i]], $i);
    }
    foreach ($a_hash as $g => $v) {
      $c = chr(ord('a')+$g);
      $clr = self::getPScolor("clr$g", $colors[$g % count($colors)]);
      fwrite($fp, "$clr\n");
      fwrite($fp, "\\pgfplotstableread{%\n");
      foreach ($v as $i) {
        if (preg_match('/^\s*$/', $x_arr[$i])) {
          continue;
        }
        if (preg_match('/^\s*$/', $y_arr[$i])) {
          continue;
        }
        fwrite($fp, $x_arr[$i]." ".$y_arr[$i]."\n");
      }
      fwrite($fp, "}\\g$c"."data%\n");
    }
    krsort($a_hash);
    return $a_hash;
  }

  function getPScolor($n, $c) {
    if (strncmp($c, "#", 1) == 0) {
      list($r, $g, $b) = sscanf($c, "#%02x%02x%02x");
      return "\\definecolor{".$n."}{RGB}{"."$r, $g, $b"."}";
    }
    return "\\definecolor{".$n."}{named}{".$c."}";
  }

  function writeTikzPair($fp, $a_hash, $x_id, $x_name, $y_id, $y_name, 
    $minx, $maxx, $miny, $maxy) {
    $lmaxx = ($maxx - $minx);
    $lmaxy = ($maxy - $miny);
    if ($lmaxx <= 0) { $lmaxx = 1; }
    if ($lmaxy <= 0) { $lmaxy = 1; }
    $xunit = (640 - 2 * 70)/$lmaxx/100;
    $yunit = (480 - 2 * 54)/$lmaxy/100;
    $ox = 70/100/$xunit;
    $oy = 54/100/$yunit;
    $tx = (640 - 70)/100/$xunit;
    $ty = (480 - 54)/100/$yunit;
    $xl = self::myescape("$x_id: $x_name");
    $yl = self::myescape("$y_id: $y_name");
    $str = "
\\def\\ttsize{\\tiny}%
\\def\\ttsizea{\\fontsize{9pt}{9pt}\\selectfont}%
\\def\\ttsizeb{\\fontsize{8pt}{8pt}\\selectfont}%
\\def\\ttsizec{\\fontsize{7pt}{7pt}\\selectfont}%
\\def\\pshlabel#1{\\ttsize #1}%
\\def\\psvlabel#1{\\ttsize #1}%
\\begin{tikzpicture}[x=$xunit in, y=$yunit in]
\\begin{axis}[x=$xunit in, y=$yunit in,
  axis x line = bottom,axis y line = left,
  ticklabel style={font=\\tiny},
  ymin=$miny, ymax=$maxy, xmin=$minx, xmax=$maxx,
  xlabel={\\bf \\ttsizeb $xl},
  ylabel={\\bf \\ttsizea $yl},
  xlabel style={below,anchor=north},
  ylabel style={left,anchor=south}]
\\draw[black,line width=0.75pt] ($minx, $miny) rectangle ($maxx, $maxy);
";
    fwrite($fp, $str);
    foreach ($a_hash as $g => $v) {
      $c = chr(ord('a')+$g);
      $dname = "\\g".$c."data";
      fwrite($fp, "\\addplot+[line width=1.3pt, color=clr$g, mark=*, only marks,mark options={color=clr$g}]  table [x index=0, y index=1] {$dname};\n");
    }
    fwrite($fp, "\\end{axis}\n");
    fwrite($fp, "\\end{tikzpicture}\n");
  }

  function writeTikzPDF($fp, $x_id, $x_name, $y_id, $y_name, $outprefix) {
    $xl = self::myescape("$x_id: $x_name");
    $yl = self::myescape("$y_id: $y_name");
    $str = "
\\begin{tikzpicture}
    \\node[anchor=south west] at (0,0) (a)
            {\\includegraphics[height=2in]{" . $outprefix. "-1.pdf}};
    \\node[anchor=south] at (a.south) {\\textbf{ $xl }};
    \\node[rotate=90,anchor=north] at (a.west) {\\textbf{ $yl }};
\\end{tikzpicture}
";
    fwrite($fp, $str);
  }

  function writeTikzSurvPDF($fp, $outprefix) {
    $str = "
\\begin{tikzpicture}
    \\node[anchor=south west] at (0,0) (a)
            {\\includegraphics[height=2in]{" . $outprefix. "-2.pdf}};
\\end{tikzpicture}
";
    fwrite($fp, $str);
  }

  function writeMatAbsolutePDF($fp, $x_id, $x_name, $y_id, $y_name, 
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
    ax.plot(data[c][0],data[c][1], color=c, ls='None', marker='.', mew=2, ms=2, mec=c)
ax.axis([$minx, $maxx, $miny, $maxy])
fig.savefig('$outprefix-1.pdf', dpi=100)
");
  }

  function writeTikzSurvival($fp, $shash, $outprefix) {
    $times = explode("\t", $shash["t.ticks"], -1);
    $groups = explode("\t", $shash["groups"], -1);
    $colors = explode("\t", $shash["colors"], -1);
    $strata = explode("\t", $shash["strata"], -1);
    $time = explode("\t", $shash["time"], -1);
    $risk = explode("\t", $shash["n.risk"], -1);
    $event = explode("\t", $shash["n.event"], -1);
    $censor = explode("\t", $shash["n.censor"], -1);
    $surv = explode("\t", $shash["surv"], -1);
    $index = 0;
    $gnum = [];
    for ($g = 0; $g < count($strata); $g++) {
      $start = $index;
      $limit = $index + $strata[$g];
      $c = chr(ord('a')+$g);
      $clr = self::getPScolor("clr$g", $colors[$g % count($colors)]);
      fwrite($fp, "$clr\n");
      $gnum[$g] = [1, 0];
      fwrite($fp, "\\pgfplotstableread{%\n");
      $s = 1.0;
      $t = 0;
      fwrite($fp, "$t $s\n");
      for ($i = $start; $i < $limit; $i++) {
        fwrite($fp, $time[$i]." ".$surv[$i]."\n");
        $t = $time[$i];
        $s = $surv[$i];
        $gnum[$g][0] ++;
      }
      fwrite($fp, "}\\g$c"."data%\n");
      $cname = "\\g$c"."censor";
      fwrite($fp, "\\pgfplotstableread{%\n");
      for ($i = $start; $i < $limit; $i++) {
        if ($censor[$i] > 0) {
          fwrite($fp, $time[$i]." ".$surv[$i]."\n");
          $gnum[$g][1] ++;
        }
      }
      fwrite($fp, "}$cname%\n");
      $index = $limit;
    }
    $stimes = $times;
    asort($stimes);
    $maxtime = $stimes[count($stimes) - 1];
    if ($maxtime < self::max($time)) {
      $maxtime = $maxtime + $stimes[1];
      array_push($stimes, $maxtime);
    }
    $xmax = $maxtime * 1.1;
    $xunit = 1.5/$maxtime;
    $yunit = 1.5;
    $middle = $maxtime/2;
    $leftx = 0.3/$xunit;
    $bottom = 0.4/$yunit;
    $l1 = 0.75/$xunit;
    $dx = $stimes[1];
    $rs = 0.08;
    $boty = $bottom + (count($strata)+1) * $rs;
    $pvalue = $shash["pvalue"];
    $pvalue = preg_replace('/\s.*/i', '', $pvalue);
    $pvalue = sprintf("%.2g", $pvalue);
    $xl = "Time";
    $yl = "Survival";
    $res = "
\\def\\ttsize{\\tiny}%
\\def\\ttsizea{\\fontsize{9pt}{9pt}\\selectfont}%
\\def\\ttsizeb{\\fontsize{8pt}{8pt}\\selectfont}%
\\def\\ttsizec{\\fontsize{7pt}{7pt}\\selectfont}%
\\def\\pshlabel#1{\\ttsize #1}%
\\def\\psvlabel#1{\\ttsize #1}%
\\begin{tikzpicture}[x=$xunit in, y=$yunit in]
\\node[anchor=west]  at ($leftx,0){\\bf \\ttsizea p = $pvalue};
\\node[anchor=west] at (-$l1,-$bottom){\\bf \\ttsizec No at risk};
\\draw[black,line width=0.75pt] (0, -0.1) rectangle ($xmax, 1.1);
\\begin{axis}[x=$xunit in, y=$yunit in,
  anchor=origin, axis x line = bottom,axis y line = left,
  ticklabel style={font=\\tiny},
  ymin=-0.1, ymax=1.1, xmin=0, xmax=$xmax,
  xlabel={\\bf \\ttsizeb $xl},
  ylabel={\\bf \\ttsizea $yl},
  xlabel style={below,anchor=north},
  ylabel style={left,anchor=south},
  xtick={0,$dx,...,$maxtime},
  ytick={0,0.2,...,1.0}]
";
    fwrite($fp, $res);
    for ($g = 0; $g < count($strata); $g++) {
      $c = chr(ord('a')+$g);
      $cname = "\\g$c"."censor";
      $dname = "\\g$c"."data";
      $res = "
\\addplot+[line width=1.3pt, color=clr$g, const plot, no marks]  table [x index=0, y index=1] {$dname};
";
      if ($gnum[$g][1] > 0) {
        $res .= "
\\addplot+[line width=0.75pt, color=clr$g, mark=+, only marks]  table [x index=0, y index=1] {$cname};
";
      }
      fwrite($fp, $res);
    }
    fwrite($fp, "\\end{axis}\n");
    $nrisk = [["No. at Risk", $stimes, []]];
    $index = 0;
    for ($g = 0; $g < count($strata); $g++) {
      $start = $index;
      $limit = $index + $strata[$g];
      $gr = $groups[$g];
      $gr = preg_replace('/ \(.*/i', '', $gr);
      $numbers = [];
      $snumbers = [];
      foreach ($stimes as $t) {
        while ($index < $limit && $time[$index] < $t) { $index++; }
        if ($index >= $limit) {
          array_push($numbers, 0);
          array_push($snumbers, $surv[$limit - 1]);
        }
        else {
          array_push($numbers, $risk[$index]);
          array_push($snumbers, $surv[$index]);
        }
      }
      array_push($nrisk, [$gr, $numbers, $snumbers]);
      $index = $limit;
    }
    for ($i = 1; $i < count($nrisk); $i++) {
      $g = $i - 1;
      $y = $bottom + $i * $rs;
      $ly = 1.0 - $g * $rs;
      $gr = $nrisk[$i][0];
      $snumbers = $nrisk[$i][2];
      $pr = sprintf("%.1f", $snumbers[count($snumbers) - 1] * 100)."\%";
      fwrite($fp, "\\node[anchor=east,color=clr$g] at ($maxtime,$ly){\\bf \\ttsizeb $gr ($pr)};\n");
      fwrite($fp, "\\node[anchor=west] at (-$l1,-$y){\\bf \\ttsizec $gr};\n");
    }
    for ($j = 0; $j < count($nrisk[0][1]); $j++) {
      for ($i = 1; $i < count($nrisk); $i++) {
        $y = $bottom + $i * $rs;
        $t = $nrisk[0][1][$j];
        $n = $nrisk[$i][1][$j];
        fwrite($fp, "\\node[anchor=west,inner sep=0pt] at ($t,-$y) {\\bf \\ttsizec $n};\n");
      } 
    }
    fwrite($fp, "\\end{tikzpicture}\n");
  }

  function getThresholdXY($file, $x_arr, $y_arr) {
    $pre = str_replace("-expr.txt", "", $file);
    $x_thr = null;
    $y_thr = null;
    if (file_exists("$pre-thr.txt")) {
      if (($fp = fopen("$pre-thr.txt", "r")) === FALSE) {
        echo "Can't open file $pre-thr.txt <br>";
        exit;
      }
      $line = fgets($fp);
      while (!feof($fp))
      {
        $line = fgets($fp);
        $ll = explode("\t", $line);
        if (count($ll) > 0 && strcmp($ll[0], $x_arr[0]) == 0) {
          $x_thr = $ll[1];
        }
        if (count($ll) > 0 && strcmp($ll[0], $y_arr[0]) == 0) {
          $y_thr = $ll[1];
        }
        if ($x_thr != null && $y_thr != null) {
          break;
        }
      }
      fclose($fp);
    }
    if ($x_thr == null) {
      $thrx = self::getThrData($x_arr, 2, count($x_arr)-2);
      $x_thr = $thrx[0];
    }
    if ($y_thr == null) {
      $thry = self::getThrData($y_arr, 2, count($y_arr)-2);
      $y_thr = $thry[0];
    }
    if ($x_thr == null || $x_thr == "") {
      $x_thr = 0;
    }
    if ($y_thr == null || $y_thr == "") {
      $y_thr = 0;
    }
    return array($x_thr, $y_thr);
  }

  function generateBooleanGroups ($file, $x_arr, $y_arr, $h_arr) {
    list($x_thr, $y_thr) = self::getThresholdXY($file, $x_arr, $y_arr);
    $gr = [[0, "lolo", []], 
      [1, "lohi", []],
      [2, "hilo", []],
      [3, "hihi", []]];
    $num = count($x_arr);
    for($i=2; $i < $num; $i++){       
      if (preg_match('/^\s*$/', $x_arr[$i])) {
        continue;
      }
      if (preg_match('/^\s*$/', $y_arr[$i])) {
        continue;
      }
      if ($x_arr[$i] < $x_thr && $y_arr[$i] < $y_thr) {
        array_push($gr[0][2], $h_arr[$i]);
      }
      if ($x_arr[$i] < $x_thr && $y_arr[$i] >= $y_thr) {
        array_push($gr[1][2], $h_arr[$i]);
      }
      if ($x_arr[$i] >= $x_thr && $y_arr[$i] < $y_thr) {
        array_push($gr[2][2], $h_arr[$i]);
      }
      if ($x_arr[$i] >= $x_thr && $y_arr[$i] >= $y_thr) {
        array_push($gr[3][2], $h_arr[$i]);
      }
    }
    return $gr;
  }

  function convertGroups($gr) {
    $groups = "";
    for ($i = 0; $i < 4; $i++) {
      if (count($gr[$i][2]) > 0) {
        $groups .= "$i=".$gr[$i][1]."=". join(":", $gr[$i][2]) . ";";
      }
    }
    return $groups;
  }

  function convertGroupsArray($groups) {
    $gr = [];
    $list = explode(";", $groups);
    foreach ($list as $g) {
      if ($g != '') {
        list($i, $nm, $v) = explode("=", $g, 3);
        array_push($gr, [$i, $nm, explode(":", $v)]);
      }
    }
    return $gr;
  }

  function joinGroupsArray($groups) {
    $gr = [];
    $list = explode(";", $groups);
    foreach ($list as $g) {
      if ($g != '') {
        list($i, $nm, $v) = explode("=", $g, 3);
        foreach (explode(":", $v) as $arr) {
          $gr[$arr] = 1;
        }
      }
    }
    return $gr;
  }

  function generateTikzPlot($file, $sfile, $x, $y, $x_name, $y_name, $ct, 
    $groups, $debug, $outprefix, $param) {

    list($x_arr, $y_arr, $h_arr) = self::getXandY($file, $x, $y, $debug);
    $boolean = 0;
    $group_array = self::convertGroupsArray($groups);
    if ($groups == "") {
      $group_array = self::generateBooleanGroups($file, $x_arr, $y_arr, $h_arr);
      $groups = self::convertGroups($group_array);
      $boolean = 1;
    }
    $x_id = $x_arr[0];
    $y_id = $y_arr[0];
    list($x_min, $x_max) = self::getMinMax($x_arr, 2, count($x_arr)-2);
    list($y_min, $y_max) = self::getMinMax($y_arr, 2, count($y_arr)-2);
    $p_arr = self::getPArray($sfile, $h_arr, $groups);
    $outfile = "$outprefix.tex";
    if (($fp = fopen($outfile, "w")) === FALSE) {
      echo "Can't open file $outfile <br>";
    }
    self::writeTikzHeader($fp);
    if (0) {
      $a_hash = self::writeTikzData($fp, $x_arr, $y_arr, $p_arr, $h_arr);
      self::writeTikzPair($fp, $a_hash, $x_id, $x_name, $y_id, $y_name, 
        $x_min - 0.5 , $x_max + 0.5, $y_min - 0.5, $y_max + 0.5);
    }
    else {
      $ofile = "$outprefix.py";
      self::setupMatData($x_arr, $y_arr, $p_arr, $outprefix, $param);
      if (($fp1 = fopen($ofile, "w")) === FALSE) {
        echo "Can't open file $ofile <br>";
      }
      self::writeMatAbsolutePDF($fp1, $x_id, $x_name, $y_id, $y_name, 
        $x_min - 0.5 , $x_max + 0.5, $y_min - 0.5, $y_max + 0.5, $outprefix);
      fclose($fp1);
      $cmd = "HOME=tmpdir /usr/bin/python $ofile";
      if ( ($fh = popen($cmd, 'w')) === false )
        die("Open failed: ${php_errormsg}\n");
      pclose($fh);
      self::writeTikzPDF($fp, $x_id, $x_name, $y_id, $y_name, $outprefix);
      $shash = self::getSurvivalData($outprefix, $sfile, $ct, $groups);
      if (array_key_exists("c", $shash) && file_exists("$outprefix-2.pdf")) {
        self::writeTikzSurvPDF($fp, $outprefix);
        self::writeTikzSurvival($fp, $shash, $outprefix);
      }
    }
    fwrite($fp, "\\begin{tikzpicture}\n");
    fwrite($fp, "\\node at (0,0) (a) {\\textbf{Information}};\n");
    fwrite($fp, "\\node[anchor=north, align=left] at (a.south) {\n");
    $source = "";
    if (array_key_exists("source", $param)) {
      $source = $param["source"];
    }
    fwrite($fp, "Source: $source \\\\ \n");
    if ($boolean == 1) {
      fwrite($fp, "Automatic groups based on StepMiner \\\\ \n");
    }
    else {
      fwrite($fp, "User defined groups \\\\ \n");
    }
    fwrite($fp, "\\begin{tabular}{ccc}\n");
    fwrite($fp, "Group ID & Name & Number\\\\\n");
    foreach ($group_array as $g) {
      fwrite($fp, $g[0]." & ".$g[1]." & ".count($g[2])."\\\\\n");
    }
    fwrite($fp, "\\end{tabular}\\\\\n");
    list($x_thr, $y_thr) = self::getThresholdXY($file, $x_arr, $y_arr);
    $pre = str_replace("-expr.txt", "", $file);
    if (file_exists("$pre-thr.txt")) {
      fwrite($fp, "Threshold derived from -thr file\\\\\n");
    }
    fwrite($fp, "\\begin{tabular}{ccc}\n");
    fwrite($fp, "ID & Name & Threshold\\\\\n");
    fwrite($fp, self::myescape($x_id)." & ".self::myescape($x_name).
      " & $x_thr\\\\\n");
    fwrite($fp, self::myescape($y_id)." & ".self::myescape($y_name).
      " & $y_thr\\\\\n");
    fwrite($fp, "\\end{tabular}\\\\\n");
    list($thrx0, $thrx1, $thrx2) = self::getThreshold($x_arr, 2, count($x_arr)-2);
    list($thry0, $thry1, $thry2) = self::getThreshold($y_arr, 2, count($y_arr)-2);
    $rhash = self::getXYStats($x_arr, $y_arr);
    fwrite($fp, "\\begin{tabular}{cl}\n");
    fwrite($fp, "Key & Value \\\\\n");
    fwrite($fp, "MinX & $thrx0 \\\\\n");
    fwrite($fp, "SThrX & $thrx1 \\\\\n");
    fwrite($fp, "MaxX & $thrx2 \\\\\n");
    fwrite($fp, "MinY & $thry0 \\\\\n");
    fwrite($fp, "SThrY & $thry1 \\\\\n");
    fwrite($fp, "MaxY & $thry2 \\\\\n");
    foreach ($rhash as $k => $v) {
      fwrite($fp, self::myescape($k)." & ".self::myescape($v). " \\\\\n");
    }
    fwrite($fp, "\\end{tabular}\\\\\n");
    fwrite($fp, "};\n");
    fwrite($fp, "\\end{tikzpicture}\n");
    self::writeTikzFooter($fp);

    fclose($fp);
    $cmd = "pdflatex -output-directory tmpdir/ $outfile";
    if ( ($fh = popen($cmd, 'w')) === false )
      die("Open failed: ${php_errormsg}\n");
    pclose($fh);
    self::cleanupfile("$outprefix.log");
    self::cleanupfile("$outprefix.aux");
    self::cleanupfile("$outprefix.tex");
    self::cleanupfile("$outprefix-1.pdf");
    self::cleanupfile("$outprefix-2.pdf");
  }

}

?>
