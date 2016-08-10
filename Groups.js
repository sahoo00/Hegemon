
var groups = new Array();
var groupNames = new Array();
var numGroups = 0;
var numKeys = 0;
var showVal = new Array();
var showImVal = new Array();
var maxarrayget = 200;

function createGroup(str) {
  var g = new Array();
  for (var i = 2; i < (str.length-1); i++) {
    var list = str[i].split("\t");
    g[i-2] = list[0];
  }
  return g;
}

function removeGroups(id) {
  if (typeof(groups[id]) != 'undefined') {
    delete groups[id];
    delete groupNames[id];
    numGroups--;
  }
}

function addGroups(g) {
  if (g && g.length > 0) {
    groups[numKeys] = g;
    groupNames[numKeys] = 'Group ' + numKeys;
    numKeys++;
    numGroups++;
  }
}

function callDiffBA() {
  var list = document.getElementsByName('selectGroup');
  var g = new Array();
  var num = 0;
  for (var i = list.length - 1; i >= 0; i--) {
    if (list[i].checked) {
      if (num == 0) {
        num++;
        var id = list[i].value;
        for (x in groups[id]) {
          g[groups[id][x]] = 1;
        }
      }
      else {
        num++;
        var id = list[i].value;
        for (x in groups[id]) {
          if (g[groups[id][x]]) {
            g[groups[id][x]] = 0;
          }
        }
      }
    }
  }
  if (num > 0) {
    var res = new Array();
    for (x in g) {
      if (g[x] == 1) {
        res.push(x);
      }
    }
    addGroups(res);
    updateGroupDisplay();
  }
  return false;
}

function callDiffAB() {
  var list = document.getElementsByName('selectGroup');
  var g = new Array();
  var num = 0;
  for (var i = 0; i < list.length; i++) {
    if (list[i].checked) {
      if (num == 0) {
        num++;
        var id = list[i].value;
        for (x in groups[id]) {
          g[groups[id][x]] = 1;
        }
      }
      else {
        num++;
        var id = list[i].value;
        for (x in groups[id]) {
          if (g[groups[id][x]]) {
            g[groups[id][x]] = 0;
          }
        }
      }
    }
  }
  if (num > 0) {
    var res = new Array();
    for (x in g) {
      if (g[x] == 1) {
        res.push(x);
      }
    }
    addGroups(res);
    updateGroupDisplay();
  }
  return false;
}

function callIntersection() {
  var list = document.getElementsByName('selectGroup');
  var g = new Array();
  var num = 0;
  for (var i = 0; i < list.length; i++) {
    if (list[i].checked) {
      num++;
      var id = list[i].value;
      for (x in groups[id]) {
        if (g[groups[id][x]]) {
          g[groups[id][x]] += 1;
        }
        else {
          g[groups[id][x]] = 1;
        }
      }
    }
  }
  var res = new Array();
  for (x in g) {
    if (g[x] == num) {
      res.push(x);
    }
  }
  addGroups(res);
  updateGroupDisplay();
  return false;
}

function callUnion() {
  var list = document.getElementsByName('selectGroup');
  var g = new Array();
  for (var i = 0; i < list.length; i++) {
    if (list[i].checked) {
      var id = list[i].value;
      for (x in groups[id]) {
        g[groups[id][x]] = 1;
      }
    }
  }
  var res = new Array();
  for (x in g) {
    res.push(x);
  }
  addGroups(res);
  updateGroupDisplay();
  return false;
}

function callRemove() {
  var list = document.getElementsByName('selectGroup');
  for (var i = 0; i < list.length; i++) {
    if (list[i].checked) {
      var id = list[i].value;
      removeGroups(id);
    }
  }
  updateGroupDisplay();
  return false;
}

function callReset() {
  groups = new Array();
  groupNames = new Array();
  numGroups = 0;
  numKeys = 0;
  showVal = new Array();
  showImVal = new Array();
  updateGroupDisplay();
  return false;
}

