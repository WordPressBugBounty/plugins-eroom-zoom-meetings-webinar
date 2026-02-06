// Global meeting config.
var meetingConfig = {
	china: 0, //0-GLOBAL, 1-China
};

console.log(JSON.stringify(ZoomMtg.checkSystemRequirements()));

// it's option if you want to change the MeetingSDK-Web dependency link resources. setZoomJSLib must be run at first
// ZoomMtg.setZoomJSLib("https://source.zoom.us/{VERSION}/lib", "/av"); // default, don't need call it
if (meetingConfig.china)
	ZoomMtg.setZoomJSLib("https://jssdk.zoomus.cn/3.10.0/lib", "/av"); // china cdn option

ZoomMtg.preLoadWasm();
ZoomMtg.prepareWebSDK();

function beginJoin(meetingData) {
	ZoomMtg.i18n.load(stmZoomMeetingData.lang);
	ZoomMtg.init({
		leaveUrl: meetingData.leaveUrl,
		disableCORP: !window.crossOriginIsolated, // default true
		// disablePreview: false, // default false
		success: function () {
			console.log('Zoom initialized, joining meeting...');
			ZoomMtg.join({
				meetingNumber: meetingData.meetingNumber,
				userName: meetingData.userName,
				signature: meetingData.signature,
				sdkKey: meetingData.sdkKey,
				userEmail: meetingData.userEmail,
				passWord: meetingData.password,
				tk: meetingData.tk,
				success: function (res) {
					console.log('Join meeting success');
					console.log('Get attendee list');
					ZoomMtg.getAttendeeslist({});
					ZoomMtg.getCurrentUser({
						success: function (res) {
							console.log('Success getCurrentUser', res.result.currentUser);
						},
					});
				},
				error: function (res) {
					console.error('Join meeting error:', res);
				},
			});
		},
		error: function (res) {
			console.error('Init error:', res);
		},
	});

	ZoomMtg.inMeetingServiceListener('onUserJoin', function (data) {
		console.log('inMeetingServiceListener onUserJoin', data);
	});

	ZoomMtg.inMeetingServiceListener('onUserLeave', function (data) {
		console.log('inMeetingServiceListener onUserLeave', data);
	});

	ZoomMtg.inMeetingServiceListener('onUserIsInWaitingRoom', function (data) {
		console.log('inMeetingServiceListener onUserIsInWaitingRoom', data);
	});

	ZoomMtg.inMeetingServiceListener('onMeetingStatus', function (data) {
		console.log('inMeetingServiceListener onMeetingStatus', data);
	});
}

// Fetch meeting data and signature from server.
fetch(stmZoomMeetingData.endpoint, {
	method: 'POST',
	headers: {
		'Content-Type': 'application/json',
	},
	body: JSON.stringify({
		postId: stmZoomMeetingData.postId,
		role: stmZoomMeetingData.role,
	}),
})
.then(function(response) {
	return response.json();
})
.then(function(data) {
	if (data.success && data.data.signature) {
		beginJoin(data.data);
	} else {
		console.error('Failed to get meeting data from server:', data);
		alert('Failed to initialize meeting: ' + (data.data && data.data.message ? data.data.message : 'Unknown error'));
	}
})
.catch(function(error) {
	console.error('Error fetching meeting data:', error);
	alert('Failed to initialize meeting. Please try again.');
});

