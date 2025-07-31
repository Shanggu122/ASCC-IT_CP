// your Agora credentials
const APP_ID   = 'ab155f23c3fc4ae980b11973d818c460';
const TOKEN    = '007eJxTYChOEopXWTWhsuyCa9ITR/kwW5nja6Yd4A3r2xZoOuW9sYECQ2KSoalpmpFxsnFaskliqqWFQZKhoaW5cYqFoUWyiZnBhjKVjIZARobbOWrMjAwQCOKzMzgWOzvreoYwMAAAAbgdew==';          // null if token not needed
const CHANNEL  = decodeURIComponent(location.pathname.split('/').pop());

let client, localAudioTrack, localVideoTrack;

// grab the UI
const localContainer  = document.getElementById('local-player');
const remoteContainer = document.getElementById('remote-player');
const leaveBtn        = document.getElementById('leave-btn');

// 1) initialize and join on load
window.addEventListener('DOMContentLoaded', async () => {
  client = AgoraRTC.createClient({ mode: 'rtc', codec: 'vp8' });

  // listen for other users
  client.on('user-published',  handleUserPublished);
  client.on('user-unpublished', handleUserUnpublished);

  // join channel
  await client.join(APP_ID, CHANNEL, TOKEN, null);

  // create & publish your tracks
  [localAudioTrack, localVideoTrack] = await AgoraRTC.createMicrophoneAndCameraTracks();
  localVideoTrack.play(localContainer);
  await client.publish([ localAudioTrack, localVideoTrack ]);
});

// 2) subscribe & play remote
async function handleUserPublished(user, mediaType) {
  await client.subscribe(user, mediaType);
  if (mediaType === 'video')  user.videoTrack.play(remoteContainer);
  if (mediaType === 'audio')  user.audioTrack.play();
}

// 3) clear UI when they leave
function handleUserUnpublished(user) {
  remoteContainer.innerHTML = '';
}

// 4) leave button
leaveBtn.onclick = async () => {
  localAudioTrack.close();
  localVideoTrack.close();
  await client.leave();
  location.href = '/messages';  // back to your chat page
};
