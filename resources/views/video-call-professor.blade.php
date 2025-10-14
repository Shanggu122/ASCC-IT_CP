<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <title>Professor Meeting</title>
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/css/video-call.css" />
  <script src="https://download.agora.io/sdk/release/AgoraRTC_N.js"></script>
  <script src="https://download.agora.io/sdk/release/AgoraRTM.min.js"></script>
</head>
<body>
  <div class="layout no-sidebar">
  <div class="topbar"><div class="title">Professor Meeting</div><div></div></div>
    <div id="stage">
      <div id="local-player" class="video-player"><div id="local-status" class="status-icon hidden"><i class='bx bxs-microphone-off'></i> Mic Off</div></div>
      <div id="remote-player" class="video-player"><div id="remote-status" class="status-icon hidden"><i class='bx bxs-video-off'></i> Cam Off</div></div>
      <div id="controls-panel">
        <div></div>
        <div class="controls-center">
          <div class="ctrl">
            <button id="toggle-mic" class="ctrl-btn"><i class='bx bxs-microphone'></i><span>Audio</span></button>
            <button id="mic-caret" class="caret"><i class='bx bx-chevron-up'></i></button>
            <div id="mic-dropdown" class="dropdown">
              <label>Microphone</label><select id="micQuickSelect"></select>
              <label style="margin-top:6px;">Speaker</label><select id="spkQuickSelect"></select>
              <span class="link" id="openSettingsFromMic">Audio settings…</span>
            </div>
          </div>
          <div class="ctrl">
            <button id="toggle-cam" class="ctrl-btn"><i class='bx bxs-video'></i><span>Video</span></button>
            <button id="video-caret" class="caret"><i class='bx bx-chevron-up'></i></button>
            <div id="video-dropdown" class="dropdown">
              <label>Camera</label><select id="camQuickSelect"></select>
              <label style="margin-top:6px;">Resolution</label>
              <select id="resQuickSelect"><option value="default">Default</option><option value="hd">1280x720</option><option value="fhd">1920x1080</option></select>
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
      <div class="tabs"><div class="tab active" data-tab="chat">Chat</div><div class="tab" data-tab="people">People</div></div>
      <div class="panel" id="panel-chat"><div class="messages" id="messages"></div></div>
      <div class="panel hidden" id="panel-people"><ul class="participants" id="participants"></ul></div>
  <div class="msg-input" id="chat-input"><input id="messageBox" type="text" placeholder="Type a message…" maxlength="5000" /><button id="sendBtn"><i class='bx bx-send'></i></button></div>
    </aside>
  </div>
  <div class="modal" id="settingsModal" aria-hidden="true">
    <div class="card">
      <h3>Audio & Video Settings</h3>
      <div class="grid">
        <div><label>Camera</label><select id="cameraSelect"></select></div>
        <div><label>Microphone</label><select id="micSelect"></select></div>
        <div><label>Speaker (output)</label><select id="speakerSelect"></select></div>
        <div><label>Resolution</label><select id="resolutionSelect"><option value="default">Default</option><option value="hd">1280x720</option><option value="fhd">1920x1080</option></select></div>
      </div>
      <div style="display:flex;gap:8px;margin-top:12px;"><button id="applySettings">Apply</button><button id="closeSettings">Close</button></div>
    </div>
  </div>
  <script>
    const APP_ID = @json(config('app.agora_app_id'));
  const CHANNEL = @json($channel ?? 'prof-room');
  const COUNTERPART_NAME = @json($counterpartName ?? null);
    const IS_DEBUG = @json(config('app.debug'));
    let TOKEN = null;
    let RTM_TOKEN = null;
    const LEAVE_REDIRECT = "{{ auth('professor')->check() ? route('messages.professor') : route('landing') }}";
    let client, rtmClient, rtmChannel, localUid;
    let localAudioTrack, localVideoTrack, screenVideoTrack;
    let micMuted=false, camOff=false, isSharing=false;
    const localContainer=document.getElementById('local-player');
    const remoteContainer=document.getElementById('remote-player');
    const micBtn=document.getElementById('toggle-mic');
    const camBtn=document.getElementById('toggle-cam');
    const leaveBtn=document.getElementById('leave-btn');
    const localStatus=document.getElementById('local-status');
    const remoteStatus=document.getElementById('remote-status');
    const sidebarBtn=document.getElementById('ctrl-panel');
    const layoutEl=document.querySelector('.layout');
    const tabEls=document.querySelectorAll('.tab');
    const panelChat=document.getElementById('panel-chat');
    const panelPeople=document.getElementById('panel-people');
    const messagesEl=document.getElementById('messages');
    const participantsEl=document.getElementById('participants');
    const messageBox=document.getElementById('messageBox');
    const sendBtn=document.getElementById('sendBtn');
    const settingsModal=document.getElementById('settingsModal');
    const cameraSelect=document.getElementById('cameraSelect');
    const micSelect=document.getElementById('micSelect');
    const speakerSelect=document.getElementById('speakerSelect');
    const resolutionSelect=document.getElementById('resolutionSelect');
    const applySettingsBtn=document.getElementById('applySettings');
    const closeSettingsBtn=document.getElementById('closeSettings');
    const participantsBtn=document.getElementById('participantsBtn');
    const chatBtn=document.getElementById('chatBtn');
    const micCaret=document.getElementById('mic-caret');
    const videoCaret=document.getElementById('video-caret');
    const micDropdown=document.getElementById('mic-dropdown');
    const videoDropdown=document.getElementById('video-dropdown');
    const micQuickSelect=document.getElementById('micQuickSelect');
    const spkQuickSelect=document.getElementById('spkQuickSelect');
    const camQuickSelect=document.getElementById('camQuickSelect');
    const resQuickSelect=document.getElementById('resQuickSelect');
    const openSettingsFromMic=document.getElementById('openSettingsFromMic');
    const openSettingsFromCam=document.getElementById('openSettingsFromCam');
  function showRetryChat(){ /* intentionally hidden from UI to avoid noise */ }
    // HTTP-polling chat fallback
    const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    let pollTimer=null; let seenKeys=new Set();
    let studId=null, profId=null;
    try{ const m=/^stud-([^]+)-prof-([^]+)$/.exec(@json($channel ?? '')); if(m){ studId=m[1]; profId=m[2]; } }catch{}
    const SELF_ROLE='professor';
  function renderFetched(list){ for(const msg of list){ const key=`${msg.Created_At}|${msg.Sender}|${msg.Message||''}|${msg.file_path||''}`; if(seenKeys.has(key)) continue; seenKeys.add(key); const isSelf = msg.Sender==='professor'; const who = isSelf?'You':(COUNTERPART_NAME || 'Student'); if((msg.Message||'').trim()!==''){ logMsg(`${who}: ${msg.Message}`, false, isSelf); } } }
    async function pollOnce(){ if(!studId||!profId) return; try{ const res=await fetch(`/load-direct-messages/${encodeURIComponent(studId)}/${encodeURIComponent(profId)}`,{credentials:'include'}); if(!res.ok) return; const list=await res.json(); renderFetched(list);}catch{} }
    function startPolling(){ if(pollTimer) return; pollTimer=setInterval(pollOnce,2000); pollOnce(); }
  async function httpSendChat(text){ if(!studId||!profId) return false; try{ const res=await fetch(`/send-message`,{ method:'POST', credentials:'include', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'}, body:JSON.stringify({ stud_id:studId, prof_id:profId, sender:SELF_ROLE, recipient:String(studId), message:text })}); if(res.ok){ setTimeout(pollOnce,200); return true; } }catch{} return false; }
  function logMsg(content,isSystem=false,isSelf=false){ if(isSystem){ try{console.debug(content);}catch{} return; } const d=document.createElement('div');d.className='msg ' + (isSelf?'me':'other'); d.textContent=content;messagesEl.appendChild(d);messagesEl.scrollTop=messagesEl.scrollHeight;}
  function refreshParticipants(){participantsEl.innerHTML='';const me=document.createElement('li');me.innerHTML=`<i class='bx bxs-user'></i> You`;participantsEl.appendChild(me);client.remoteUsers.forEach(u=>{const li=document.createElement('li');const vid=u.hasVideo?'':"<span class='pill'>Cam off</span>";const mic=u.hasAudio?'':"<span class='pill'>Mic muted</span>";const name = COUNTERPART_NAME || 'Student'; li.innerHTML=`<i class='bx bxs-user'></i> ${name} ${vid} ${mic}`;participantsEl.appendChild(li);});}
    async function enumerateDevices(){const devices=await navigator.mediaDevices.enumerateDevices();const cams=devices.filter(d=>d.kind==='videoinput');const mics=devices.filter(d=>d.kind==='audioinput');const outs=devices.filter(d=>d.kind==='audiooutput');cameraSelect.innerHTML=cams.map(d=>`<option value="${d.deviceId}">${d.label||'Camera'}</option>`).join('');micSelect.innerHTML=mics.map(d=>`<option value="${d.deviceId}">${d.label||'Microphone'}</option>`).join('');speakerSelect.innerHTML=outs.map(d=>`<option value="${d.deviceId}">${d.label||'Speaker'}</option>`).join('');}
    async function switchCamera(deviceId){const profile=resolutionSelect.value;const encoderConfig=profile==='hd'?'720p':profile==='fhd'?'1080p':undefined;const newVideo=await AgoraRTC.createCameraVideoTrack({cameraId:deviceId,encoderConfig});await client.unpublish(localVideoTrack);localVideoTrack.stop();localVideoTrack.close();localVideoTrack=newVideo;await client.publish(localVideoTrack);localVideoTrack.play(localContainer);camOff=false;camBtn.innerHTML="<i class='bx bxs-video'></i>Video";}
    async function switchMic(deviceId){const newAudio=await AgoraRTC.createMicrophoneAudioTrack({microphoneId:deviceId});await client.unpublish(localAudioTrack);localAudioTrack.stop();localAudioTrack.close();localAudioTrack=newAudio;await client.publish(localAudioTrack);if(micMuted){localAudioTrack.setEnabled(false);}}
    function openSettings(){settingsModal.classList.add('open');}
    function closeSettings(){settingsModal.classList.remove('open');}
    tabEls.forEach(t=>t.addEventListener('click',()=>{tabEls.forEach(el=>el.classList.remove('active'));t.classList.add('active');const tab=t.getAttribute('data-tab');panelChat.classList.toggle('hidden',tab!=='chat');panelPeople.classList.toggle('hidden',tab!=='people');document.getElementById('chat-input').classList.toggle('hidden',tab!=='chat');}));
    function toggleSidebarFor(tab){
      const isMobile = window.matchMedia('(max-width: 768px)').matches;
      if(isMobile){
        const open = layoutEl.classList.contains('show-sidebar');
        const current = document.querySelector('.tab.active')?.getAttribute('data-tab');
        if(open && current === tab){ layoutEl.classList.remove('show-sidebar'); }
        else { layoutEl.classList.add('show-sidebar'); }
      } else {
        const isHidden=layoutEl.classList.contains('no-sidebar');
        if(isHidden){layoutEl.classList.remove('no-sidebar');}
        else { const current=document.querySelector('.tab.active')?.getAttribute('data-tab'); if(current===tab){layoutEl.classList.add('no-sidebar');return;} }
      }
      tabEls.forEach(el=>el.classList.remove('active'));
      document.querySelector(`.tab[data-tab="${tab}"]`).classList.add('active');
      panelChat.classList.toggle('hidden',tab!=='chat');
      panelPeople.classList.toggle('hidden',tab!=='people');
    }
    participantsBtn.addEventListener('click',()=>toggleSidebarFor('people'));
    chatBtn.addEventListener('click',()=>toggleSidebarFor('chat'));
    function closeDrops(){micDropdown.classList.remove('open');videoDropdown.classList.remove('open');}
    document.addEventListener('click',e=>{if(!e.target.closest('.ctrl')) closeDrops();});
    micCaret.addEventListener('click',async e=>{e.stopPropagation();await enumerateDevices();micQuickSelect.innerHTML=micSelect.innerHTML;spkQuickSelect.innerHTML=speakerSelect.innerHTML;micDropdown.classList.toggle('open');videoDropdown.classList.remove('open');});
    videoCaret.addEventListener('click',async e=>{e.stopPropagation();await enumerateDevices();camQuickSelect.innerHTML=cameraSelect.innerHTML;resQuickSelect.value=resolutionSelect.value;videoDropdown.classList.toggle('open');micDropdown.classList.remove('open');});
    micQuickSelect.addEventListener('change',async()=>{if(micQuickSelect.value) await switchMic(micQuickSelect.value);});
    spkQuickSelect?.addEventListener('change',async()=>{try{const vids=document.querySelectorAll('video,audio');for(const v of vids){if(v.setSinkId) await v.setSinkId(spkQuickSelect.value);} }catch{}});
    camQuickSelect.addEventListener('change',async()=>{if(camQuickSelect.value){resolutionSelect.value=resQuickSelect.value;await switchCamera(camQuickSelect.value);}});
    resQuickSelect.addEventListener('change',async()=>{if(camQuickSelect.value){resolutionSelect.value=resQuickSelect.value;await switchCamera(camQuickSelect.value);}});
    openSettingsFromMic.addEventListener('click',()=>{enumerateDevices();openSettings();closeDrops();});
    openSettingsFromCam.addEventListener('click',()=>{enumerateDevices();openSettings();closeDrops();});
    closeSettingsBtn.addEventListener('click',closeSettings);
    applySettingsBtn.addEventListener('click',async()=>{if(cameraSelect.value) await switchCamera(cameraSelect.value);if(micSelect.value) await switchMic(micSelect.value);try{const videos=remoteContainer.querySelectorAll('video');videos.forEach(v=>v.setSinkId && v.setSinkId(speakerSelect.value));}catch{}closeSettings();});
    async function fetchRtcTokenProf(channel){
      const url = `{{ route('agora.token.rtc.prof') }}?channel=${encodeURIComponent(channel)}`;
      const res = await fetch(url, { credentials: 'include' });
      if(!res.ok){
        let body='';
        try{ body = await res.text(); }catch{}
        console.error('RTC token fetch failed', {status: res.status, url, body});
        throw new Error(`RTC token endpoint returned ${res.status}`);
      }
      const data = await res.json();
      if(!data.token || !data.appId){
        console.error('Invalid RTC token response payload', data);
        throw new Error('Invalid RTC token response');
      }
      return data;
    }
    async function fetchRtmTokenProf(){
      try{
        const url = `{{ route('agora.token.rtm.prof') }}`;
        const res = await fetch(url, { credentials: 'include' });
        if(!res.ok){
          let body='';
          try{ body = await res.text(); }catch{}
          console.warn('RTM token endpoint status', {status: res.status, url, body});
          return null;
        }
        const data = await res.json();
        if(!data || !data.token) return null; return data;
      }catch(err){ console.warn('RTM token fetch error', err); return null; }
    }

    // Try to create local tracks; fall back gracefully if devices are busy/denied
    async function createLocalTracksWithFallback(){
      try{
        const pair = await AgoraRTC.createMicrophoneAndCameraTracks();
        return { audio: pair[0], video: pair[1], mode: 'mic+cam' };
      }catch(err){
        console.warn('createMicrophoneAndCameraTracks failed, falling back…', err);
        let audio=null, video=null, mode='none';
        try{ video = await AgoraRTC.createCameraVideoTrack(); mode = video ? 'cam' : mode; }catch(e){ console.warn('camera track failed', e); }
        try{ audio = await AgoraRTC.createMicrophoneAudioTrack(); mode = audio && mode==='cam' ? 'mic+cam' : (audio ? 'mic' : mode); }catch(e){ console.warn('microphone track failed', e); }
        return { audio, video, mode, error: err };
      }
    }
    async function joinCall(){
      const rtc = await fetchRtcTokenProf(CHANNEL);
      TOKEN = rtc.token; const appIdFromServer = rtc.appId || APP_ID; const uidFromServer = rtc.uid !== undefined && rtc.uid !== null ? Number(rtc.uid) : null;
      client=AgoraRTC.createClient({mode:'rtc',codec:'vp8'});
      client.on('user-published',handleUserPublished); client.on('user-unpublished',handleUserUnpublished); client.on('user-joined',refreshParticipants); client.on('user-left',refreshParticipants);
      client.on('token-privilege-will-expire', async ()=>{ try{ const fresh = await fetchRtcTokenProf(CHANNEL); TOKEN = fresh.token; await client.renewToken(TOKEN); logMsg('RTC token renewed',true);}catch{ logMsg('RTC token renewal failed',true);} });
      client.on('token-privilege-did-expire', async ()=>{ try{ const fresh = await fetchRtcTokenProf(CHANNEL); TOKEN = fresh.token; await client.renewToken(TOKEN); logMsg('RTC token reloaded after expiry',true);}catch{ logMsg('RTC token reload failed',true);} });
      // Join first; do not abort the call if device creation fails
      localUid = await client.join(appIdFromServer, CHANNEL, TOKEN, uidFromServer);
      const created = await createLocalTracksWithFallback();
      localAudioTrack = created.audio || null;
      localVideoTrack = created.video || null;
      const toPublish = [];
      if(localAudioTrack) toPublish.push(localAudioTrack);
      if(localVideoTrack) {
        toPublish.push(localVideoTrack);
        try{ localVideoTrack.play(localContainer); }catch{}
      }
      if(toPublish.length){
        try{ await client.publish(toPublish); }
        catch(pubErr){ console.error('Failed to publish local tracks', pubErr); }
      } else {
        logMsg('Joined without mic/camera (device blocked or not available).', true);
        // Show status badges appropriately
        try{ remoteStatus.classList.remove('hidden'); }catch{}
      }
  try{ rtcDataStream = await client.createDataStream(); logMsg('Chat ready (RTC data stream).', true);}catch{}
  refreshParticipants();
      async function connectRTM(){
        if (typeof AgoraRTM === 'undefined') { logMsg('Chat SDK (AgoraRTM) not loaded; skipping RTM.', true); startPolling(); return; }
        try{
          const rtm = await fetchRtmTokenProf();
          if(rtm && rtm.token){
            RTM_TOKEN = rtm.token; const rtmUid = String(rtm.uid ?? localUid);
            logMsg(`RTM: attempting login as ${rtmUid}`, true);
            logMsg(`RTM: appId ${appIdFromServer}, token length ${RTM_TOKEN?.length||0}`, true);
            rtmClient = AgoraRTM.createInstance(appIdFromServer);
            rtmClient.on('ConnectionStateChanged', (state, reason)=>{ logMsg(`RTM state: ${state} (${reason})`, true); });
            await rtmClient.login({uid: rtmUid, token: RTM_TOKEN});
            rtmClient.on('TokenPrivilegeWillExpire', async ()=>{ try{ const fresh = await fetchRtmTokenProf(); if(fresh){ await rtmClient.renewToken(fresh.token); logMsg('RTM token renewed',true);} }catch{} });
            rtmClient.on('TokenPrivilegeDidExpire', async ()=>{ try{ const fresh = await fetchRtmTokenProf(); if(fresh){ await rtmClient.renewToken(fresh.token); logMsg('RTM token reloaded',true);} }catch{} });
            rtmChannel = await rtmClient.createChannel(CHANNEL); await rtmChannel.join(); rtmChannel.on('ChannelMessage',({text},senderId)=>{ logMsg(`${COUNTERPART_NAME || 'Student'}: ${text}`, false, false); }); logMsg('Chat connected',true);
          } else { logMsg('Chat not available (no RTM token).',true);} 
  }catch(err){ console.error('RTM login error', err); const code = err?.code ?? err?.message ?? 'unknown'; logMsg(`Chat not available (RTM login failed: ${code}).`,true); showRetryChat(); } 
      }
  await connectRTM();
  startPolling();
    }
    window.addEventListener('DOMContentLoaded', async ()=>{ try{ await joinCall(); }catch(err){ alert('Connection failed. See logs or token endpoint.'); } });
    micBtn.addEventListener('click',()=>{if(!localAudioTrack)return;micMuted=!micMuted;localAudioTrack.setEnabled(!micMuted);micBtn.innerHTML=micMuted?"<i class='bx bxs-microphone-off'></i>Unmute":"<i class='bx bxs-microphone'></i>Mute";localStatus.classList.toggle('hidden',!micMuted);});
    camBtn.addEventListener('click',()=>{if(!localVideoTrack)return;camOff=!camOff;localVideoTrack.setEnabled(!camOff);camBtn.innerHTML=camOff?"<i class='bx bxs-video-off'></i>Show":"<i class='bx bxs-video'></i>Video";});
    const shareBtn=document.getElementById('ctrl-share');
    shareBtn.addEventListener('click',async()=>{if(!isSharing){try{screenVideoTrack=await AgoraRTC.createScreenVideoTrack({withAudio:'auto'});await client.unpublish(localVideoTrack);localVideoTrack.stop();await client.publish(screenVideoTrack);screenVideoTrack.play(localContainer);isSharing=true;shareBtn.innerHTML="<i class='bx bx-desktop'></i><span>Stop</span>";screenVideoTrack.on('track-ended',async()=>{if(isSharing) await stopShare();});}catch(e){logMsg('Share screen failed.',true);}}else{await stopShare();}});
    async function stopShare(){if(!isSharing) return;await client.unpublish(screenVideoTrack);screenVideoTrack.stop();screenVideoTrack.close();await client.publish(localVideoTrack);localVideoTrack.play(localContainer);isSharing=false;shareBtn.innerHTML="<i class='bx bx-desktop'></i><span>Share</span>";}
    sendBtn.addEventListener('click',sendChat);messageBox.addEventListener('keydown',e=>{if(e.key==='Enter'){e.preventDefault();sendChat();}});
  async function sendChat(){const text=messageBox.value.trim();if(!text)return;messageBox.value='';if(rtmChannel){try{await rtmChannel.sendMessage({text});logMsg(`You: ${text}`, false, true);}catch{logMsg('Failed to send message.',true);} }else if(rtcDataStream){ try{ await rtcDataStream.send(text); logMsg(`You: ${text}`, false, true);}catch{logMsg('Failed to send message (RTC).',true);} } else { const ok = await httpSendChat(text); if(!ok) logMsg('Chat unavailable. RTM not connected.',true);} }
    leaveBtn.addEventListener('click',async()=>{try{if(localAudioTrack)localAudioTrack.close();if(localVideoTrack)localVideoTrack.close();if(screenVideoTrack)screenVideoTrack.close();if(rtmChannel)await rtmChannel.leave();if(rtmClient)await rtmClient.logout();await client.leave();}finally{window.location.href=LEAVE_REDIRECT;}});
  async function handleUserPublished(user,mediaType){await client.subscribe(user,mediaType);if(mediaType==='video'){user.videoTrack.play(remoteContainer);remoteStatus.classList.add('hidden');}if(mediaType==='audio'){user.audioTrack.play();}refreshParticipants();}
  if(client && client.on){ client.on('stream-message', ({uid,streamId,data})=>{ try{ const text=typeof data==='string'?data:(new TextDecoder()).decode(data); const isSelf = String(uid)===String(localUid); const who = isSelf ? 'You' : (COUNTERPART_NAME || 'Student'); logMsg(`${who}: ${text}`, false, isSelf);}catch{} }); }
    function handleUserUnpublished(user){remoteContainer.innerHTML='';remoteContainer.appendChild(remoteStatus);remoteStatus.classList.remove('hidden');refreshParticipants();}
  </script>
</body>
</html>