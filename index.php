<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>QR Attendance System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://unpkg.com/html5-qrcode"></script>
  <style>
    :root {
      --primary-color: #0B59A4;
    }
    .btn-primary {
      background-color: var(--primary-color);
      border-color: var(--primary-color);
    }
    .btn-primary:hover {
      background-color: #094a89;
      border-color: #094a89;
    }
    .card-option {
      cursor: pointer;
      transition: transform 0.3s ease;
    }
    .card-option:hover {
      transform: scale(1.05);
    }
    .hidden { display: none; }
    .card-option .bi {
      font-size: 2rem;
      color: var(--primary-color);
      margin-bottom: 0.5rem;
    }
  </style>
</head>
<body class="bg-light">
  <div class="container py-5">
    <div class="text-center mb-4">
      <h2 style="color: #0B59A4;">QR Attendance System</h2>
      <p class="lead">Choose your attendance method</p>
      <div class="btn-group" role="group" aria-label="Check In/Out">
        <button class="btn btn-primary" onclick="setMode('checkin')"><i class="bi bi-box-arrow-in-right"></i> Check In</button>
        <button class="btn btn-outline-primary" onclick="setMode('checkout')"><i class="bi bi-box-arrow-left"></i> Check Out</button>
      </div>
    </div>

    <div class="row g-4 justify-content-center">
      <div class="col-md-5">
        <div class="card card-option text-center shadow-sm" onclick="showMethod('qr')">
          <div class="card-body">
            <i class="bi bi-qr-code-scan"></i>
            <h5 class="card-title">Scan QR Code</h5>
            <p class="card-text">Use your camera to scan your staff QR code.</p>
            <button class="btn btn-outline-primary btn-sm"><i class="bi bi-camera-video"></i> Start Scanner</button>
          </div>
        </div>
      </div>

      <div class="col-md-5">
        <div class="card card-option text-center shadow-sm" onclick="showMethod('manual')">
          <div class="card-body">
            <i class="bi bi-keyboard"></i>
            <h5 class="card-title">Enter Staff Number</h5>
            <p class="card-text">Type your staff number to log attendance manually.</p>
            <button class="btn btn-outline-primary btn-sm"><i class="bi bi-person-check"></i> Enter Number</button>
          </div>
        </div>
      </div>
    </div>

    <div id="qr-section" class="mt-5 hidden">
      <h4 class="mb-3 text-center">QR Code Scanner</h4>
      <div class="d-flex justify-content-center">
        <div id="reader" style="width: 300px;"></div>
      </div>
    </div>

    <div id="manual-section" class="mt-5 hidden">
      <h4 class="mb-3 text-center">Manual Attendance</h4>
      <form id="fallbackForm" class="w-100 d-flex flex-column align-items-center" onsubmit="submitFallback(event)">
        <div class="mb-3 w-50">
          <input type="text" class="form-control" id="staff_no" placeholder="Enter your staff number" required>
        </div>
        <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check-circle"></i> Submit</button>
      </form>
    </div>

    <div id="result" class="text-center mt-4 fw-bold"></div>
  </div>

  <script>
    let qrScanner = null;
    let currentMode = 'checkin'; // default mode

    function setMode(mode) {
      currentMode = mode;
      document.querySelectorAll(".btn-group .btn").forEach(btn => btn.classList.remove('btn-primary'));
      document.querySelectorAll(".btn-group .btn").forEach(btn => btn.classList.add('btn-outline-primary'));
      document.querySelector(`.btn-group .btn[onclick="setMode('${mode}')"]`).classList.remove('btn-outline-primary');
      document.querySelector(`.btn-group .btn[onclick="setMode('${mode}')"]`).classList.add('btn-primary');
    }

    function showMethod(method) {
      document.getElementById('qr-section').classList.add('hidden');
      document.getElementById('manual-section').classList.add('hidden');
      document.getElementById('result').textContent = '';

      const scannerDiv = document.getElementById("reader");
      if (qrScanner) {
        qrScanner.clear().then(() => {
          scannerDiv.innerHTML = "";
        }).catch(err => {
          console.warn("Failed to clear scanner:", err);
        });
      }

      if (method === 'qr') {
        document.getElementById('qr-section').classList.remove('hidden');
        qrScanner = new Html5QrcodeScanner("reader", { fps: 10, qrbox: 250 });
        qrScanner.render(onScanSuccess);
      } else {
        document.getElementById('manual-section').classList.remove('hidden');
      }
    }

    function logResult(message, success = true) {
      const res = document.getElementById("result");
      res.innerHTML = message;
      res.style.color = success ? "green" : "red";
    }

    function submitFallback(e) {
      e.preventDefault();
      const staffNo = document.getElementById("staff_no").value;
      fetch('api/attendance.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ mode: 'manual', staff_no: staffNo, action: currentMode })
      })
      .then(res => res.json())
      .then(data => logResult(data.message, data.status === "success"));
    }

    function onScanSuccess(decodedText) {
      fetch('api/attendance.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ mode: 'qr', code: decodedText, action: currentMode })
      })
      .then(res => res.json())
      .then(data => logResult(data.message, data.status === "success"));
    }
  </script>
</body>
</html>
