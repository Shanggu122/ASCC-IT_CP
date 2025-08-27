<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Video Call — {{ $channel }}</title>

  <!-- Boxicons for Icons -->
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

  <style>
    body, html {
      margin: 0;
      padding: 0;
      height: 100%;
      background-color: #0B3D3C;
      color: white;
      font-family: 'Arial', sans-serif;
    }

    #videos {
      display: flex;
      justify-content: center;
      align-items: center;
      height: calc(100vh - 130px);
      background-color: #1a1a1a;
      gap: 1rem;
    }

    .video-player {
      width: 45vw;
      height: 80vh;
      background-color: black;
      border-radius: 10px;
      overflow: hidden;
      position: relative;
    }

    #controls-panel {
      position: fixed;
      bottom: 20px;
      left: 50%;
      transform: translateX(-50%);
      display: flex;
      gap: 1.5rem;
      background: #12372a;
      padding: 10px 20px;
      border-radius: 40px;
    }

    #controls-panel button {
      display: flex;
      flex-direction: column;
      align-items: center;
      font-size: 13px;
      background-color: transparent;
      color: white;
      border: none;
      cursor: pointer;
    }

    #controls-panel button i {
      font-size: 24px;
      margin-bottom: 4px;
    }

    #leave-btn {
      background-color: #e63946;
      padding: 8px 20px;
      border-radius: 30px;
      font-size: 14px;
      border: none;
      color: white;
      cursor: pointer;
    }

    .status-icon {
      position: absolute;
      top: 10px;
      left: 10px;
      background: rgba(0, 0, 0, 0.7);
      padding: 5px 10px;
      border-radius: 20px;
      font-size: 13px;
      display: flex;
      align-items: center;
      gap: 5px;
    }

    .status-icon i {
      font-size: 16px;
    }

    .hidden {
      display: none;
    }
  </style>

  <script src="https://download.agora.io/sdk/release/AgoraRTC_N.js"></script>
</head>
<body>

  <div id="videos">
    <div id="local-player" class="video-player">
      <div id="local-status" class="status-icon hidden"><i class='bx bxs-microphone-off'></i> Mic Off</div>
    </div>
    <div id="remote-player" class="video-player">
      <div id="remote-status" class="status-icon hidden"><i class='bx bxs-video-off'></i> Cam Off</div>
    </div>
  </div>

  <div id="controls-panel">
    <button id="toggle-mic"><i class='bx bxs-microphone'></i>Mute</button>
    <button id="toggle-cam"><i class='bx bxs-video'></i>Video</button>
    <button id="leave-btn"><i class='bx bx-phone-off'></i>Leave</button>
  </div>

  <script src="{{ asset('js/video-call.js') }}"></script>

</body>
</html>