function displayGroups() {
  var str = '';
  str += numGroups + "<br/>";
  for (var i in groups) {
    str += '<input type="checkbox" name="selectGroup" value="' + i + '" ';
    str += ' onclick="changeImage();" />' ;
    str += '<input type="text" size="10" value="' + groupNames[i] + '" ';
    str += ' onchange="changeGroupNames(this, ' + i + ');" />' ;
    str += ' ' + groups[i].length + ' <br/> ';
  }
  str += "";
  if (showVal && showVal.length > 0) {
    str += '<textarea name="showVal" cols="20" rows="5">';
    for (var i in showVal) {
      str += showVal[i] + "\n";
    }
    str += '</textarea>';
    str += "<br/>\n";
    str += "<div style=\"line-height:110%;\">\n";
    var pattern = /(GSM\d+).*/;
    for (var i in showVal) {
      if (pattern.test(showVal[i])) {
        var id = showVal[i].replace(pattern, "$1");
        str += "<a href=\"http://www.ncbi.nlm.nih.gov/geo/query/acc.cgi?acc=" + id + "\" target=\"_blank\">" + id + "</a><br/>\n";
      }
    }
    str += "</div>\n";
  }
  if (showImVal && showImVal.length > 0) {
    var imgurl= document.getElementById('img0link').href;
    var keys = getKeys(imgurl);
    str += "<div style=\"line-height:110%;\">\n";
    for (var i in showImVal) {
      var id = showImVal[i];
      str += "<a href=\"zoom.php?dataset=" + keys['id'] + 
        "&aid=" + id + "\" target=\"_blank\">" + id + "</a><br/>\n";
    }
    str += "</div>\n";
  }
  return str;
}

function updateGroupDisplay() {
  var dis = document.getElementById('group_display');
  dis.innerHTML = displayGroups();
  dis.style.visibility="visible";
}

function submitMouseEvent() {
  if (xmlHttp != null && xmlHttp.readyState==4) { 
    var str = xmlHttp.responseText.split("\n");
    if (str[0] > 0) {
      addGroups(createGroup(str));
    }
    updateGroupDisplay();
  }
}

function getGroupStr() {
  var list = document.getElementsByName('selectGroup');
  var str = '';
  for (var i = 0; i < list.length; i++) {
    if (list[i].checked) {
      var id = list[i].value;
      str += i + '=' + groupNames[id] + '=' + groups[id].join(':') + ';' ;
    }
  }
  return escape(str);
}

function getNumArrays() {
  var list = document.getElementsByName('selectGroup');
  var sum = 0;
  for (var i = 0; i < list.length; i++) {
    if (list[i].checked) {
      var id = list[i].value;
      sum += groups[id].length;
    }
  }
  return sum;
}

function changeImage() {
  var num = getNumArrays();
  var url = document.getElementById('img0link').href;
  if (num < maxarrayget) {
    url += '&groups=' + getGroupStr();
    document.getElementById('img0').src = url;
    document.getElementById('img1link').href = url;
    document.getElementById('img1link').style.visibility = 'visible';
  }
  else {
    var list = url.split("?");
    var params = list[1] + '&groups=' + getGroupStr();
    xmlHttp.open("POST", list[0], true);
    //Send the proper header information along with the request
    xmlHttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xmlHttp.setRequestHeader("Content-length", params.length);
    xmlHttp.setRequestHeader("Connection", "close");
    xmlHttp.onreadystatechange = function() {
      if(xmlHttp.readyState == 4 && xmlHttp.status == 200) {
        var ur = xmlHttp.responseText;
        document.getElementById('img0').src = ur;
        document.getElementById('img1link').href = ur;
        document.getElementById('img1link').style.visibility = 'visible';
      }
    }
    xmlHttp.send(params);
  }
}

function changeGroupNames(box, id) {
  groupNames[id] = box.value;
}

function callSurvival() {
  var num = getNumArrays();
  var str1 = escape(document.getElementById('CT').value);
  var url = document.getElementById('img0link').href;
  url += '&CT=' + str1 + '&groups=' + getGroupStr();
  url = url.replace("go=plot", "go=survival");
  if (num < maxarrayget) {
    document.getElementById('img0').src = url;
    document.getElementById('img2link').href = url;
    document.getElementById('img2link').style.visibility = 'visible';
  }
  else {
    var list = url.split("?");
    var params = list[1];
    xmlHttp.open("POST", list[0], true);
    //Send the proper header information along with the request
    xmlHttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xmlHttp.setRequestHeader("Content-length", params.length);
    xmlHttp.setRequestHeader("Connection", "close");
    xmlHttp.onreadystatechange = function() {
      if(xmlHttp.readyState == 4 && xmlHttp.status == 200) {
        var ur = xmlHttp.responseText;
        document.getElementById('img0').src = ur;
        document.getElementById('img2link').href = ur;
        document.getElementById('img2link').style.visibility = 'visible';
      }
    }
    xmlHttp.send(params);
  }
}

