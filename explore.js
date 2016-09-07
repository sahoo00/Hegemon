function GetXmlHttpObject()
{
  var xmlHttp=null;
  try
  {
    // Firefox, Opera 8.0+, Safari
    xmlHttp=new XMLHttpRequest();
  }
  catch (e)
  {
    // Internet Explorer
    try
    {
      xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");
    }
    catch (e)
    {
      xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
    }
  }
  return xmlHttp;
}

//Our XmlHttpRequest object to get the auto suggest
var xmlHttp = GetXmlHttpObject();

function getDatasetID(name) {
  return document.getElementById(name).value;
} 

function callGetPlots() {
  bindGetPlots('getplots', handleGetPlot);
}

function bindGetPlots(cmd, fun) {
  if (xmlHttp.readyState == 4 || xmlHttp.readyState == 0) {
    var ss = document.getElementById('results');
    ss.innerHTML ='Please Wait ... <br/><img width=70 src="Images/loading.gif"/>' ;
    ss.style.visibility = 'visible';
    var str1 = escape(document.getElementById('Ab').value);
    var str2 = escape(document.getElementById('Bb').value);
    var s1 = escape(getDatasetID('dataset'));
    if ( !s1 ) {
      ss.innerHTML = 'Select a dataset';
      return;
    }
    if ( s1 && (str1 == null || str2 == null || str1 == "" || str2 == "")) {
      ss.innerHTML = 'Enter gene names in A and B';
      return;
    }
    var url = 'explore.php?go=' + cmd + '&A=' + str1 + '&B=' + str2;
    url += '&id=' + s1;
    xmlHttp.open("GET", url, true);
    xmlHttp.onreadystatechange = fun; 
    xmlHttp.send(null);
  }		
}
function handleGetPlot() {
  if (xmlHttp.readyState == 4) {
    var ss = document.getElementById('results');
    ss.innerHTML = '';
    ss.style.visibility = 'visible';
    var str = xmlHttp.responseText.split("\n");
    if (str.length < 2) {
      ss.style.visibility = 'hidden';
    }
    var patt=/^Error/i;
    if (patt.test(str[0])) {
      str[0] += '<br/> Click getPlots and then click on a plot to select probeset id';
    }
    var plots = '<table border=0> <tr>';
    for(i=0; i < str.length - 1; i++) {
      var list = str[i].split("\t");
      var box = '<td>' ;
      box += list[1] + ' and ' + list[3] ; 
      box += '(' + list[0] + ',' + list[2] + ',' + i + ') ';
      box += '<a href="' + list[4] + '"> p</a>  ';
      box += '<br/>';
      box += '<img height=240 width=320 src="' + list[4] + '"  ';
      box += ' onclick="updateTextBox(\'' + list[0] + '\',\'' + list[1] + '\',\'' + list[2] + '\',\'' + list[3] + '\');"/>';
      box += '</td>';
      if ( (i % 2) == 1 ) {
        box += '</tr><tr>';
      }
      plots += box;
    }
    plots += '</tr></table>';
    ss.innerHTML += plots;
  }
}
function updateTextBox(v1, n1, v2, n2) {
  document.getElementById('Ab').value = v1;
  document.getElementById('Bb').value = v2;
}

function callGetStats() {
  if (xmlHttp.readyState == 4 || xmlHttp.readyState == 0) {
    $("#results").html('Please Wait ... <br/><img width=70 src="Images/loading.gif"/>');
    $('#results').css("visibility", "visible");
    var s1 = $('#dataset').val();
    var str1 = $('#Ab').val();
    var str2 = $('#Bb').val();
    var sthr = $.trim($('#sthr').val());
    var pthr = $.trim($('#pthr').val());
    if (/^[\d\.]+$/.test(sthr) == false)
      sthr=3;
    if (/^[\d\.]+$/.test(pthr) == false)
      pthr=0.1;
    var url = 'explore.php?go=getstats&id=' + s1 + '&sthr=' + sthr;
    url += '&pthr=' + pthr + "&A=" + encodeURIComponent(str1)
    + "&B=" + encodeURIComponent(str2);
    $('#results').load(url);
  }
}

function callTopGenes() {
  if (xmlHttp.readyState == 4 || xmlHttp.readyState == 0) {
    $("#results").html('Please Wait ... <br/><img width=70 src="Images/loading.gif"/>');
    $('#results').css("visibility", "visible");
    var s1 = $('#dataset').val();
    var num = $.trim($('#arg1').val());
    if (/^[\d]+$/.test(num) == false)
      num=10;
    var url = 'explore.php?go=topgenes&id=' + s1 + '&num=' + num;
    $('#results').load(url);
  }
}

function callMiDReG() {
  if (xmlHttp.readyState == 4 || xmlHttp.readyState == 0) {
    $("#results").html('Please Wait ... <br/><img width=70 src="Images/loading.gif"/>');
    $('#results').css("visibility", "visible");
    var s1 = $('#dataset').val();
    var str1 = $('#Ab').val();
    var str2 = $('#Bb').val();
    var sthr = $.trim($('#sthr').val());
    var pthr = $.trim($('#pthr').val());
    if (/^[\d\.]+$/.test(sthr) == false)
      sthr=3;
    if (/^[\d\.]+$/.test(pthr) == false)
      pthr=0.1;
    var url = 'explore.php?go=midreg&id=' + s1 + '&sthr=' + sthr;
    url += '&pthr=' + pthr + "&A=" + encodeURIComponent(str1)
    + "&B=" + encodeURIComponent(str2);
    $('#results').load(url);
  }
}

function callExplore() {
  if (xmlHttp.readyState == 4 || xmlHttp.readyState == 0) {
    $("#results").html('Please Wait ... <br/><img width=70 src="Images/loading.gif"/>');
    $('#results').css("visibility", "visible");
    var s1 = $('#dataset').val();
    var str1 = $('#Ab').val();
    var str2 = $('#Bb').val();
    var url = 'explore.php?go=explore&id=' + s1;
    url += "&A=" + encodeURIComponent(str1)
    + "&B=" + encodeURIComponent(str2);
    $('#results').load(url);
  }
}

function callCorr() {
  if (xmlHttp.readyState == 4 || xmlHttp.readyState == 0) {
    $("#results").html('Please Wait ... <br/><img width=70 src="Images/loading.gif"/>');
    $('#results').css("visibility", "visible");
    var s1 = $('#dataset').val();
    var str1 = $('#Ab').val();
    var str2 = $('#Bb').val();
    var sthr = $.trim($('#sthr').val());
    var pthr = $.trim($('#pthr').val());
    if (/^[\d\.]+$/.test(sthr) == false)
      sthr=3;
    if (/^[\d\.]+$/.test(pthr) == false)
      pthr=0.1;
    var url = 'explore.php?go=getcorr&id=' + s1 + '&sthr=' + sthr;
    url += '&pthr=' + pthr + "&A=" + encodeURIComponent(str1)
    + "&B=" + encodeURIComponent(str2);
    $('#results').load(url);
  }
}

function callGetIDs() {
  if (xmlHttp.readyState == 4 || xmlHttp.readyState == 0) {
    $("#results").html('Please Wait ... <br/><img width=70 src="Images/loading.gif"/>');
    $('#results').css("visibility", "visible");
    var s1 = $('#dataset').val();
    var str1 = $('#Ab').val();
    var str2 = $('#Bb').val();
    var url = 'explore.php?go=getids&id=' + s1;
    url += "&A=" + encodeURIComponent(str1)
    + "&B=" + encodeURIComponent(str2);
    $('#results').load(url);
  }
}

