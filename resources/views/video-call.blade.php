<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Video Meeting — {{ $channel ?? 'Room' }}</title>

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

    .layout {
      display: grid;
      grid-template-columns: 1fr 340px;
      grid-template-rows: 56px 1fr;
      grid-template-areas: "topbar topbar" "stage sidebar";
      height: 100vh;
    }

    /* Top bar */
  .topbar {
      grid-area: topbar;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 16px;
      border-bottom: 1px solid rgba(255,255,255,0.08);
      background: #0F4947;
    }
    .topbar .title { font-weight: 600; }
  .icon-btn {
      display: inline-flex; align-items: center; gap: 6px;
      padding: 6px 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.12);
      background: #12372a; color: #fff; cursor: pointer; font-size: 13px;
    }
    .icon-btn i{ font-size: 18px; }

  /* Stage fills screen; remote full, local PiP */
  #stage { grid-area: stage; background-color: #111; position: relative; overflow: hidden; }
  .video-player { background-color: black; border-radius: 10px; overflow: hidden; position: absolute; }
  #remote-player.video-player { inset: 0 0 0 0; border-radius: 0; z-index: 1; }
  #local-player.video-player { right: 16px; bottom: 80px; width: 280px; height: 158px; box-shadow: 0 6px 20px rgba(0,0,0,0.4); z-index: 3; }

  /* Bottom control bar like Zoom */
  #controls-panel { position: absolute; left: 0; right: 0; bottom: 0; display: grid; grid-template-columns: 1fr auto 1fr; align-items: center; background: rgba(0,0,0,0.88); height: 64px; border-top: 1px solid rgba(255,255,255,0.08); z-index: 50; }
  .controls-center { display:flex; align-items:center; justify-content:center; gap: 12px; }
  .controls-right { display:flex; justify-content:flex-end; padding-right: 16px; }
  #controls-panel .ctrl { display: inline-flex; align-items: center; gap: 0; position: relative; }
  #controls-panel .ctrl .ctrl-btn { display: flex; flex-direction: column; align-items: center; gap: 3px; width: 84px; cursor: pointer; border: none; background: transparent; color:#fff; padding-right: 24px; position: relative; }
  #controls-panel .ctrl .ctrl-btn i { font-size: 22px; }
  #controls-panel .caret { border: none; background: rgba(255,255,255,0.08); color:#fff; cursor: pointer; font-size: 16px; width: 22px; height: 22px; border-radius: 6px; display:inline-flex; align-items:center; justify-content:center; position:absolute; right: 6px; top: 6px; z-index: 5; }
  #controls-panel .icon-btn { display:flex; flex-direction: column; align-items:center; gap:3px; width: 100px; background:transparent; border:none; color:#fff; cursor:pointer; }
  #controls-panel .icon-btn i{ font-size:22px; }
  #leave-btn { background-color: #e63946; padding: 8px 16px; border-radius: 8px; font-size: 14px; border: none; color: white; cursor: pointer; }

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

    /* Sidebar */
    .sidebar {
      grid-area: sidebar;
      display: flex; flex-direction: column;
      background: #0f3432; border-left: 1px solid rgba(255,255,255,0.08);
    }
  /* Collapse sidebar to full-width stage */
  .layout.no-sidebar { grid-template-columns: 1fr 0px; }
  .layout.no-sidebar #sidebar { display: none; }
    .tabs { display: flex; }
    .tab { flex: 1; text-align: center; padding: 10px; cursor: pointer; background:#11403e; }
    .tab.active { background:#15514e; font-weight: 600; }
    .panel { flex:1; overflow: auto; padding: 10px; }
    .messages { display: flex; flex-direction: column; gap: 8px; }
    .msg { background:#12372a; padding: 8px 10px; border-radius: 8px; }
    .sys { opacity: 0.8; font-size: 12px; }
    .participants { list-style:none; padding:0; margin:0; }
    .participants li { padding: 8px 6px; border-bottom: 1px dashed rgba(255,255,255,0.08); display:flex; align-items:center; gap:8px; }
    .pill { font-size:11px; padding:2px 6px; border-radius: 999px; background:#1f6f69; }
    .msg-input { display:flex; gap:6px; padding: 8px; border-top: 1px solid rgba(255,255,255,0.08); }
    .msg-input input { flex:1; padding:8px 10px; border-radius: 8px; border:1px solid rgba(255,255,255,0.15); background:#0d2726; color:#fff; }
    .msg-input button { padding: 8px 12px; border-radius:8px; border:1px solid rgba(255,255,255,0.12); background:#1a5e59; color:#fff; cursor:pointer; }

    /* Settings modal */
    .modal { position: fixed; inset: 0; display:none; align-items:center; justify-content:center; background: rgba(0,0,0,0.5); }
    .modal.open { display:flex; }
    .modal .card { width: 560px; background:#0f3432; border:1px solid rgba(255,255,255,0.12); border-radius:12px; padding:16px; }
    .grid { display:grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    select, .modal button { width:100%; padding:8px 10px; border-radius:8px; background:#0d2726; color:#fff; border:1px solid rgba(255,255,255,0.15); }
  .card h3 { margin: 6px 0 10px; }

  /* Quick dropdowns for mic/cam */
  .dropdown { display:none; position:absolute; bottom:68px; left:0; background:#0f3432; border:1px solid rgba(255,255,255,0.12); border-radius:8px; padding:10px; width:260px; z-index:20; }
  .dropdown.open{ display:block; }
  .dropdown label { font-size:12px; opacity:0.8; display:block; margin-bottom:4px; }
  .dropdown select { width:100%; padding:6px; border-radius:6px; background:#0d2726; color:#fff; border:1px solid rgba(255,255,255,0.15); }
  .dropdown .link { margin-top:8px; display:inline-block; color:#8dd8d2; cursor:pointer; font-size:12px; }
  </style>

  <script src="https://download.agora.io/sdk/release/AgoraRTC_N.js"></script>
  <!-- Optional RTM for chat/presence; will degrade gracefully if it fails -->
  <script src="https://download.agora.io/sdk/release/AgoraRTM_N.js"></script>
</head>
<body>

  <div class="layout no-sidebar">
    <div class="topbar">
      <div class="title">Meeting — {{ $channel ?? 'Room' }}</div>
      <div></div>
    </div>

      <div id="stage">
      <div id="local-player" class="video-player">
        <div id="local-status" class="status-icon hidden"><i class='bx bxs-microphone-off'></i> Mic Off</div>
      </div>
      <div id="remote-player" class="video-player">
        <div id="remote-status" class="status-icon hidden"><i class='bx bxs-video-off'></i> Cam Off</div>
      </div>

      <div id="controls-panel">
        <div></div>
        <div class="controls-center">
        <div class="ctrl">
          <button id="toggle-mic" class="ctrl-btn"><i class='bx bxs-microphone'></i><span>Audio</span></button>
          <button id="mic-caret" class="caret" title="More"><i class='bx bx-chevron-up'></i></button>
          <div id="mic-dropdown" class="dropdown">
            <label>Microphone</label>
            <select id="micQuickSelect"></select>
            <label style="margin-top:6px;">Speaker</label>
            <select id="spkQuickSelect"></select>
            <span class="link" id="openSettingsFromMic">Audio settings…</span>
          </div>
        </div>

        <div class="ctrl">
          <button id="toggle-cam" class="ctrl-btn"><i class='bx bxs-video'></i><span>Video</span></button>
          <button id="video-caret" class="caret" title="More"><i class='bx bx-chevron-up'></i></button>
          <div id="video-dropdown" class="dropdown">
            <label>Camera</label>
            <select id="camQuickSelect"></select>
            <label style="margin-top:6px;">Resolution</label>
            <select id="resQuickSelect">
              <option value="default">Default</option>
              <option value="hd">1280x720</option>
              <option value="fhd">1920x1080</option>
            </select>
            <span class="link" id="openSettingsFromCam">Video settings…</span>
          </div>
        </div>

        <button id="participantsBtn" class="icon-btn"><i class='bx bxs-user-detail'></i><span>Participants</span></button>
        <button id="chatBtn" class="icon-btn"><i class='bx bx-chat'></i><span>Chat</span></button>
        <button id="ctrl-share" class="icon-btn"><i class='bx bx-desktop'></i><span>Share</span></button>
        </div>
        <div class="controls-right"><button id="leave-btn"><i class='bx bx-phone-off'></i>End</button></div>
      </div>
    </div>

    <aside class="sidebar" id="sidebar">
      <div class="tabs">
        <div class="tab active" data-tab="chat">Chat</div>
        <div class="tab" data-tab="people">People</div>
      </div>
      <div class="panel" id="panel-chat">
        <div class="messages" id="messages"></div>
      </div>
      <div class="panel hidden" id="panel-people">
        <ul class="participants" id="participants"></ul>
      </div>
      <div class="msg-input" id="chat-input">
  <input id="messageBox" type="text" placeholder="Type a message…" maxlength="5000" />
        <button id="sendBtn"><i class='bx bx-send'></i></button>
      </div>
    </aside>
  </div>

  <!-- Settings Modal -->
  <div class="modal" id="settingsModal" aria-hidden="true">
    <div class="card">
      <h3>Audio & Video Settings</h3>
      <div class="grid">
        <div>
          <label>Camera</label>
          <select id="cameraSelect"></select>
        </div>
        <div>
          <label>Microphone</label>
          <select id="micSelect"></select>
        </div>
        <div>
          <label>Speaker (output)</label>
          <select id="speakerSelect"></select>
        </div>
        <div>
          <label>Resolution</label>
          <select id="resolutionSelect">
            <option value="default">Default</option>
            <option value="hd">1280x720</option>
            <option value="fhd">1920x1080</option>
          </select>
        </div>
      </div>
      <div style="display:flex; gap:8px; margin-top:12px;">
        <button id="applySettings">Apply</button>
        <button id="closeSettings">Close</button>
      </div>
    </div>
  </div>

  <script>
    // Basic configuration (replace with your dynamic values if available)
    const APP_ID   = 'ab155f23c3fc4ae980b11973d818c460';
    const TOKEN    = '007eJxTYBB7sH2B1evi8MNMvhtm+twQlZJ1/Vi8aoGryK1H4grGWt8VGMxNjYyTTVLNjI3NzUzMjQwtUo0tTc3TjA0Mzc0sDIxSf7QczGgIZGSoXabPwsgAgSA+O0NJanGJoZExAwMAMtoeWg==';
    const CHANNEL  = 'test123';
    const RTM_TOKEN = TOKEN || null; // If RTM token differs, set it server-side.
  const LEAVE_REDIRECT = "{{ auth('professor')->check() ? route('messages.professor') : route('messages') }}";

    let client, rtmClient, rtmChannel, localUid;
    let localAudioTrack, localVideoTrack, screenVideoTrack;
    let micMuted = false, camOff = false, isSharing = false;

    const localContainer   = document.getElementById('local-player');
    const remoteContainer  = document.getElementById('remote-player');
    const micBtn           = document.getElementById('toggle-mic');
    const camBtn           = document.getElementById('toggle-cam');
    const leaveBtn         = document.getElementById('leave-btn');
  const shareBtn         = document.getElementById('ctrl-share') || document.getElementById('btn-share');
  const sidebarBtn       = document.getElementById('ctrl-panel') || document.getElementById('btn-toggle-sidebar');
  const settingsBtn      = document.getElementById('ctrl-settings') || document.getElementById('btn-settings');
    const localStatus      = document.getElementById('local-status');
    const remoteStatus     = document.getElementById('remote-status');

  const sidebar          = document.getElementById('sidebar');
  const layoutEl         = document.querySelector('.layout');
    const tabEls           = document.querySelectorAll('.tab');
    const panelChat        = document.getElementById('panel-chat');
    const panelPeople      = document.getElementById('panel-people');
    const messagesEl       = document.getElementById('messages');
    const participantsEl   = document.getElementById('participants');
    const messageBox       = document.getElementById('messageBox');
    const sendBtn          = document.getElementById('sendBtn');

    const settingsModal    = document.getElementById('settingsModal');
    const cameraSelect     = document.getElementById('cameraSelect');
    const micSelect        = document.getElementById('micSelect');
    const speakerSelect    = document.getElementById('speakerSelect');
    const resolutionSelect = document.getElementById('resolutionSelect');
    const applySettingsBtn = document.getElementById('applySettings');
    const closeSettingsBtn = document.getElementById('closeSettings');
  // Bottom bar extras
  const participantsBtn  = document.getElementById('participantsBtn');
  const chatBtn          = document.getElementById('chatBtn');
  const micCaret         = document.getElementById('mic-caret');
  const videoCaret       = document.getElementById('video-caret');
  const micDropdown      = document.getElementById('mic-dropdown');
  const videoDropdown    = document.getElementById('video-dropdown');
  const micQuickSelect   = document.getElementById('micQuickSelect');
  const spkQuickSelect   = document.getElementById('spkQuickSelect');
  const camQuickSelect   = document.getElementById('camQuickSelect');
  const resQuickSelect   = document.getElementById('resQuickSelect');
  const openSettingsFromMic = document.getElementById('openSettingsFromMic');
  const openSettingsFromCam = document.getElementById('openSettingsFromCam');

    function logMsg(content, isSystem=false){
      const div = document.createElement('div');
      div.className = 'msg' + (isSystem? ' sys' : '');
      div.textContent = content;
      messagesEl.appendChild(div);
      messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    function refreshParticipants(){
      participantsEl.innerHTML = '';
      const me = document.createElement('li');
      me.innerHTML = `<i class='bx bxs-user'></i> You <span class="pill">${localUid ?? ''}</span>`;
      participantsEl.appendChild(me);
      client.remoteUsers.forEach(u => {
        const li = document.createElement('li');
        const vid = u.hasVideo ? '' : "<span class='pill'>Cam off</span>";
        const mic = u.hasAudio ? '' : "<span class='pill'>Mic muted</span>";
        li.innerHTML = `<i class='bx bxs-user'></i> User ${u.uid} ${vid} ${mic}`;
        participantsEl.appendChild(li);
      });
    }

    async function enumerateDevices(){
      const devices = await navigator.mediaDevices.enumerateDevices();
      const cams = devices.filter(d=>d.kind==='videoinput');
      const mics = devices.filter(d=>d.kind==='audioinput');
      const outs = devices.filter(d=>d.kind==='audiooutput');
      cameraSelect.innerHTML = cams.map(d=>`<option value="${d.deviceId}">${d.label||'Camera'}</option>`).join('');
      micSelect.innerHTML = mics.map(d=>`<option value="${d.deviceId}">${d.label||'Microphone'}</option>`).join('');
      speakerSelect.innerHTML = outs.map(d=>`<option value="${d.deviceId}">${d.label||'Speaker'}</option>`).join('');
    }

    async function switchCamera(deviceId){
      const profile = resolutionSelect.value;
      const encoderConfig = profile==='hd' ? '720p' : profile==='fhd' ? '1080p' : undefined;
      const newVideo = await AgoraRTC.createCameraVideoTrack({ cameraId: deviceId, encoderConfig });
      await client.unpublish(localVideoTrack);
      localVideoTrack.stop(); localVideoTrack.close();
      localVideoTrack = newVideo;
      await client.publish(localVideoTrack);
      localVideoTrack.play(localContainer);
      camOff = false; camBtn.innerHTML = "<i class='bx bxs-video'></i>Video";
    }

    async function switchMic(deviceId){
      const newAudio = await AgoraRTC.createMicrophoneAudioTrack({ microphoneId: deviceId });
      await client.unpublish(localAudioTrack);
      localAudioTrack.stop(); localAudioTrack.close();
      localAudioTrack = newAudio;
      await client.publish(localAudioTrack);
      if(micMuted){ localAudioTrack.setEnabled(false); }
    }

    function openSettings(){ settingsModal.classList.add('open'); }
    function closeSettings(){ settingsModal.classList.remove('open'); }

    // Sidebar tabs
    tabEls.forEach(t=>t.addEventListener('click', ()=>{
      tabEls.forEach(el=>el.classList.remove('active'));
      t.classList.add('active');
      const tab = t.getAttribute('data-tab');
      panelChat.classList.toggle('hidden', tab!=='chat');
      panelPeople.classList.toggle('hidden', tab!=='people');
      document.getElementById('chat-input').classList.toggle('hidden', tab!=='chat');
    }));

  if (sidebarBtn) sidebarBtn.addEventListener('click', ()=>{
      const hidden = layoutEl.classList.toggle('no-sidebar');
      if(hidden){
        // Ensure chat tab is visible when reopened later
        tabEls.forEach(el=>el.classList.remove('active'));
        document.querySelector('.tab[data-tab="chat"]').classList.add('active');
        panelChat.classList.remove('hidden');
        panelPeople.classList.add('hidden');
      }
  });
    function toggleSidebarFor(tab){
      const isHidden = layoutEl.classList.contains('no-sidebar');
      if(isHidden){
        layoutEl.classList.remove('no-sidebar');
      }else{
        // If same tab is active, hide; otherwise just switch tab
        const current = document.querySelector('.tab.active')?.getAttribute('data-tab');
        if(current === tab){ layoutEl.classList.add('no-sidebar'); return; }
      }
      tabEls.forEach(el=>el.classList.remove('active'));
      document.querySelector(`.tab[data-tab="${tab}"]`).classList.add('active');
      panelChat.classList.toggle('hidden', tab!=='chat');
      panelPeople.classList.toggle('hidden', tab!=='people');
    }
    participantsBtn.addEventListener('click', ()=> toggleSidebarFor('people'));
    chatBtn.addEventListener('click', ()=> toggleSidebarFor('chat'));
    // Mic/Video dropdowns
    function closeDrops(){ micDropdown.classList.remove('open'); videoDropdown.classList.remove('open'); }
    document.addEventListener('click', (e)=>{
      if(!e.target.closest('.ctrl')) closeDrops();
    });
    micCaret.addEventListener('click', async (e)=>{ e.stopPropagation(); await enumerateDevices();
      micQuickSelect.innerHTML = micSelect.innerHTML; spkQuickSelect.innerHTML = speakerSelect.innerHTML; micDropdown.classList.toggle('open'); videoDropdown.classList.remove('open');
    });
    videoCaret.addEventListener('click', async (e)=>{ e.stopPropagation(); await enumerateDevices();
      camQuickSelect.innerHTML = cameraSelect.innerHTML; resQuickSelect.value = resolutionSelect.value; videoDropdown.classList.toggle('open'); micDropdown.classList.remove('open');
    });
    micQuickSelect.addEventListener('change', async ()=>{ if(micQuickSelect.value) await switchMic(micQuickSelect.value); });
    spkQuickSelect?.addEventListener('change', async ()=>{
      // Try to set sinkId for all media elements
      try{
        const vids = document.querySelectorAll('video, audio');
        for(const v of vids){ if(v.setSinkId) await v.setSinkId(spkQuickSelect.value); }
      }catch{}
    });
    camQuickSelect.addEventListener('change', async ()=>{ if(camQuickSelect.value){ resolutionSelect.value = resQuickSelect.value; await switchCamera(camQuickSelect.value); }});
    resQuickSelect.addEventListener('change', async ()=>{ if(camQuickSelect.value){ resolutionSelect.value = resQuickSelect.value; await switchCamera(camQuickSelect.value); }});
    openSettingsFromMic.addEventListener('click', ()=>{ enumerateDevices(); openSettings(); closeDrops(); });
    openSettingsFromCam.addEventListener('click', ()=>{ enumerateDevices(); openSettings(); closeDrops(); });
  if (settingsBtn) settingsBtn.addEventListener('click', ()=>{ enumerateDevices(); openSettings(); });
    closeSettingsBtn.addEventListener('click', closeSettings);
    applySettingsBtn.addEventListener('click', async ()=>{
      if(cameraSelect.value) await switchCamera(cameraSelect.value);
      if(micSelect.value) await switchMic(micSelect.value);
      // Attempt set sinkId if supported
      try{
        const videos = remoteContainer.querySelectorAll('video');
        videos.forEach(v=>v.setSinkId && v.setSinkId(speakerSelect.value));
      }catch{}
      closeSettings();
    });

    window.addEventListener('DOMContentLoaded', async () => {
      try {
        client = AgoraRTC.createClient({ mode: 'rtc', codec: 'vp8' });
        client.on('user-published', handleUserPublished);
        client.on('user-unpublished', handleUserUnpublished);
        client.on('user-joined', refreshParticipants);
        client.on('user-left', refreshParticipants);

        localUid = await client.join(APP_ID, CHANNEL, TOKEN, null);
        [localAudioTrack, localVideoTrack] = await AgoraRTC.createMicrophoneAndCameraTracks();

        localVideoTrack.play(localContainer);
        await client.publish([localAudioTrack, localVideoTrack]);
        refreshParticipants();

        // Try to init RTM for chat
        try{
          rtmClient = AgoraRTM.createInstance(APP_ID);
          await rtmClient.login({ uid: String(localUid), token: RTM_TOKEN });
          rtmChannel = await rtmClient.createChannel(CHANNEL);
          await rtmChannel.join();
          rtmChannel.on('ChannelMessage', ({text}, senderId)=>{
            logMsg(`User ${senderId}: ${text}`);
          });
          logMsg('Chat connected', true);
        }catch(err){
          logMsg('Chat not available (RTM login failed).', true);
        }

      } catch (err) {
        alert('Connection failed. Check APP_ID/TOKEN.');
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
    });

    // Screen share
    shareBtn.addEventListener('click', async ()=>{
      if(!isSharing){
        try{
          screenVideoTrack = await AgoraRTC.createScreenVideoTrack({ withAudio: 'auto' });
          await client.unpublish(localVideoTrack);
          localVideoTrack.stop();
          await client.publish(screenVideoTrack);
          screenVideoTrack.play(localContainer);
          isSharing = true; shareBtn.innerHTML = "<i class='bx bx-desktop'></i><span>Stop</span>";
          // When user stops via browser UI
          screenVideoTrack.on('track-ended', async ()=>{
            if(isSharing) await stopShare();
          });
        }catch(e){ logMsg('Share screen failed.', true); }
      }else{
        await stopShare();
      }
    });

    async function stopShare(){
      if(!isSharing) return;
      await client.unpublish(screenVideoTrack);
      screenVideoTrack.stop(); screenVideoTrack.close();
      await client.publish(localVideoTrack);
      localVideoTrack.play(localContainer);
      isSharing = false; shareBtn.innerHTML = "<i class='bx bx-desktop'></i><span>Share</span>";
    }

    // Chat send
    sendBtn.addEventListener('click', sendChat);
    messageBox.addEventListener('keydown', (e)=>{ if(e.key==='Enter'){ e.preventDefault(); sendChat(); } });
    async function sendChat(){
      const text = messageBox.value.trim(); if(!text) return;
      messageBox.value = '';
      if(rtmChannel){
        try{ await rtmChannel.sendMessage({ text }); logMsg(`You: ${text}`); }catch{ logMsg('Failed to send message.', true); }
      }else{
        logMsg('Chat unavailable. RTM not connected.', true);
      }
    }

  leaveBtn.addEventListener('click', async () => {
      try{
        if (localAudioTrack) { localAudioTrack.close(); }
        if (localVideoTrack) { localVideoTrack.close(); }
        if (screenVideoTrack) { screenVideoTrack.close(); }
        if (rtmChannel) { await rtmChannel.leave(); }
        if (rtmClient) { await rtmClient.logout(); }
        await client.leave();
      } finally {
    window.location.href = LEAVE_REDIRECT;
      }
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
      refreshParticipants();
    }

    function handleUserUnpublished(user) {
      remoteContainer.innerHTML = "";
      remoteContainer.appendChild(remoteStatus);
      remoteStatus.classList.remove('hidden');
      refreshParticipants();
    }
  </script>

</body>
</html>
