
var groups = new Array();
var groupNames = new Array();
var numGroups = 0;
var numKeys = 0;
var searchGroup = 0;
var showVal = new Array();
var showImVal = new Array();
var maxarrayget = 200;

function urlencode(str) {
   var symbols = {
      '@': '%40',
      '&amp;': '%26',
      '*': '%2A',
      '+': '%2B',
      '/': '%2F',
      '&lt;': '%3C',
      '&gt;': '%3E'
   };
   return escape(str).replace(/([@*+/]|%26(amp|lt|gt)%3B)/g, function (m) { return symbols[m]; });
}

function createGroup(str) {
  var g = new Array();
  var i = 0;
  var index = 0;
  while ( (i+1) < str.length && str[i] && str[i] != "") {
    var count = +str[i];
    if (count > 0) {
      g[index] = ["", [] ];
      var list = str[i + 1].split("\t");
      g[index][0] = list[0];
    }
    for (var j = 0; j < count; j++) {
      var k = i + 2 + j;
      if (k >= str.length) {
        return g;
      }
      var list = str[k].split("\t");
      g[index][1][j] = list[0];
    }
    if (count > 0) {
      index++;
    }
    i = i + 2 + count;
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

function addGroups(g, updateName) {
  if (g && g.length > 0) {
    for (var i = 0; i < g.length; i++) {
      if (g[i] && g[i][1] && g[i][1].length > 0) {
        groups[numKeys] = g[i][1];
        groupNames[numKeys] = 'Group ' + numKeys;
        if (updateName) {
          groupNames[numKeys] = g[i][0];
        }
        numKeys++;
        numGroups++;
      }
    }
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
    addGroups([["DiffBA", res]]);
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
    addGroups([["DiffAB", res]]);
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
  addGroups([["Intersect", res]]);
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
  addGroups([["Union", res]]);
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
  searchGroup = 0;
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
  if (searchGroup) {
    str += '<textarea id="searchGroupArea" name="searchGroupArea" cols="20" rows="5">';
    str += '</textarea><br/>';
    str += '<input type="button" name="searchGroup" value="searchGroup"';
    str += ' onclick="callSearchGroup();" />' ;
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

function loadDisplay(mouse, e) {
  var imgobj = document.getElementById('img0');
  var ori = getOffset(imgobj);
  var url = '';
  url = url + "&top=" + mouse.getTop();
  url = url + "&left=" + mouse.getLeft();
  url = url + "&width=" + mouse.getWidth();
  url = url + "&height=" + mouse.getHeight();
  url = url + "&orix=" + ori.left;
  url = url + "&oriy=" + ori.top;
  var imgurl= document.getElementById('img0link').href;
  try {
    url = imgurl.replace("go=plot", "go=group") + url;
    xmlHttp.open("GET",url,true);
    xmlHttp.onreadystatechange=submitMouseEvent;
    xmlHttp.send(null);
  } catch(e) {
    alert("URL Problem");
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
  return urlencode(str);
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

function updateImg1linkPost(url) {
  var list = url.split("?");
  var d = list[1].split('&').reduce(function(s,c){
      var t=c.split('=');s[t[0]]=t[1];return s;},{})
  $.ajax({type: 'POST',
      data: d,
      url: list[0],
      success: function (data) {
      var ur = data;
      document.getElementById('img0').src = ur;
      document.getElementById('img1link').href = ur;
      document.getElementById('img1link').style.visibility = 'visible';
        }});
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
    var nurl = url + '&groups=' + getGroupStr();
    updateImg1linkPost(nurl);
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
    var url = document.getElementById('img0link').href;
    var list = url.split("?");
    url = list[0] + '?go=selectPatientInfo';
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
    var url = document.getElementById('img0link').href;
    var list = url.split("?");
    url = list[0] + '?go=getPatients&id=' + s1;
    url += '&clinical=' + num;
    var str1 = escape(document.getElementById(id).value);
    str1 = str1.replace(/\+/g, "%2B");
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
    var url = document.getElementById('img0link').href;
    var list = url.split("?");
    xmlHttp.open("POST", list[0], true);
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
  $('#explore').hide();
}

var selectionRect = {
    element         : null,
    previousElement : null,
    currentY        : 0,
    currentX        : 0,
    originX         : 0,
    originY         : 0,
    setElement: function(ele) {
        this.previousElement = this.element;
        this.element = ele;
    },
    getNewAttributes: function() {
        var x = this.currentX<this.originX?this.currentX:this.originX;
        var y = this.currentY<this.originY?this.currentY:this.originY;
        var width = Math.abs(this.currentX - this.originX);
        var height = Math.abs(this.currentY - this.originY);
        return {
            x       : x,
            y       : y,
            width   : width,
            height  : height
        };
    },
    getCurrentAttributes: function() {
        // use plus sign to convert string into number
        var x = +this.element.attr("x");
        var y = +this.element.attr("y");
        var width = +this.element.attr("width");
        var height = +this.element.attr("height");
        return {
            x1  : x,
            y1  : y,
            x2  : x + width,
            y2  : y + height
        };
    },
    getCurrentAttributesAsText: function() {
        var attrs = this.getCurrentAttributes();
        return "x1: " + attrs.x1 + " x2: " + attrs.x2 + " y1: " + attrs.y1 + " y2: " + attrs.y2;
    },
    init: function(newX, newY, svg) {
        var rectElement = svg.append("rect")
          .attr("rx", 4)
          .attr("ry", 4)
          .attr("x", 0)
          .attr("y", 0)
          .attr("width", 0)
          .attr("height", 0)
          .classed("selection", true);
        this.setElement(rectElement);
        this.originX = newX;
        this.originY = newY;
        this.update(newX, newY);
    },
    update: function(newX, newY) {
        this.currentX = newX;
        this.currentY = newY;
        var a = this.getNewAttributes();
        for (var k in a) {
          this.element.attr(k, a[k]);
        }
    },
    focus: function() {
        this.element
            .style("stroke", "#DE695B")
            .style("stroke-width", "1");
    },
    remove: function() {
        this.element.remove();
        this.element = null;
    },
    removePrevious: function() {
        if(this.previousElement) {
            this.previousElement.remove();
        }
    }
};

function displayCorrRes(corr, cData) {
  var columns = corrData.getColumns();
  d3.select("#tablenum").text(function (a) { return corr.length; });
  var table = d3.select("#lineresults").select("table")
    .html(function (a) { return ""; });
  var thead = table.append("thead"),
      tbody = table.append("tbody");
  thead.append("tr").selectAll("th").data(columns).enter().append("th")
    .text(function (c) { return c; });
  var rows = tbody.selectAll("tr").data(corr).enter().append("tr");
  var cells = rows.selectAll("td")
    .data(function(row) {
        var r = [row, "E", "E", "E"];
        if (typeof row != "undefined") {
        r = [row].concat(cData.get(row));
        r[1] = cData.getC(row, 0);
        r[2] = cData.getC(row, 1);
        }
        return r;})
    .enter()
    .append("td")
    .attr("style", "font-family: Courier") // sets the font style
    .html(function(d) { return d; });
  cells.each(function(p, j) {
    if (j === 1) {
    var id = d3.select(this.previousElementSibling).text();
    d3.select(this).text(null)
    .append("a").attr("href", "javascript:void(0)").html(function (d) {
        return d;})
    .on("click", function(d,i){
      cData.changeExplore(id, 1);
      return false;});
    }
    if (j === 2) {
    var sib = this.previousElementSibling;
    var id = d3.select(sib.previousElementSibling).text();
    d3.select(this).text(null)
    .append("a").attr("href", "javascript:void(0)").html(function (d) {
        return d;})
    .on("click", function(d,i){
      cData.changeExplore(id, 0);
      return false;});
    }
  });
}

function dragStart() {
    var p = d3.mouse(this);
    selectionRect.init(p[0], p[1], d3.select(this));
    selectionRect.removePrevious();
}

function dragMove() {
    var p = d3.mouse(this);
    selectionRect.update(p[0], p[1]);
}

function dragEnd(cData, xMap, yMap) {
  var finalAttributes = selectionRect.getCurrentAttributes();
  if(finalAttributes.x2 - finalAttributes.x1 > 1 && finalAttributes.y2 - finalAttributes.y1 > 1){
    // range selected
    d3.event.sourceEvent.preventDefault();
    selectionRect.focus();
    var x1 = xMap.invert(finalAttributes.x1);
    var x2 = xMap.invert(finalAttributes.x2);
    var y1 = yMap.invert(finalAttributes.y1);
    var y2 = yMap.invert(finalAttributes.y2);
    var res = cData.getKeys(x1, y1, x2, y2);
    displayCorrRes(res, cData);
  }
}

var corrData = { obj : null,
  keys : null,
  url : null,
  columns : null,
  keysSorted : [ null, null],
  init : function(obj, url) {
    this.obj = obj;
    this.url = url;
    var keys = d3.keys(obj);
    this.keys = keys.slice(0);
    this.keysSorted[0] = keys.slice(0).sort(function(a,b){return obj[a][0]-obj[b][0]});
    this.keysSorted[1] = keys.slice(0).sort(function(a,b){return obj[a][1]-obj[b][1]});
  },

  get: function(id) {
    return this.obj[id];
  },

  getC: function(id, i) {
    var str = (+this.obj[id][i]).toPrecision(3);
    return str;
  },
  setColumns: function (c) { this.columns = c; },
  getColumns: function () { return this.columns; },
  binaryIndexOf: function(valueIndex, searchElement) {

    var array = this.keysSorted[valueIndex];
    var minIndex = 0;
    var maxIndex = array.length - 1;
    var currentIndex;
    var currentElement;

    while (minIndex <= maxIndex) {
        currentIndex = (minIndex + maxIndex) / 2 | 0;
        currentElement = this.obj[array[currentIndex]][valueIndex];

        if (currentElement < searchElement) {
            minIndex = currentIndex + 1;
        }
        else if (currentElement > searchElement) {
            maxIndex = currentIndex - 1;
        }
        else {
            return currentIndex;
        }
    }

    return minIndex;
  },

  getKeys : function(x1, y1, x2, y2) {
    var xi1 = this.binaryIndexOf(0, x1);
    var xi2 = this.binaryIndexOf(0, x2);
    var yi1 = this.binaryIndexOf(1, y1);
    var yi2 = this.binaryIndexOf(1, y2);
    if (xi2 < xi1) {
        var tmp = xi1;
        xi1 = xi2;
        xi2 = tmp;
    }
    if (yi2 < yi1) {
        var tmp = yi1;
        yi1 = yi2;
        yi2 = tmp;
    }
    var hh = d3.map();
    var array = this.keysSorted[1];
    for (var i = yi1; i < array.length && i <= yi2; i++) {
      hh.set(array[i], 1);
    }
    array = this.keysSorted[0];
    var res = [];
    for (var i = xi1; i < array.length && i <= xi2; i++) {
      if (hh.has(array[i])) {
         res.push(array[i]);
      }
    }
    return res.reverse();
  },

  changeExplore : function(id, index) {
    var list = this.url.split("?");
    var d = list[1].split('&').reduce(function(s,c){
        var t=c.split('=');s[t[0]]=t[1];return s;},{})
    d["go"] = "plotids";
    var groups = d["groups"];
    delete d["groups"];
    var url = list[0] + "?" + jQuery.param(d);
    d3.json(url, function(data) {
        data[index][0] = id;
        var id1 = data[0][0];
        var id2 = data[1][0];
        var url = list[0] + "?go=getplots&A=" + id1 +
            "&B=" + id2 + "&id=" + d["id"];
        d3.tsv(url, function(data) {
            if (data.columns && data.columns.length === 5) {
                var img0link = data.columns[4];
                var list = img0link.split("?");
                var h = list[1].split('&').reduce(function(s,c){
                  var t=c.split('=');s[t[0]]=t[1];return s;},{})
                h["xn"] = data.columns[1];
                h["yn"] = data.columns[3];
                img0link = list[0] + "?" + jQuery.param(h);
                var img1link = img0link +"&groups="+groups;
                d3.select("#img0link").attr("href", function (p, i) { 
                    return img0link;});
                d3.select("#gInfoX").text(id1 + " : " + h["xn"]);
                d3.select("#gInfoY").text(id2 + " : " + h["yn"]);
                updateImg1linkPost(img1link);
            }
            });
        });
  },
  search : function (val, max) {
    if (val in this.obj) {
      return [val];
    }
    var obj = this.obj;
    var index = this.columns.length - 2;
    var res = [];
    this.keys.forEach(function (e, i, a) {
        if (obj[e][index].search(val) >= 0 && res.length < max) {
            res.push(e);
        }
    });
    return res;
  }

};

function displayGCorr(data, url) {
  try {
  var obj = JSON.parse(data);
  var keys = d3.keys(obj);
  corrData.init(obj, url);
  var columns = ["ID", "corrX", "corrY", "n1", "n2", "Name"];
  corrData.setColumns(columns);
  var mn = {t: 20, r: 20, b: 20, l: 30},
    width = 300, height = 150;
  // add the graph canvas to the body of the webpage
  d3.select("#lineresults").html("")
  var svg = d3.select("#lineresults").append("svg")
    .attr("width", width).attr("height", height);
  var tablesearch = d3.select("#lineresults").append("input")
    .attr("type", "text").attr("size", 10).attr("id", "tablesearch")
    .attr("value", "");
  var tablego = d3.select("#lineresults").append("input")
    .attr("type", "button").attr("name", "GO")
    .attr("value", "GO").on("click", function () {
        var val = document.getElementById("tablesearch").value;
        var res = corrData.search(val, 100);
        displayCorrRes(res, corrData);
    });
  var tablenum = d3.select("#lineresults").append("div")
    .attr("id", "tablenum");
  var table = d3.select("#lineresults").append("table")
    .attr("id", "tableresults").attr("border", 0);
  var x = d3.scaleLinear().domain([-1, 1]).range([mn.l, width-mn.r]),
      y = d3.scaleLinear().domain([-1, 1]).range([height-mn.b, mn.t]);
  x.clamp(true);
  y.clamp(true);
  var xAxis = d3.axisBottom(x).ticks(8),
      yAxis = d3.axisLeft(y).ticks(5);
  var gx = svg.append("g")
    .attr("class", "axis axis--x")
    .attr("transform", "translate("+ 0 +"," + (height - mn.b) + ")")
    .call(xAxis);
  var gy = svg.append("g")
    .attr("class", "axis axis--y")
    .attr("transform", "translate("+ mn.l +"," + 0 + ")")
    .call(yAxis);
  var xMap = function(d) { return x(obj[d][0]);}
  var yMap = function(d) { return y(obj[d][1]);}
  svg.selectAll(".dot")
    .data(keys)
    .enter().append("circle")
    .attr("class", "dot")
    .attr("r", 1)
    .attr("cx", xMap)
    .attr("cy", yMap)
    .style("fill", "#555");
  var dragBehavior = d3.drag()
    .on("drag", dragMove)
    .on("start", dragStart)
    .on("end", function() { 
        dragEnd(corrData, x, y);
    });
  svg.call(dragBehavior);

  } catch(e) { }
}

function getLogVal(x, maxx) {
  if (x <= 0) {
    return maxx;
  }
  else {
    return Math.min(maxx, -Math.log10(x));
  }
}

function displayGDiff(data, url) {
  try {
  var obj = JSON.parse(data);
  var keys = d3.keys(obj);
  keysSorted = keys.slice(0).sort(function(a,b){return obj[a][1]-obj[b][1]});
  var i = 0;
  for (i =0; i < keys.length; i++) {
    if (obj[keysSorted[i]][1] != 0) {
        break;
    }
  }
  var maxy = -Math.log10(obj[keysSorted[i]][1]) * 1.05;
  keys.forEach(function (e, i, a) {
        obj[e][1] = getLogVal(obj[e][1], maxy);
    });
  corrData.init(obj, url);
  var columns = ["ID", "Diff", "-log10(p)", "n1" , "n2", "Name"];
  corrData.setColumns(columns);
  var mn = {t: 20, r: 20, b: 20, l: 30},
    width = 300, height = 150;
  // add the graph canvas to the body of the webpage
  d3.select("#lineresults").html("")
  var svg = d3.select("#lineresults").append("svg")
    .attr("width", width).attr("height", height);
  var tablesearch = d3.select("#lineresults").append("input")
    .attr("type", "text").attr("size", 10).attr("id", "tablesearch")
    .attr("value", "");
  var tablego = d3.select("#lineresults").append("input")
    .attr("type", "button").attr("name", "GO")
    .attr("value", "GO").on("click", function () {
        var val = document.getElementById("tablesearch").value;
        var res = corrData.search(val, 100);
        displayCorrRes(res, corrData);
    });
  var tablenum = d3.select("#lineresults").append("div")
    .attr("id", "tablenum");
  var table = d3.select("#lineresults").append("table")
    .attr("id", "tableresults").attr("border", 0);
  var minx = d3.min(keys, function(d) { return +obj[d][0]; });
  var maxx = d3.max(keys, function(d) { return +obj[d][0]; });
  var max = d3.max([-minx, maxx]) * 1.1;
  var miny = d3.min(keys, function(d) { return +obj[d][1]; });
  var maxy = d3.max(keys, function(d) { return +obj[d][1]; }) * 1.05;
  var x = d3.scaleLinear().domain([-max, max]).range([mn.l, width-mn.r]),
      y = d3.scaleLinear().domain([miny, maxy]).range([height-mn.b, mn.t]);
  x.clamp(true);
  y.clamp(true);
  var xAxis = d3.axisBottom(x).ticks(4),
      yAxis = d3.axisLeft(y).ticks(4);
  var gx = svg.append("g")
    .attr("class", "axis axis--x")
    .attr("transform", "translate("+ 0 +"," + (height - mn.b) + ")")
    .call(xAxis);
  var gy = svg.append("g")
    .attr("class", "axis axis--y")
    .attr("transform", "translate("+ mn.l +"," + 0 + ")")
    .call(yAxis);
  var xMap = function(d) { return x(obj[d][0]);}
  var yMap = function(d) { return y(obj[d][1]);}
  svg.selectAll(".dot")
    .data(keys)
    .enter().append("circle")
    .attr("class", "dot")
    .attr("r", 1)
    .attr("cx", xMap)
    .attr("cy", yMap)
    .style("fill", "#555");
  var pvalueThresholdBonferroni = 1.3 + Math.log10(keys.length);
  svg.append("svg:line").attr("x1", mn.l).attr("x2", width-mn.r).
    attr("y1", y(pvalueThresholdBonferroni))
    .attr("y2", y(pvalueThresholdBonferroni))
    .style("stroke", "#e5e");
  svg.append("svg:line").attr("x1", mn.l).attr("x2", width-mn.r).
    attr("y1", y(1.3)).attr("y2", y(1.3))
    .style("stroke", "#5e5");
  svg.append("svg:line").attr("x1", x(1)).attr("x2", x(1)).
    attr("y1", mn.t).attr("y2", height-mn.b)
    .style("stroke", "#e55");
  svg.append("svg:line").attr("x1", x(-1)).attr("x2", x(-1)).
    attr("y1", mn.t).attr("y2", height-mn.b)
    .style("stroke", "#e55");

  var dragBehavior = d3.drag()
    .on("drag", dragMove)
    .on("start", dragStart)
    .on("end", function() { 
        dragEnd(corrData, x, y);
    });
  svg.call(dragBehavior);

  } catch(e) { }
}

function callGCorr() {
  var url = document.getElementById('img0link').href;
  url = url.replace("go=plot", "go=getgcorr");
  url += '&groups=' + getGroupStr();
  var list = url.split("?");
  var d = list[1].split('&').reduce(function(s,c){
      var t=c.split('=');s[t[0]]=t[1];return s;},{});
  $.ajax({type: 'POST',
      data: d,
      url: list[0],
      success: function (data) { return displayGCorr(data, url);}});
  $('#lineresults').css("visibility", "visible");
}

function callGDiff() {
  var url = document.getElementById('img0link').href;
  url = url.replace("go=plot", "go=getgdiff");
  url += '&groups=' + getGroupStr();
  var list = url.split("?");
  var d = list[1].split('&').reduce(function(s,c){
      var t=c.split('=');s[t[0]]=t[1];return s;},{});
  $.ajax({type: 'POST',
      data: d,
      url: list[0],
      success: function (data) { return displayGDiff(data, url);}});
  $('#lineresults').css("visibility", "visible");
}

function callDownload() {
  var str1 = escape(document.getElementById('CT').value);
  var url = document.getElementById('img0link').href;
  url += '&CT=' + str1 + '&groups=' + getGroupStr() + "&param=type:pdf";
  url = url.replace("go=plot", "go=download");
  var list = url.split("?");
  var d = list[1].split('&').reduce(function(s,c){
      var t=c.split('=');s[t[0]]=t[1];return s;},{});
  var urlAction = list[0];
  var data = d;
  var $form = $('<form target="_blank" method="POST" action="' + urlAction + '">');
  $.each(data, function(k,v){
    $form.append('<input type="hidden" name="' + k + '" value="' + v + '">');
  });
  $(document.body).append($form);
  $form.submit();
}

function callSearch() {
  if (searchGroup == 0) {
    searchGroup = 1;
  }
  else {
    searchGroup = 0;
  }
  updateGroupDisplay();
  return false;
}

function callSearchGroup() {
  var url = document.getElementById('img0link').href;
  url += '&groups=' + getGroupStr();
  var str1 = escape(document.getElementById('clinical0').value);
  url += '&clinical=' + str1;
  url = url.replace("go=plot", "go=sga");
  var list = url.split("?");
  var d = list[1].split('&').reduce(function(s,c){
      var t=c.split('=');s[t[0]]=t[1];return s;},{});
  var str = $("#searchGroupArea").val();
  d['sga'] = str;
  $.ajax({type: 'POST',
      data: d,
      url: list[0],
      success: function (data) {
        if (str == "") {
          $("#searchGroupArea").val(data);
        }
        else {
          var list2 = data.split("\n");
          if (list2[0] > 0) {
            if (str.startsWith("Boolean")) {
              addGroups(createGroup(list2), 1);
            }
            else {
              addGroups(createGroup(list2));
            }
          }
          updateGroupDisplay();
        }
        }});
  return false;
}

