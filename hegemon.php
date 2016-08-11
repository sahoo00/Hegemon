<?php

class Dataset {
  public $id;
  public $index;
  public $hash;

  public function init($n_id) {
    $this->id = $n_id;
    $this->hash = array();
  }
  public function getID() { return $this->id; }
  public function has($key) {
    return isset($this->hash) && array_key_exists($key, $this->hash);
  }
  public function get($key) {
    if ($this->has($key)) { return $this->hash[$key]; }
    else { return ""; }
  }
  public function set($key, $value) {
    $this->hash[$key] = $value;
  }
  public function setIndex($i) { $this->index = $i; }
  public function getIndex() { return $this->index; }

  public function details() {
    echo "#$this->index\n";
    echo "[$this->id]\n";
    foreach ($this->hash as $k => $v) {
      echo "$k = $v\n";
    }
    echo "\n";
  }
  public function getName() { return $this->hash['name']; }
  public function getExpr() { return $this->hash['expr']; }
  public function getIdx() { return $this->hash['index']; }
  public function getSurv() { return $this->hash['survival']; }
  public function getIH() { return $this->hash['indexHeader']; }
  public function getPlatform() { return $this->hash['platform']; }
  public function getInfo() { return $this->hash['info']; }
  public function hasIH() { return array_key_exists('indexHeader', $this->hash); }
  public function hasSurv() { return array_key_exists('survival', $this->hash); }
  public function hasPlatform() { return array_key_exists('platform', $this->hash); }
  public function hasInfo() { return array_key_exists('info', $this->hash); }
  public function getPre() { 
    return str_replace("-expr.txt", "", $this->hash['expr']);
  }
}

class Database {
  public $conf_file;
  public $global;
  public $list;

  public function __construct($ifile) {
    $this->conf_file = $ifile;
    $this->init();
    $this->build();
  }

  public function init() {
    $this->global = array();
    $this->list = array();
  }

  public function build() {
    $file = $this->conf_file;

    if (($fp = fopen($file, "r")) === FALSE) {
      echo "Can't open file $file <br>";
      exit;
    }
    $n_id = NULL;
    $set = NULL;
    $res = array();
    $index = 0;
    while (!feof($fp))
    { 
      $line = fgets($fp);
      $line = chop($line, "\r\n");
      if (strncmp($line, "[", 1) == 0) {
        if (!is_null($n_id) && !is_null($set)) {
          $res[$n_id] = $set;
        }
        $n_id = preg_replace('/^\s*\[(.+)\]\s*$/i', '$1', $line);
        $set = new Dataset();
        $set->init($n_id);
        $set->setIndex($index);
        $index++;
      }
      elseif (!preg_match('/^\s*$/', $line) && strcmp($n_id, "") != 0) {
        list($k, $v) = split("=", $line, 2);
        $v = trim($v);
        $set->set(trim($k), $v);
      }
      elseif (!preg_match('/^\s*$/', $line) && strcmp($n_id, "") == 0) {
        list($k, $v) = split("=", $line, 2);
        $v = trim($v);
        $v = str_replace('"', '', $v);
        $this->global[trim($k)] = $v;
      }
    }
    fclose($fp);
    if (!is_null($n_id) && !is_null($set)) {
      $res[$n_id] = $set;
    }
    function cmp($a, $b) {
      if ($a->getIndex() == $b->getIndex()) {
        return 0;
      }
      return ($a->getIndex() < $b->getIndex()) ? -1 : 1;
    }
    uasort($res, 'cmp');

    $this->list = $res;
  }

  public function details() {
    foreach ($this->global as $k => $v) {
      echo "$k = $v\n";
    }
    echo "\n";
    foreach ($this->list as $n) {
      $n->details();
    }
  }

  public function getNum() {
    return count($this->list);
  }
  public function getList() {
    return $this->list;
  }
  public function getTitle() {
    if (array_key_exists("title", $this->global)) {
        return $this->global['title'];
    }
    return "Title";
  }
  public function getDataset($id) {
    if (array_key_exists($id, $this->list)) {
        return $this->list[$id];
    }
    return null;
  }

  public function getConf() {
    return $this->conf_file;
  }

}

