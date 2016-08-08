
function falsefunc() { return false; } // used to block cascading events

function getOffsetSum(elem) {
  var top=0, left=0;

  while(elem) {
    top = top + parseInt(elem.offsetTop);
    left = left + parseInt(elem.offsetLeft);
    elem = elem.offsetParent;
  }
   
  return {top: top, left: left};
}

function getOffsetRect(elem) {
    // (1)
    var box = elem.getBoundingClientRect();
    
    var body = document.body;
    var docElem = document.documentElement;
    
    // (2)
    var scrollTop = window.pageYOffset || docElem.scrollTop || body.scrollTop;
    var scrollLeft = window.pageXOffset || docElem.scrollLeft || body.scrollLeft;
    
    // (3)
    var clientTop = docElem.clientTop || body.clientTop || 0;
    var clientLeft = docElem.clientLeft || body.clientLeft || 0;
    
    // (4)
    var top  = box.top +  scrollTop - clientTop;
    var left = box.left + scrollLeft - clientLeft;
    
    return { top: Math.round(top), left: Math.round(left) };
}

function getOffset(elem) {
    if (elem.getBoundingClientRect) {
        return getOffsetRect(elem);
    } else { // old browser
        return getOffsetSum(elem);
    }
}

var mouse = {
    x: 0,
    y: 0,
    startX: 0,
    startY: 0,
    getWidth: function() {
      return Math.abs(this.x - this.startX);
    },
    getHeight: function() {
      return Math.abs(this.y - this.startY);
    },
    getTop: function() {
      return (this.y < this.startY) ? this.y : this.startY;
    },
    getLeft: function() {
      return (this.x < this.startX) ? this.x : this.startX;
    },
  };

function getMouseXY(e) { 
  e = e || window.event;
  if ( e.pageX == null && e.clientX != null ) {
    var html = document.documentElement;
    var body = document.body;
    e.pageX = e.clientX + 
      (html && html.scrollLeft || body && body.scrollLeft || 0) -
      (html.clientLeft || 0);
    e.pageY = e.clientY + 
      (html && html.scrollTop || body && body.scrollTop || 0) -
      (html.clientTop || 0);
  }
  mouse.x = e.pageX;
  mouse.y = e.pageY;
};


function initDraw(canvas, loadDisplay) {
  //canvas.onselectstart=falsefunc;
  canvas.ondragstart=falsefunc;
  //document.onmousedown = falsefunc;

  var rectobj = document.getElementById('rect');
  var imgobj = document.getElementById('img0');

  canvas.onmousedown = function (e) {
    getMouseXY(e);
    if (rectobj !== null) {
      mouse.startX = mouse.x;
      mouse.startY = mouse.y;
      rectobj.style.width = 0 + 'px';
      rectobj.style.height = 0 + 'px';
      rectobj.style.left = mouse.getLeft() + 'px';
      rectobj.style.top = mouse.getTop() + 'px';
      rectobj.style.zIndex = 10;
      rectobj.style.visibility="visible";
    }

    canvas.onmousemove = function (e) {
      getMouseXY(e);
      if (rectobj !== null) {
        rectobj.style.width = mouse.getWidth() + 'px';
        rectobj.style.height = mouse.getHeight() + 'px';
        rectobj.style.left = mouse.getLeft() + 'px';
        rectobj.style.top = mouse.getTop() + 'px';
      }
      return false;
    };

    document.onmouseup = function (e) {
      if (rectobj !== null) {
        rectobj.style.zIndex = 0;
        rectobj.style.visibility="hidden";
        rectobj.style.width = 0 + "px";
        rectobj.style.height = 0 + "px";
        canvas.onmousemove = null;
        document.onmouseup = null;
        if (imgobj != null) {
          loadDisplay(mouse, getOffset(imgobj));
        }
      }
      return false;
    };

    return false;
  };

}

