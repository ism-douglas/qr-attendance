
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>QR Attendance System</title>
  <script src="https://unpkg.com/html5-qrcode"></script>
</head>
<body>
  <h2>Scan QR Code</h2>
  <div id="reader" style="width: 300px;"></div>
  <hr>
  <h3>Fallback: Enter Staff Number</h3>
  <form id="fallbackForm" onsubmit="submitFallback(event)">
    <input type="text" id="staff_no" placeholder="Enter Staff No" required>
    <button type="submit">Submit</button>
  </form>
  <div id="result"></div>

  <script>
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
        body: JSON.stringify({ mode: 'manual', staff_no: staffNo })
      })
      .then(res => res.json())
      .then(data => logResult(data.message, data.status === "success"));
    }

    function onScanSuccess(decodedText) {
      fetch('api/attendance.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ mode: 'qr', code: decodedText })
      })
      .then(res => res.json())
      .then(data => logResult(data.message, data.status === "success"));
    }

    new Html5QrcodeScanner("reader", { fps: 10, qrbox: 250 }).render(onScanSuccess);
  </script>
</body>
</html>
