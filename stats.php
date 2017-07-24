<?php

include "util.php";

$params = null;
if (array_key_exists("params", $_GET)) {
  $params = getParams($_GET["params"]);
}
if (array_key_exists("params", $_POST)) {
  $params = getParams($_POST["params"]);
}

if (array_key_exists("go", $_GET)) {
  if (strcmp($_GET["go"], "StepMiner") == 0) {
    printStepMiner($_GET['A'], $_GET['B']);
  }
  if (strcmp($_GET["go"], "hist") == 0) {
    printHistogram($_GET['A'], $_GET['B'], $params);
  }
}
elseif (array_key_exists("go", $_POST)) {
  if (strcmp($_POST["go"], "StepMiner") == 0) {
    printStepMiner($_POST['A'], $_POST['B']);
  }
  if (strcmp($_POST["go"], "hist") == 0) {
    printHistogram($_POST['A'], $_POST['B'], $params);
  }
  if (strcmp($_POST["go"], "intersect") == 0) {
    printIntersect($_POST['A'], $_POST['B']);
  }
  if (strcmp($_POST["go"], "union") == 0) {
    printUnion($_POST['A'], $_POST['B']);
  }
  if (strcmp($_POST["go"], "diffab") == 0) {
    printDiff($_POST['A'], $_POST['B']);
  }
  if (strcmp($_POST["go"], "diffba") == 0) {
    printDiff($_POST['B'], $_POST['A']);
  }
}
else {
  printSummary();
}

function getParams($str) {
  $res = [];
  foreach (explode(";", urldecode($str)) as $p) {
    list($k, $v) = explode("=", $p);
    $res[$k] = $v;
  }
  return $res;
}

function getNumbers($a) {
  #preg_match_all('!\d+\.*\d*!', $a ,$match);
  preg_match_all('![-+]?\d*\.?\d+([eE][-+]?\d+)?!', $a ,$match);
  if (count($match) > 0) {
    return $match[0];
  }
  return [];
}

function printStepMiner ($a, $b) {
  $numa = getNumbers(urldecode($a));
  $numb = getNumbers(urldecode($b));
  $thra = U::getThreshold($numa);
  $thrb = U::getThreshold($numb);
  if ($thra[1] != "") {
    $thra[1] = sprintf("%.3f", $thra[1]);
  }
  if ($thrb[1] != "") {
    $thrb[1] = sprintf("%.3f", $thrb[1]);
  }
  $sa = U::getXstats($numa, 0);
  $sb = U::getXstats($numb, 0);
  echo "<table border=\"0\">\n";
  echo "<tr><td>T</td><td>Num</td><td>Min</td><td>Mean (sd) </td>
    <td>StepMiner</td><td>Max</td><td>box</td></tr>";
  echo "<tr><td>A</td><td>".$sa['n']."</td><td>$thra[0]</td><td>".
    $sa['m']." (".$sa['sd'].") </td>
    <td>$thra[1]</td><td>$thra[2]</td><td>" . $sa["box"] . "</td></tr>";
  echo "<tr><td>B</td><td>".$sb['n']."</td><td>$thrb[0]</td><td>".
    $sb['m']." (".$sb['sd'].") </td>
    <td>$thrb[1]</td><td>$thrb[2]</td><td>" . $sb["box"] . "</td></tr>";
  echo "</table>\n";
}

function printTabular($arr, $num) {
  echo count($arr) . "<br/>";
  echo "<table border=\"0\">\n";
  echo "<tr>";
  for ($i = 0; $i < count($arr); $i++) {
    $val = $arr[$i];
    echo "<td> $val </td>\n";
    if ( ($i % $num) == ($num - 1) ) {
      echo "</tr><tr>\n";
    }
  }
  echo "</tr>";
  echo "</table>\n";
}

function printIntersect ($a, $b) {
  echo "Intersect <br/>";
  $a = trim(strtoupper(urldecode($a)));
  $b = trim(strtoupper(urldecode($b)));
  $la = preg_split("/\s+/", $a);
  $lb = preg_split("/\s+/", $b);
  $res = U::intersection($la, $lb);
  $res = array_unique($res);
  printTabular($res, 6);
}

function printUnion ($a, $b) {
  echo "Union <br/>";
  $a = trim(strtoupper(urldecode($a)));
  $b = trim(strtoupper(urldecode($b)));
  $la = preg_split("/\s+/", $a);
  $lb = preg_split("/\s+/", $b);
  $res = U::union($la, $lb);
  $res = array_unique($res);
  printTabular($res, 6);
}

function printDiff ($a, $b) {
  echo "Diff <br/>";
  $a = trim(strtoupper(urldecode($a)));
  $b = trim(strtoupper(urldecode($b)));
  $la = preg_split("/\s+/", $a);
  $lb = preg_split("/\s+/", $b);
  $res = U::diff($la, $lb);
  $res = array_unique($res);
  printTabular($res, 6);
}