function callScr() {
  if (!(xmlHttp.readyState == 4 || xmlHttp.readyState == 0)) {
    return;
  }
  var num = getNumArrays();
  var str1 = escape(document.getElementById('CT').value);
  var url = document.getElementById('img0link').href;
  url += '&CT=' + str1 + '&groups=' + getGroupStr();
  url = url.replace("go=plot", "go=getsurvival");
  var ss = document.getElementById('results');
  ss.innerHTML ='Please Wait ... <br/><img width=70 src="loading.gif"/>' ;
  ss.style.visibility = 'visible';
  if (num < maxarrayget) {
    xmlHttp.open("GET", url, true);
    xmlHttp.onreadystatechange = handleSurvival; 
    xmlHttp.send(null);
  }
  else {
    var list = url.split("?");
    var params = list[1];
    xmlHttp.open("POST", list[0], true);
    //Send the proper header information along with the request
    xmlHttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xmlHttp.setRequestHeader("Content-length", params.length);
    xmlHttp.setRequestHeader("Connection", "close");
    xmlHttp.onreadystatechange = handleSurvival; 
    xmlHttp.send(params);
  }
}
function handleSurvival() {
  if (xmlHttp.readyState == 4) {
    var ss = document.getElementById('results');
    ss.innerHTML = '';
    ss.innerHTML = xmlHttp.responseText;
    ss.style.visibility = 'visible';
  }
}

function callSelectLineTool() {
  if (xmlHttp.readyState == 4 || xmlHttp.readyState == 0) {
    var url = document.getElementById('img0link').href;
    var str1 = escape(document.getElementById('clinical0').value);
    url = url.replace("n=-1", "n=" + str1);
    document.getElementById('imgh0').src = url;
  }
}

function callSelectTool() {
  if (xmlHttp.readyState == 4 || xmlHttp.readyState == 0) {
    var s1 = escape(getDatasetID('dataset'));
    var url = 'explore.php?go=selectPatientInfo';
    url += '&id=' + s1;
    var str1 = escape(document.getElementById('clinical0').value);
    url += '&clinical=' + str1;
    xmlHttp.open("GET", url, true);
    xmlHttp.onreadystatechange = handleSelectTool; 
    xmlHttp.send(null);
  }
}

function handleSelectTool() {
  if (xmlHttp.readyState == 4) {
    var ss = document.getElementById('selectPatientInfo');
    ss.innerHTML = xmlHttp.responseText;
    ss.style.visibility = 'visible';
  }
}
 
function callPatientGroup(id, num) {
  if (xmlHttp.readyState == 4 || xmlHttp.readyState == 0) {
    var s1 = escape(getDatasetID('dataset'));
    var url = 'explore.php?go=getPatients&id=' + s1;
    url += '&clinical=' + num;
    var str1 = escape(document.getElementById(id).value);
    str1 = str1.replace("+", "%2B");
    url += '&value=' + str1;
    xmlHttp.open("GET", url, true);
    xmlHttp.onreadystatechange = submitMouseEvent; 
    xmlHttp.send(null);
  }
}

function callRect() {
  if (xmlHttp.readyState == 4 || xmlHttp.readyState == 0) {
    var url = document.getElementById('img0link').href;
    url = url.replace("go=plot", "go=getrect");
    xmlHttp.open("GET", url, true);
    xmlHttp.onreadystatechange = handleRect; 
    xmlHttp.send(null);
  }
}

function handleRect() {
  if (xmlHttp.readyState == 4) {
    var ss = document.getElementById('selectPatientInfo');
    ss.innerHTML = xmlHttp.responseText;
    ss.style.visibility = 'visible';
  }
}

function callRectGroup(id) {
  if (xmlHttp.readyState == 4 || xmlHttp.readyState == 0) {
    var str1 = escape(document.getElementById(id).value);
    var url = document.getElementById('img0link').href;
    url += '&value=' + str1;
    url = url.replace("go=plot", "go=getrectgroup");
    xmlHttp.open("GET", url, true);
    xmlHttp.onreadystatechange = submitMouseEvent; 
    xmlHttp.send(null);
  }
}

function callThr() {
  if (xmlHttp.readyState == 4 || xmlHttp.readyState == 0) {
    var url = document.getElementById('img0link').href;
    url = url.replace("go=plot", "go=getthr");
    xmlHttp.open("GET", url, true);
    xmlHttp.onreadystatechange = handleThr; 
    xmlHttp.send(null);
  }
}

function handleThr() {
  if (xmlHttp.readyState == 4) {
    var ss = document.getElementById('selectPatientInfo');
    ss.innerHTML = xmlHttp.responseText;
    ss.style.visibility = 'visible';
  }
}