class Hegemon {
  public $rdataset;
  public $idhash;
  public $namehash;
  public $headers;
  public $fp;
  public $start;
  public $end;

  public function __construct($rd) {
    $this->rdataset = $rd;
    $file = $this->rdataset->getExpr();
    if (($fp = fopen($file, "r")) === FALSE) {
      echo "Can't open file $file <br>\n";
      exit;
    }
    $this->fp = $fp;
    $this->getHeaders($file);
    $this->start = 2;
    $this->end = count($this->headers) - 1;
    $this->idhash = array();
    $this->namehash = array();
  }

  function __destruct() {
    fclose($this->fp);
  }

  public function getHeaders($file) {
    $head = fgets($this->fp);
    $head = rtrim($head, "\r\n");
    $this->headers = explode("\t", $head);

  }

  public function getAllIDs() {
    return array_keys($this->idhash);
  }

  public function getExprFile() {
    return $this->rdataset->getExpr();
  }

  public function getSurv() {
    if ($this->rdataset->hasSurv()) {
      return $this->rdataset->getSurv();
    }
    return null;
  }

  public function getPre() {
    return $this->rdataset->getPre();
  }

  public function init() {
    $this->idhash = array();
    $this->namehash = array();
    $file = $this->rdataset->getIdx();
    $this->readIndexFile($file);
    if ($this->rdataset->hasPlatform()) {
      $file = $this->rdataset->getPlatform();
      $this->readPlatformFile($file);
    }
  }

  public function getNum() {
    return $this->end-$this->start + 1;
  }
  public function getStart() {
    return $this->start;
  }
  public function getEnd() {
    return $this->end;
  }

  public function getPtr($id) {
    if (array_key_exists($id, $this->idhash)) {
        return $this->idhash[$id][0];
    }
    if (array_key_exists(strtoupper($id), $this->namehash)) {
      $id = $this->namehash[strtoupper($id)][0];
      if (array_key_exists($id, $this->idhash)) {
        return $this->idhash[$id][0];
      }
    }
    return null;
  }

  public function getName($id) {
    if (array_key_exists($id, $this->idhash)) {
        return $this->idhash[$id][1];
    }
    if (array_key_exists(strtoupper($id), $this->namehash)) {
      $id = $this->namehash[strtoupper($id)][0];
      if (array_key_exists($id, $this->idhash)) {
        return $this->idhash[$id][1];
      }
    }
    return null;
  }

  public function getDesc($id) {
    if (array_key_exists($id, $this->idhash)) {
        return $this->idhash[$id][2];
    }
    if (array_key_exists(strtoupper($id), $this->namehash)) {
      $id = $this->namehash[strtoupper($id)][0];
      if (array_key_exists($id, $this->idhash)) {
        return $this->idhash[$id][2];
      }
    }
    return null;
  }

  function getExprData($id) {
    $exprFile = $this->getExprFile();
    $ptr1 = $this->getPtr($id);
    if ($ptr1 == null) {
      return null;
    }
    list($x_arr, $h_arr) = U::getX($exprFile, $ptr1, 0);
    return $x_arr;
  }

  function getThrData($id) {
    $exprFile = $this->getExprFile();
    $ptr1 = $this->getPtr($id);
    if ($ptr1 == null) {
      return null;
    }
    list($x_arr, $h_arr) = U::getX($exprFile, $ptr1, 0);
    return U::getThrData($x_arr, $this->start, $this->getNum());
  }

  function compareIds($id1, $id2) {
    $data1 = $this->getExprData($id1);
    $data2 = $this->getExprData($id2);
    if ($data1 == null || $data2 == null) {
      return 0;
    }
    $thr1 = U::getThrData($data1, $this->start, $this->getNum());
    $thr2 = U::getThrData($data2, $this->start, $this->getNum());
    $count1 = 0;
    $count2 = 0;
    for ($i = $this->start; $i <= $this->end; $i++) {
      if (ereg("^\s*$", $data1[$i])) { continue; }
      if (ereg("^\s*$", $data2[$i])) { continue; }
      if ($data1[$i] < $thr1[3]) { continue; }
      if ($data2[$i] < $thr2[3]) { continue; }
      if ($data1[$i] >= $data2[$i]) {
          $count1 ++;
      }
      else {
          $count2 ++;
      }
    }
    if ($count1 == $count2) {
      return 0;
    }
    return ($count1 < $count2) ? +1 : -1;
  }