function printHistogram ($a, $b, $params = null) {
  $numa = getNumbers(urldecode($a));
  $numb = getNumbers(urldecode($b));
  $hista = U::plotHistogram($numa, 0, $params);
  $histb = U::plotHistogram($numb, 0, $params);
  echo "<table border=\"0\">\n";
  echo "<tr><td>A</td><td><img src=\"".$hista["img"]."\"/></td><td>\n";
  echo "<table border=\"0\">\n";
  echo "<tr><td>Breaks</td>";
  echo "<td>Counts</td></tr>";
  $breaks = explode(" ", $hista["breaks"]);
  $counts = explode(" ", $hista["counts"]);
  foreach (range(0, count($counts) - 2) as $i) {
    echo "<tr><td>".$breaks[$i]."</td>";
    echo "<td>".$counts[$i]."</td></tr>";
  }
  echo "</table>\n";
  echo "</td></tr>";
  echo "<tr><td>B</td><td><img src=\"".$histb["img"]."\"/></td><td>\n";
  echo "<table border=\"0\">\n";
  echo "<tr><td>Breaks</td>";
  echo "<td>Counts</td></tr>";
  $breaks = explode(" ", $histb["breaks"]);
  $counts = explode(" ", $histb["counts"]);
  foreach (range(0, count($counts) - 2) as $i) {
    echo "<tr><td>".$breaks[$i]."</td>";
    echo "<td>".$counts[$i]."</td></tr>";
  }
  echo "</table>\n";
  echo "</td></tr>";
  echo "</table>\n";
}

function printSummary() {
  printHeader();
  printBody();
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
  <script src=\"stats.js\"></script>
  </head>
  <body>
";
}

function printBody() {
echo "
    <div id=\"statsAll\">
      <form name=\"statForm\" action=\"\">
      <table border=\"0\">
      <tr><td>
      List A: <br/><textarea rows=\"10\" cols=\"20\" id=\"Ab\" 
              name=\"Ab\" alt=\"List A\"></textarea>
        </td><td>
      List B: <br/><textarea rows=\"10\" cols=\"20\" id=\"Bb\"
              name=\"Bb\" alt=\"List B\"></textarea>
        </td></tr>
      </table>
      Tools: 
          <input type=\"button\" name=\"StepMiner\" value=\"StepMiner\"
              onclick=\"callStepMiner();\"/>
          <input type=\"button\" name=\"histogram\" value=\"histogram\"
              onclick=\"callHistogram();\"/>
          <input type=\"button\" name=\"intersect\" value=\"intersect\"
              onclick=\"callIntersect();\"/>
          <input type=\"button\" name=\"union\" value=\"union\"
              onclick=\"callUnion();\"/>
          <input type=\"button\" name=\"Diff-A-B\" value=\"Diff-A-B\"
              onclick=\"callDiffAB();\"/>
          <input type=\"button\" name=\"Diff-B-A\" value=\"Diff-B-A\"
              onclick=\"callDiffBA();\"/>
      <br clear=\"all\"/>
      Parameters: 
          <input type=\"text\"  size=\"20\" id=\"params\" value=\"\"/>
      <br clear=\"all\"/>
      <div id=\"results\"> </div>
      <div id=\"lineresults\"> </div>
      </form>
    </div>
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
        <li>
Debashis Sahoo, Jun Seita, Deepta Bhattacharya, Matthew A. Inlay, Sylvia K.
Plevritis, Irving L. Weissman, David L. Dill. MiDReG: A Method of Mining
Developmentally Regulated Genes using Boolean Implications. Proc Natl Acad Sci
U S A. 2010 Mar 30;107(13):5732-7. Epub 2010 Mar 15.
        </li>
        <li>
Piero Dalerba *, Tomer Kalisky *, Debashis Sahoo *, Pradeep S. Rajendran, Mike
Rothenberg, Anne A. Leyrat, Sopheak Sim, Jennifer Okamoto, John D. Johnston,
Dalong Qian, Maider Zabala, Janet Bueno, Norma Neff, Jianbin Wang, Andy A.
Shelton, Brendan Visser, Shigeo Hisamori, Mark van den Wetering, Hans Clevers,
Michael F. Clarke * and Stephen R. Quake *. Single-cell dissection of
transcriptional heterogeneity in human colon tumors. Nature Biotech, 2011 Nov
13. doi: 10.1038/nbt.2038
        </li>
        <li>
Jens-Peter Volkmer *, Debashis Sahoo *, Robert Chin *, Philip Levy Ho, Chad
Tang, Antonina V. Kurtova, Stephen B. Willingham, Senthil K. Pazhanisamy,
Humberto Contreras-Trujillo, Theresa A. Storm, Yair Lotan, Andrew H. Beck,
Benjamin Chung, Ash A. Alizadeh, Guilherme Godoy, Seth P. Lerner, Matt van de
Rijn, Linda. D. Shortliffe, Irving L. Weissman *, and Keith S. Chan *. Three
differentiation states risk-stratify bladder cancer into distinct subtypes.
PNAS February 7, 2012 vol. 109 no. 6 pp 2078-2083.
        </li>
        <li>
Piero Dalerba*, Debashis Sahoo*, Soonmyung Paik, Xiangqian Guo, Greg Yothers,
Nan Song, Nate Wilcox-Fogel, Erna Forgó, Pradeep S. Rajendran, Stephen P.
Miranda, Shigeo Hisamori, Jacqueline Hutchison, Tomer Kalisky, Dalong Qian,
Norman Wolmark, George A. Fisher, Matt van de Rijn, and Michael F. Clarke. CDX2
as a Prognostic Biomarker in Stage II and Stage III Colon Cancer. N Engl J Med.
2016 Jan 21;374(3):211-22. doi: 10.1056/NEJMoa1506597.
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