function callThrGroup(id) {
  var num = getNumArrays();
  var url = document.getElementById('img0link').href;
  url += '&groups=' + getGroupStr();
  var str2 = escape(document.getElementById(id).value);
  url += '&value=' + str2;
  url = url.replace("go=plot", "go=getthrgroup");
  document.getElementById('img0').src = url;
  document.getElementById('img1link').href = url;
  document.getElementById('img1link').style.visibility = 'visible';
}

function callGetLinePlots() {
  if (xmlHttp.readyState == 4 || xmlHttp.readyState == 0) {
    var ss = document.getElementById('lineresults');
    ss.innerHTML ='Please Wait ... <br/><img width=70 src="loading.gif"/>' ;
    ss.style.visibility = 'visible';
    var s1 = escape(getDatasetID('dataset'));
    var str1 = escape(document.getElementById('gList').value);
    if ( !s1 ) {
      ss.innerHTML = 'Select a dataset';
      return;
    }
    if ( s1 && (str1 == null)) {
      ss.innerHTML = 'Enter a list of genes';
      return;
    }
    var params = "go=getlineplots&id=" + s1 + "&gList=" + str1;
    xmlHttp.open("POST", "explore.php", true);
    //Send the proper header information along with the request
    xmlHttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xmlHttp.setRequestHeader("Content-length", params.length);
    xmlHttp.setRequestHeader("Connection", "close");
    xmlHttp.onreadystatechange = function() {
      if(xmlHttp.readyState == 4 && xmlHttp.status == 200) {
        var ss = document.getElementById('lineresults');
        ss.innerHTML = xmlHttp.responseText;
        ss.style.visibility = 'visible';
      }
    }
    xmlHttp.send(params);
  }		
}

function callBoxP() {
  var num = getNumArrays();
  var str1 = escape(document.getElementById('CT').value);
  var url = document.getElementById('img0link').href;
  url += '&CT=' + str1 + '&groups=' + getGroupStr();
  url = url.replace("go=plot", "go=boxplot");
  if (num < maxarrayget) {
    document.getElementById('img0').src = url;
    document.getElementById('img2link').href = url;
    document.getElementById('img2link').style.visibility = 'visible';
  }
  else {
    var list = url.split("?");
    var params = list[1];
    xmlHttp.open("POST", list[0], true);
    //Send the proper header information along with the request
    xmlHttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xmlHttp.setRequestHeader("Content-length", params.length);
    xmlHttp.setRequestHeader("Connection", "close");
    xmlHttp.onreadystatechange = function() {
      if(xmlHttp.readyState == 4 && xmlHttp.status == 200) {
        var ur = xmlHttp.responseText;
        document.getElementById('img0').src = ur;
        document.getElementById('img2link').href = ur;
        document.getElementById('img2link').style.visibility = 'visible';
      }
    }
    xmlHttp.send(params);
  }
}

function callShow() {
  var list = document.getElementsByName('selectGroup');
  var g = new Array();
  for (var i = 0; i < list.length; i++) {
    if (list[i].checked) {
      var id = list[i].value;
      for (x in groups[id]) {
        g[groups[id][x]] = 1;
      }
    }
  }
  var res = new Array();
  for (x in g) {
    res.push(x);
  }
  showVal = res;
  updateGroupDisplay();
}

function callShowIm() {
  var list = document.getElementsByName('selectGroup');
  var g = new Array();
  for (var i = 0; i < list.length; i++) {
    if (list[i].checked) {
      var id = list[i].value;
      for (x in groups[id]) {
        g[groups[id][x]] = 1;
      }
    }
  }
  var res = new Array();
  for (x in g) {
    res.push(x);
  }
  showImVal = res;
  updateGroupDisplay();
}

function callStats() {
  var url = document.getElementById('img0link').href;
  url = url.replace("go=plot", "go=getlstats");
  xmlHttp.open("GET", url, true);
  xmlHttp.onreadystatechange = function() {
    if(xmlHttp.readyState == 4 && xmlHttp.status == 200) {
      var ur = xmlHttp.responseText;
      var ss1 = document.getElementById('lineresults');
      ss1.innerHTML = ur;
      ss1.style.visibility = 'visible';
    }
  }
  xmlHttp.send(null);
}

function getKeys(str) {
  var url= str;
  var pr = url.split("?");
  var vars = pr[1].split("&");
  var keys = new Array();
  keys['?'] = pr[0];
  for (var i=0;i<vars.length;i++) {
    var pair = vars[i].split("=");
    keys[pair[0]] = pair[1];
  }
  return keys;
}

function callClear() {
  var ss = document.getElementById('results');
  ss.innerHTML = '';
  ss.style.visibility = 'visible';
  var ss1 = document.getElementById('lineresults');
  ss1.innerHTML = '';
  ss1.style.visibility = 'visible';
}

