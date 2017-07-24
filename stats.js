
function updateResults(data) {
  $('#results').html(data);
}

function callGeneric(cmd) {
  $("#results").html('Please Wait ... <br/><img width=70 src="Images/loading.gif"/>');
  $('#results').css("visibility", "visible");
  var str1 = $('#Ab').val();
  var str2 = $('#Bb').val();
  var params = $('#params').val();
  var d = { go: cmd, A: encodeURIComponent(str1),
    B: encodeURIComponent(str2),
    params: encodeURIComponent(params)};
  $.ajax({type: 'POST',
      data: d,
      url: "stats.php",
      success: function (data) { return updateResults(data);}});
}

function callStepMiner() {
  callGeneric("StepMiner");
}

function callHistogram() {
  callGeneric("hist");
}

function callIntersect() {
  callGeneric("intersect");
}

function callUnion() {
  callGeneric("union");
}

function callDiffAB() {
  callGeneric("diffab");
}

function callDiffBA() {
  callGeneric("diffba");
}

