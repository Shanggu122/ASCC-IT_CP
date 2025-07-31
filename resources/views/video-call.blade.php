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

  <script>
    const APP_ID   = 'ab155f23c3fc4ae980b11973d818c460';
    const TOKEN    = '007eJxTYLBnr1pkoWH5+YvVq5O1WZueaJ4MO+7vN/N0/qK/F5xaNNUUGBKTDE1N04yMk43Tkk0SUy0tDJIMDS3NjVMsDC2STcwMFh7WyGgIZGSYcaqAgREKQXx2hpLU4hJDI2MGBgDmpyDn';
    const CHANNEL  = 'test123';

    let client, localAudioTrack, localVideoTrack;
    let micMuted = false;
    let camOff = false;

    const localContainer   = document.getElementById('local-player');
    const remoteContainer  = document.getElementById('remote-player');
    const micBtn           = document.getElementById('toggle-mic');
    const camBtn           = document.getElementById('toggle-cam');
    const leaveBtn         = document.getElementById('leave-btn');
    const localStatus      = document.getElementById('local-status');
    const remoteStatus     = document.getElementById('remote-status');

    window.addEventListener('DOMContentLoaded', async () => {
      try {
        client = AgoraRTC.createClient({ mode: 'rtc', codec: 'vp8' });
        client.on('user-published', handleUserPublished);
        client.on('user-unpublished', handleUserUnpublished);

        await client.join(APP_ID, CHANNEL, TOKEN, null);
        [localAudioTrack, localVideoTrack] = await AgoraRTC.createMicrophoneAndCameraTracks();

        localVideoTrack.play(localContainer);
        await client.publish([localAudioTrack, localVideoTrack]);

        console.log("Connected to channel:", CHANNEL);
      } catch (err) {
        console.error("Agora connection failed:", err);
        alert("Connection failed. Check APP_ID/TOKEN.");
      }
    });

    micBtn.addEventListener('click', () => {
      if (!localAudioTrack) return;
      micMuted = !micMuted;
      localAudioTrack.setEnabled(!micMuted);
      micBtn.innerHTML = micMuted ? "<i class='bx bxs-microphone-off'></i>Unmute" : "<i class='bx bxs-microphone'></i>Mute";
      localStatus.classList.toggle('hidden', !micMuted);
    });

    camBtn.addEventListener('click', () => {
      if (!localVideoTrack) return;
      camOff = !camOff;
      localVideoTrack.setEnabled(!camOff);
      camBtn.innerHTML = camOff ? "<i class='bx bxs-video-off'></i>Show" : "<i class='bx bxs-video'></i>Video";
      // Optional: Update status icon here if desired
    });

    leaveBtn.addEventListener('click', async () => {
      if (localAudioTrack) localAudioTrack.close();
      if (localVideoTrack) localVideoTrack.close();
      await client.leave();
      window.location.href = '/messages';
    });

    async function handleUserPublished(user, mediaType) {
      await client.subscribe(user, mediaType);
      if (mediaType === 'video') {
        user.videoTrack.play(remoteContainer);
        remoteStatus.classList.add('hidden');
      }
      if (mediaType === 'audio') {
        user.audioTrack.play();
      }
    }

    function handleUserUnpublished(user) {
      remoteContainer.innerHTML = "";
      remoteContainer.appendChild(remoteStatus);
      remoteStatus.classList.remove('hidden');
    }
  </script>

</body>
</html>