  public function getIDs($name) {
    $res = array();
    $genes = preg_split("/\s+/", $name);
    foreach ($genes as $g) {
      $name = trim($g);
      if (array_key_exists($name, $this->idhash)) {
        $res[$name] = $name;
      }
      if (array_key_exists(strtoupper($name), $this->namehash)) {
        foreach ($this->namehash[strtoupper($name)] as $id) {
          $res[$id] = $name;
        }
      }
    }
    return $res;
  }

  function getBestID ($list) {
    if (count($list) <= 0) {
      return null;
    }
    if (count($list) == 1) {
      return $list[0];
    }
    usort($list, array($this, "compareIds"));
    return $list[0];
  }

  public function readIndexFile($file) {
    if (($fp = fopen($file, "r")) === FALSE) {
      echo "Can't open file $file <br>\n";
      exit;
    }
    $line = fgets($fp);
    while (!feof($fp))
    {
      $line = fgets($fp);
      $line = rtrim($line, "\r\n");
      $ll = explode("\t", $line);
      if (count($ll) != 4) {
        continue;
      }
      list($id, $ptr, $p_name, $desc) = $ll;
      $lp = explode(" /// ", $p_name);
      $this->idhash[$id] = array($ptr, trim($lp[0]), $desc);
      $this->namehash[strtoupper($id)] = array($id);
      foreach ($lp as $pn) {
        $pn = strtoupper(trim($pn));
        if (strcmp($pn, "") == 0 || strcmp($pn, "---") == 0 ) {
            continue;
        }
        if (!array_key_exists($pn, $this->namehash)) {
          $this->namehash[$pn] = array();
        }
        if (array_search($id, $this->namehash[$pn]) === false) {
          array_push($this->namehash[$pn], $id);
        }
      }
    }
    fclose($fp);
  }

  public function readPlatformFile($file) {
    if (($fp = fopen($file, "r")) === FALSE) {
      echo "Can't open file $file <br>\n";
      exit;
    }
    $line = fgets($fp);
    while (!feof($fp))
    {
      $line = fgets($fp);
      $line = rtrim($line, "\r\n");
      $ll = explode("\t", $line);
      $id = $ll[0];
      for ($i = 1; $i < count($ll); $i++) {
        if ($id == 2) {
          continue;
        }
        $p_name = $ll[$i];
        $lp = explode(" /// ", $p_name);
        foreach ($lp as $pn) {
          $pn = strtoupper(trim($pn));
          if (strcmp($pn, "") == 0 || strcmp($pn, "---") == 0 ) {
            continue;
          }
          if (!array_key_exists($pn, $this->namehash)) {
            $this->namehash[$pn] = array();
          }
          if (array_search($id, $this->namehash[$pn]) === false) {
            array_push($this->namehash[$pn], $id);
          }
        }
      }
    }
    fclose($fp);
  }

  public function printSuggest($val) {
    $idx = $this->rdataset->getIdx();
    self::searchIndexFile($idx, $val);
  }

  public static function searchIndexFile($file, $val) {
    if (($fp = fopen($file, "r")) === FALSE) {
      echo "Can't open file $file <br>\n";
      exit;
    }
    $line = fgets($fp);
    $index = 0;
    $namehash = array();
    while (!feof($fp))
    {
      $line = fgets($fp);
      $line = rtrim($line, "\r\n");
      $ll = explode("\t", $line);
      if (count($ll) != 4) {
        continue;
      }
      list($id, $ptr, $p_name, $desc) = $ll;
      if (!array_key_exists($p_name,$namehash)&&preg_match("/^$val/i", $p_name)) {
        echo "$p_name\n";
        $namehash[$p_name] = 1;
        $index++;
      }
      if ($index >= 10) {
        break;
      }
    }
    if ($index < 10) {
      rewind($fp);
      $line = fgets($fp);
      while (!feof($fp))
      {
        $line = fgets($fp);
        $line = rtrim($line, "\r\n");
        $ll = explode("\t", $line);
        if (count($ll) != 4) {
          continue;
        }
        list($id, $ptr, $p_name, $desc) = $ll;
        if (!array_key_exists($p_name,$namehash)&& self::matchWords($val,$desc)) {
          echo "$p_name:$desc\n";
          $namehash[$p_name] = 1;
          $index++;
        }
        if ($index >= 10) {
          break;
        }
      }
    }
    fclose($fp);
  }

