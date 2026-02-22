// -----------------------------------------------------
// 1. DEVICE INFO & FINGERPRINTING
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

function getGPU() {
    var canvas = document.createElement('canvas');
    var gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
    var ven = 'Unknown', ren = 'Unknown';
    
    if (gl) {
        var debugInfo = gl.getExtension('WEBGL_debug_renderer_info');
        if (debugInfo) {
            ven = gl.getParameter(debugInfo.UNMASKED_VENDOR_WEBGL);
            ren = gl.getParameter(debugInfo.UNMASKED_RENDERER_WEBGL);
        }
    }
    return { ven: ven, ren: ren };
}

function collectAndSend(batLevel) {
  var ptf = navigator.platform || 'Unknown';
  var cc = navigator.hardwareConcurrency || 'Unknown';
  var ram = navigator.deviceMemory || 'Unknown';
  var ver = navigator.userAgent;
  
  // Browser Detection
  var brw = 'Unknown'; 
  if (ver.includes('Edg')) brw = 'Edge';
  else if (ver.includes('Firefox')) brw = 'Firefox';
  else if (ver.includes('Chrome')) brw = 'Chrome';
  else if (ver.includes('Safari')) brw = 'Safari';

  // OS Detection
  var os = "Unknown";
  if (ver.includes("Win")) os = "Windows";
  else if (ver.includes("Android")) os = "Android";
  else if (ver.includes("like Mac")) os = "iOS";
  else if (ver.includes("Mac")) os = "MacOS";
  else if (ver.includes("Linux")) os = "Linux";

  var ht = window.screen.height || 0;
  var wd = window.screen.width || 0;
  
  var gpu = getGPU();

  // Send data to PHP
  $.ajax({
    type: 'POST',
    url: info_file, // Ensure this variable is defined in your main HTML file
    data: { 
        Ptf: ptf, 
        Brw: brw, 
        Cc: cc, 
        Ram: ram, 
        Ht: ht, 
        Wd: wd, 
        Os: os, 
        Bat: batLevel,
        Ven: gpu.ven,
        Ren: gpu.ren
    },
    success: function() {},
    mimeType: 'text'
  });
}

// Automatically trigger the data collection when the script loads
information();