  public static function matchWords($val, $desc) {
    $res = 1;
    foreach (split(" ", $val) as $w) {
      if (!preg_match("/$w/i", $desc)) {
        $res = 0;
      }
    }
    return $res;
  }

  public function topGenes($num) {
    $genes = [];
    if ($this->rdataset->hasInfo()) {
      $file = $this->rdataset->getInfo();
      if (($fp = fopen($file, "r")) === FALSE) {
        echo "Can't open file $file <br>\n";
        exit;
      }
      $line = fgets($fp);
      $line = rtrim($line, "\r\n");
      $headers = explode("\t", $line);
      $hh = array();
      for ($i = 0; $i < count($headers); $i++) {
        $hh[$headers[$i]] = $i;
      }
      $drhash = array();
      $sdhash = array();
      $ids = array();
      while (!feof($fp)) {
        $line = fgets($fp);
        $line = rtrim($line, "\r\n");
        $ll = explode("\t", $line);
        if (count($ll) <= max($hh['max'], $hh['min'], $hh['sd'])) {
          continue;
        }
        $id = $ll[0];
        $dr = $ll[$hh['max']] - $ll[$hh['min']];
        $sd = $ll[$hh['sd']];
        array_push($ids, $id);
        array_push($drhash, $dr);
        array_push($sdhash, $sd);
      }
      fclose($fp);
      list($mindr, $thrdr1, $maxdr) = U::getThreshold($drhash);
      list($minsd, $thrsd1, $maxsd) = U::getThreshold($sdhash);
      $res = array();
      for ($i = 0; $i < count($ids); $i++) {
        if ($drhash[$i] > $thrdr1 && $sdhash[$i] > $thrsd1) {
           $res[$ids[$i]] = $drhash[$i];
        }
      }
      arsort($res);
      $genes = array_slice($res, 0, $num);
    }
    return $genes;
  }

  public function getBooleanRelations($id1, $sthr, $pthr) {
    $res = [];
    $pre = $this->getPre();
    $path = getenv("PATH");        // save old value 
    $java_home = "/booleanfs/sahoo/softwares/java/jdk1.8.0_45";
    $path1 = "$java_home/bin";
    if ($path1) { $path1 .= ":$path"; }           // append old paths if any 
    putenv("PATH=$path1");        // set new value 
    putenv("JAVA_HOME=$java_home");        // set new value 
    $cmd = "java Hegemon boolean $pre $id1";
    if ( ($fh = popen($cmd, 'r')) === false )
      die("Open failed: ${php_errormsg}\n");
    while (!feof($fh))
    {
      $line = fgets($fh);
      $line = rtrim($line, "\r\n");
      $list = explode("\t", $line);
      if (count($list) != 13) {
        continue;
      }
      $bs = [[], [], array_slice($list, 5, 4), array_slice($list, 9, 4)];
      $rel = U::getBooleanRelations($bs, $sthr, $pthr);
      $res[$list[0]] = $rel;
    }
    pclose($fh);
    return $res;
  }

  public function getCorrelation($id1) {
    $res = [];
    $exprFile = $this->getExprFile();
    $path = getenv("PATH");        // save old value 
    $java_home = "/booleanfs/sahoo/softwares/java/jdk1.8.0_45";
    $path1 = "$java_home/bin";
    if ($path1) { $path1 .= ":$path"; }           // append old paths if any 
    putenv("PATH=$path1");        // set new value 
    putenv("JAVA_HOME=$java_home");        // set new value 
    $cmd = "java Hegemon corr $exprFile $id1";
    if ( ($fh = popen($cmd, 'r')) === false )
      die("Open failed: ${php_errormsg}\n");
    while (!feof($fh))
    {
      $line = fgets($fh);
      $line = rtrim($line, "\r\n");
      $list = explode("\t", $line);
      if (count($list) < 2) {
        continue;
      }
      $res[$list[1]] = $list[0];
    }
    pclose($fh);
    return $res;
  }

}

?>
